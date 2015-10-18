<?php
namespace Batten;

use App\Environment as Env;
use Exception;
use Ok\MiscUtils;

abstract class Controller implements ControllerInterface {
	use EventTargetTrait;

	static private $booted = false;
	static private $bootPath = [];
	static private $bootLoopRecoveryAttempted;
	static private $componentResolver;

	static protected function getBaseChain() {
		return $chain = [
			__NAMESPACE__ => [
				'namespace' => __NAMESPACE__,
				'path' => __DIR__,
			],

			'app' => [
				'namespace' => 'App',
				'path' => Env::getVars()->get('appPackageFilePath') . '/App',
			],
		];
	}

	static public function fromCode($aCode, $aOptions = array()) {
		$component = static::getComponentResolver()->resolveComponent(
			static::getChain($aCode),
			'Controller',
			null,
			null
		);

		if (!$component) {
			throw new \Exception(
				"Could not resolve Controller component for module '"  . $aCode . "'."
				. " No component class files could be found."
			);
		}

		/** @noinspection PhpIncludeInspection */
		include_once $component['includeFilePath'];

		if (!class_exists($component['className'])) {
			throw new \Exception(
				"Could not resolve Controller component for module '"  . $aCode . "'."
				. " No component class was found in include file '" . $component['includeFilePath'] . "'."
			);
		}

		/** @var Controller $controller */
		$controller = new $component['className']($aCode, $aOptions);

		return $controller;
	}

	static public function getChain($aModuleCode) {
		$chain = static::getBaseChain();

		if ($aModuleCode != null) {
			$moduleNamespace = $aModuleCode;
			$moduleDir = $moduleNamespace;

			$chain['module'] = [
				'namespace' => 'App\\Modules\\' . $moduleNamespace,
				'path' => Env::getVars()->get('appPackageFilePath') . '/App/Modules/' . $moduleDir,
			];
		}

		return $chain;
	}

	static public function getComponentResolver() {
		if (!self::$componentResolver) {
			self::$componentResolver = new ComponentResolver();
		}

		return self::$componentResolver;
	}

	static public function boot() {
		if (self::$booted) {
			throw new \Exception(
				__METHOD__ . " can only be called once per request, unlike reboot()."
			);
		}

		self::$booted = true;

		if (\App\DEBUG && Env::getVars()->get('debugMemUsage')) {
			Env::getLogger()->debug('mem[boot begin]: ' . ceil(memory_get_usage()/1024) . 'K');
		}

		if (\App\DEBUG && Env::getVars()->get('debugPaths')) {
			Env::getLogger()->debug('App dependencies file path: '. Env::getVars()->get('appDependenciesFilePath'));
			Env::getLogger()->debug('App package file path: '. Env::getVars()->get('appPackageFilePath'));
		}

		$info = [
			'moduleCode' => '',
			'nextRoute' => static::getInitialRoute(),
		];

		static::reboot($info);

		if (\App\DEBUG && Env::getVars()->get('debugMemUsage')) {
			Env::getLogger()->debug('mem[boot end]: ' . ceil(memory_get_usage()/1024) . 'K');
			Env::getLogger()->debug('mem-peak[boot end]: ' . ceil(memory_get_peak_usage()/1024) . 'K');

			Env::getLogger()->debug('realpath-cache-size[boot end]: ' . (ceil(realpath_cache_size()/1024)) . 'K/' . ini_get('realpath_cache_size'));
		}
	}

	static public function reboot($aInfo, $aHintsData = null) {
		$stubController = static::fromCode($aInfo['moduleCode']);
		$stubController->init();

		$finalController = null;
		$finalError = null;

		try {
			$finalController = $stubController->resolveController($aInfo, $aHintsData);
		}
		catch (\Exception $ex) {
			$finalError = $ex;
		}

		//if we couldn't route, but we didn't encounter an exception
		if (!$finalController && !$finalError) {
			//imply a 'could not route' error
			$finalError = new UnresolvedRouteException(
				"Could not route: '" . $aInfo['nextRoute'] . "'."
				. "\nInitial route was: " . MiscUtils::varInfo(static::getInitialRoute())
				. "\nIterations were: " . MiscUtils::varInfo(array_values(self::$bootPath))
			);
		}

		if ($finalError) {
			try {
				//if we've already attempted to recover from a boot loop error
				if (self::$bootLoopRecoveryAttempted) {
					//don't try to recover again, to avoid causing an infinite loop
					throw new Exception(
						"Unrecoverable boot loop error.",
						0,
						$finalError
					);
				}

				//attempt to handle the error and recover
				self::$bootLoopRecoveryAttempted = true;
				$stubController->handleException($finalError);
			}
			catch (\Exception $ex) {
				//if we get here, we couldn't even handle the exception, so it's game over man, game over...
				Env::getLogger()->error($ex);
			}

			unset($stubController);
		}

		else {
			unset($stubController);

			if ($finalController) {
				if (\App\DEBUG && Env::getVars()->get('debugMemUsage')) {
					Env::getLogger()->debug('mem[before go]: ' . ceil(memory_get_usage()/1024) . 'K');
				}

				$finalController->go();
			}
		}
	}

	private $code;
	private $hints;
	private $input;
	private $model;
	private $view;
	private $defaultViewType;
	private $options;
	private $plugins;
	private $proxy;

	protected function resolvePlugins() {

	}

	protected function resolveOptions() {

	}

	public function resolveController($aInfo, $aHintsData = null) {
		//this remains true until the boot loop stops.
		//During each iteration of the boot loop, controllers are created and asked to provide the next step in the route.
		//Once the same step is returned twice (i.e. no movement), we consider the route successfully processed, and the
		//last created controller is returned. Note that the controller will have a model and input already attached.
		//If any controller during the loop routes to null, we stop and consider the route unsuccessfully processed.
		$keepRouting = true;

		//the temporary boot info passed along through the boot loop
		$tempInfo = $aInfo;

		/** @var Controller $tempController */
		$tempController = null;

		$model = $this->createModel();
		$model->init();

		$input = $this->createInput();
		$input->importFromGlobals();

		$hints = $this->createHints();
		if ($aHintsData) $hints->merge($aHintsData);

		$loopCount = 0;
		do {
			if ($tempInfo != null) {
				//normalize the boot info
				$tempInfo = array_replace([
					'moduleCode' => '',
					'nextRoute' => '',
				], $tempInfo);

				//create a unique key representing this iteration of the loop.
				//This is used to detect infinite loops, due to a later iteration routing back to an earlier iteration
				$tempIteration = implode('+', [
					$tempInfo['moduleCode'],
					$tempInfo['nextRoute'],
				]);

				//if we don't have a temp controller yet,
				//or the temp controller is not the target controller (comparing by module code)
				//or we still have routing to do
				if ($tempController == null || $tempInfo['moduleCode'] != $tempController->getCode() || $tempInfo['nextRoute'] != null) {
					//if the current iteration has not been encountered before
					if (!array_key_exists($tempIteration, self::$bootPath)) {
						//append the current iteration to the boot path
						self::$bootPath[$tempIteration] = [
							'moduleCode' => $tempInfo['moduleCode'],
							'nextRoute' => $tempInfo['nextRoute'],
						];

						//if we already have a temp controller
						if ($tempController) {
							//tell it to create the target controller
							$tempController = $tempController::fromCode($tempInfo['moduleCode']);
							$tempController->init();
						}

						//else we don't have a controller yet
						else {
							//if the target controller's code is the same as the current controller
							if ($tempInfo['moduleCode'] == $this->getCode()) {
								//use the current controller as the target controller
								$tempController = $this;
							}

							//else the target controller's code is different that the current controller
							else {
								//tell the current controller to create the target controller
								$tempController = $this::fromCode($tempInfo['moduleCode']);
								$tempController->init();
							}
						}

						//attach the hints to the new temp controller
						$tempController->setHints($hints);

						//attach the input to the new temp controller
						$tempController->setInput($input);

						//attach the model to the new temp controller
						$tempController->setModel($model);

						//if we have routing to do
						if ($tempInfo['nextRoute'] != null || $loopCount == 0) {
							//tell the temp controller to process the route
							$newInfo = $tempController->processRoute($tempInfo);

							if (\App\DEBUG && Env::getVars()->get('debugRouting')) {
								Env::getLogger()->debug(get_class($tempController) . ' routed from -> to: ' . MiscUtils::varInfo($tempInfo) . ' -> ' . MiscUtils::varInfo($newInfo));
							}

							$tempInfo = $newInfo;
							unset($newInfo);

							//if we get here, the next iteration of the boot loop will now occur
						}
					}

					//else the current iteration is a duplication of an earlier iteration
					else {
						//we have detected an infinite boot loop, and cannot resolve the controller

						$tempController = null;
						$keepRouting = false;

						//append the current iteration to the boot path
						self::$bootPath[$tempIteration] = [
							'moduleCode' => $tempInfo['moduleCode'],
							'nextRoute' => $tempInfo['nextRoute'],
						];
					}
				}

				//else we don't have any routing to do
				else {
					$keepRouting = false;
				}
			}

			//else $tempInfo is null
			else {
				//if we get here, we could not resolve the final controller

				//clear any temp controller as it does not represent the final controller
				$tempController = null;

				$keepRouting = false;
			}

			$loopCount++;
		}
		while ($keepRouting);

		if ($tempController) {
			$tempController->markResolved();
		}

		return $tempController;
	}

	public function markResolved() {

	}

	public function processRoute($aInfo) {
		return null;
	}

	public function go() {
		try {
			$viewType = $this->getRequestedViewType();

			if ($viewType != null) {
				$view = $this->createView($viewType);
				$view->setController($this->getProxy());
				$view->init();

				$hintedInput = $view->getHintedInput();
				if ($hintedInput) {
					$this->getInput()->mergeReverse($hintedInput);
				}
				unset($hintedInput);

				$hints = $view->getHints();
				if ($hints) {
					$this->getHints()->mergeReverse($hints);
				}
				unset($hints);

				$view->setModel($this->getModel());

				$this->setView($view);
			}

			$this->goTasks();
			$this->goRender();
		}

		catch (\Exception $ex) {
			$this->handleException($ex);
		}
	}

	public function doTask() {

	}

	public function handleException(\Exception $aEx) {
		Env::getLogger()->error($aEx);
	}

	public function getDefaultViewType() {
		return $this->defaultViewType;
	}

	public function setDefaultViewType($aType) {
		$this->defaultViewType = (string)$aType;
	}

	public function createHints() {
		return new Hints();
	}

	public function getHints() {
		return $this->hints;
	}

	public function setHints(HintsInterface $aHints) {
		$this->hints = $aHints;
	}

	public function setInput(InputInterface $aInput) {
		$this->input = $aInput;
	}

	public function getInput() {
		return $this->input;
	}

	public function setModel(ModelInterface $aModel) {
		$this->model = $aModel;
	}

	public function getModel() {
		return $this->model;
	}

	public function createModel() {
		$code = $this->getCode();

		$component = static::getComponentResolver()->resolveComponent(
			static::getChain($code),
			'Model'
		);

		if (!$component) {
			throw new \Exception(
				"Could not resolve Model component for module '"  . $code . "'."
				. " No component class files could be found."
			);
		}

		/** @noinspection PhpIncludeInspection */
		include_once $component['includeFilePath'];

		if (!class_exists($component['className'])) {
			throw new \Exception(
				"Could not resolve Model component for module '"  . $code . "'."
				. " No component class was found in include file '" . $component['includeFilePath'] . "'."
			);
		}

		$model = new $component['className']($code);

		return $model;
	}

	public function createView($aType) {
		$code = $this->getCode();

		$component = static::getComponentResolver()->resolveComponent(
			static::getChain($code),
			'View',
			$aType
		);

		if (!$component) {
			throw new \Exception(
				"Could not resolve " . $aType . " View component for module '"  . $code . "'."
				. " No component class files could be found."
			);
		}

		/** @noinspection PhpIncludeInspection */
		include_once $component['includeFilePath'];

		if (!class_exists($component['className'])) {
			throw new \Exception(
				"Could not resolve " . $aType . " View component for module '"  . $code . "'."
				. " No component class was found in include file '" . $component['includeFilePath'] . "'."
			);
		}

		$view = new $component['className']($code);

		return $view;
	}

	public function getView() {
		return $this->view;
	}

	public function setView(ViewInterface $aView) {
		$this->view = $aView;
	}

	public function getProxy() {
		if (!$this->proxy) {
			$this->proxy = new ControllerProxy($this);
		}

		return $this->proxy;
	}

	public function getCode() {
		return $this->code;
	}

	public function getOptions() {
		if (!$this->options) {
			$this->options = new Options();
		}

		return $this->options;
	}

	public function getPlugins() {
		if (!$this->plugins) {
			$this->plugins = new ControllerPlugins($this);
		}

		return $this->plugins;
	}

	public function init() {
		//this method provides a hook to resolve plugins, options, etc.

		$this->resolvePlugins();
		$this->resolveOptions();
	}

	public function __construct($aCode) {
		if (\App\DEBUG && Env::getVars()->get('debugComponentLifetimes')) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $aCode . "] was constructed");
		}

		$this->code = (string)$aCode;
	}

	public function __destruct() {
		if (\App\DEBUG && Env::getVars()->get('debugComponentLifetimes')) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $this->getCode() . "] was destructed");
		}
	}
}
