<?php
namespace batten;

use app\Environment as Env;

include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-misc.php';
include_once __DIR__ . '/main.php';
include_once __DIR__ . '/ControllerInterface.php';
include_once __DIR__ . '/UnresolvedRouteException.php';
include_once __DIR__ . '/EventTargetTrait.php';
include_once __DIR__ . '/Reflector.php';

abstract class Controller implements ControllerInterface {
	use EventTargetTrait;

	static private $bootPath = [];
	static private $bootLoopError;
	static private $componentResolver;
	static private $classAutoloaderRegistered = false;

	static protected function getBaseChain() {
		return $chain = [
			'batten' => [
				'namespace' => 'batten',
				'path' => __DIR__ . '/../../batten',
				'classPath' => DIRECTORY_SEPARATOR . 'toolkit',
			],

			'app' => [
				'namespace' => 'app',
				'path' => APP_BASE_FILE_PATH,
				'classPath' => DIRECTORY_SEPARATOR . 'toolkit',
			],
		];
	}

	static public function fromCode($aCode) {
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
		$controller = new $component['className']($aCode);

		return $controller;
	}

	static public function getChain($aModuleCode) {
		$chain = static::getBaseChain();

		if ($aModuleCode != null) {
			$chain[$aModuleCode] = [
				'namespace' => 'app',
				'path' => APP_NS_FILE_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower(ok_strCamelToDash($aModuleCode)),
				'classPath' => DIRECTORY_SEPARATOR . 'toolkit',
				'moduleClassNamePart' => ucfirst($aModuleCode),
			];
		}

		return $chain;
	}

	static public function getComponentResolver() {
		if (!self::$componentResolver) {
			include_once __DIR__ . '/ComponentResolver.php';
			self::$componentResolver = new ComponentResolver();
		}

		return self::$componentResolver;
	}

	static public function boot() {
		if (DEBUG_MEM_USAGE) {
			Env::getLogger()->debug('mem[boot begin]: ' . memory_get_usage());
		}

		if (DEBUG_PATHS) {
			Env::getLogger()->debug('\batten\BATTEN_NS_FILE_PATH: '. \batten\BATTEN_NS_FILE_PATH);
		}

		include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-error.php';
		set_error_handler('ok_handleErrorAndThrowException');

		$info = [
			'moduleCode' => '',
			'nextRoute' => static::getInitialRoute(),
		];

		static::reboot($info);

		if (DEBUG_MEM_USAGE) {
			Env::getLogger()->debug('mem[boot end]: ' . memory_get_usage());
			Env::getLogger()->debug('mem-peak[boot end]: ' . memory_get_peak_usage());
		}
	}

	static public function reboot($aInfo, $aModelData = null) {
		/** @var ControllerInterface $stubController */
		$stubController = static::fromCode($aInfo['moduleCode']);
		$stubController->init();

		$finalController = null;
		$finalError = null;

		try {
			$finalController = $stubController->resolveController($aInfo, $aModelData);
		}
		catch (\Exception $ex) {
			$finalError = $ex;
		}

		//if we couldn't route, but we didn't encounter an exception
		if (!$finalController && !$finalError) {
			//imply a 'could not route' error
			$finalError = new UnresolvedRouteException(
				"Could not route: '" . $aInfo['nextRoute'] . "'."
			);
		}

		if ($finalError) {
			try {
				if (self::$bootLoopError) {
					throw self::$bootLoopError;
				}

				$stubController->handleException($finalError);
			}
			catch (\Exception $ex) {
				//if we get here, we couldn't even handle the exception, so it's game over man, game over...
				Env::getLogger()->error($ex);
				$finalController = null;
			}

			unset($stubController);
		}

		else {
			unset($stubController);

			if ($finalController) {
				if (DEBUG_MEM_USAGE) {
					Env::getLogger()->debug('mem[before go]: ' . memory_get_usage());
				}

				$finalController->go();
			}
		}
	}

	private $code;
	private $input;
	private $model;
	private $view;
	private $defaultViewType;
	private $options;
	private $plugins;
	private $classAutoloader;
	private $viewProxy;

	protected function resolvePlugins() {

	}

	protected function resolveOptions() {
		if (Reflector::inSurfaceMethodCall()) {
			$this->dispatchEvent(
				new Event('app-resolve-options', ['target' => $this])
			);

			$this->dispatchEvent(
				new Event('resolve-options', ['target' => $this])
			);
		}
	}

	/**
	 * @param array $aInfo Boot info. See reboot() for details.
	 * @param array|null $aModelData
	 * @return ControllerInterface|null
	 * @throws \Exception
	 */
	public function resolveController($aInfo, $aModelData = null) {
		//this remains true until the boot loop stops.
		//During each iteration of the boot loop, controllers are created and asked to provide the next step in the route.
		//Once the same step is returned twice (i.e. no movement), we consider the route successfully processed, and the
		//last created controller is returned. Note that the controller will have a model and input already attached.
		//If any controller during the loop routes to null, we stop and consider the route unsuccessfully processed.
		$keepRouting = true;

		//the temporary boot info passed along through the boot loop
		$tempInfo = $aInfo;

		/** @var ControllerInterface|null $tempController */
		$tempController = null;

		/** @var ModelInterface|null $tempModel */
		$tempModel = null;

		/** @var InputInterface|null $tempInput */
		$tempInput = null;

		$loopCount = 0;
		do {
			if ($tempInfo != null) {
				//normalize the boot info
				$tempInfo = ok_arrayMergeStruct([
					'moduleCode' => '',
					'nextRoute' => '',
				], $tempInfo ?: []);

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
					//if the current iteration is a duplication of an earlier iteration
					if (array_key_exists($tempIteration, self::$bootPath)) {
						//we have detected an infinite boot loop

						//store the infinite loop error (which is used by reboot() )
						self::$bootLoopError = new \Exception(
							"Infinite boot loop. Iterations were " . ok_varInfo(array_values(self::$bootPath))
						);

						//append the current iteration to the boot path
						self::$bootPath[$tempIteration] = [
							'moduleCode' => $tempInfo['moduleCode'],
							'nextRoute' => $tempInfo['nextRoute'],
						];

						throw self::$bootLoopError;
					}

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

					//attach the new input to the new temp controller
					$newInput = $tempController->createInput();
					if ($tempInput) {
						$newInput->merge($tempInput);
					}
					else {
						$newInput->importFromGlobals();
					}
					$tempInput = $newInput;
					unset($newInput);
					$tempController->setInput($tempInput);

					//attach the new model to the new temp controller
					$newModel = $tempController->createModel();
					$newModel->init();
					if ($tempModel) {
						$newModel->merge($tempModel);
					}
					else {
						if ($aModelData) {
							$newModel->merge($aModelData);
						}
					}
					$tempModel = $newModel;
					unset($newModel);
					$tempController->setModel($tempModel);

					//if we have routing to do
					if ($tempInfo['nextRoute'] != null || $loopCount == 0) {
						//tell the temp controller to process the route
						$newInfo = $tempController->processRoute($tempInfo);

						if (DEBUG_ROUTING) {
							Env::getLogger()->debug(get_class($tempController) . ' routed from -> to: ' . ok_varInfo($tempInfo) . ' -> ' . ok_varInfo($newInfo));
						}

						$tempInfo = $newInfo;
						unset($newInfo);

						//if we get here, the next iteration of the boot loop will now occur
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

		return $tempController;
	}

	public function processRoute($aInfo) {
		return null;
	}

	public function go() {
		try {
			$view = null;
			$hintedInput = null;

			$viewType = $this->getRequestedViewType();

			if ($viewType != null) {
				$view = $this->createView($viewType);
				$view->setController($this->getViewProxy());
				$view->init();

				$hintedInput = $view->getHintedInput();
				if ($hintedInput) {
					$this->getInput()->merge($hintedInput);
				}
				unset($hintedInput);

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
		//override this method to do module-specific tasks.
		//Remember to call the parent definition

		if (Reflector::inSurfaceMethodCall()) {
			$this->dispatchEvent(
				new Event('app-do-task', ['target' => $this])
			);

			$this->dispatchEvent(
				new Event('do-task', ['target' => $this])
			);
		}
	}

	public function getClassAutoloader() {
		if (!$this->classAutoloader) {
			include_once __DIR__ . '/ClassAutoloader.php';
			$this->classAutoloader = new ClassAutoloader($this);
		}

		return $this->classAutoloader;
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

	public function setInput(InputInterface $aInput) {
		$this->input = $aInput;
	}

	/**
	 * @return InputInterface|null
	 */
	public function getInput() {
		return $this->input;
	}

	public function setModel(ModelInterface $aModel) {
		$this->model = $aModel;
	}

	/**
	 * @return ModelInterface|null
	 */
	public function getModel() {
		return $this->model;
	}

	/**
	 * @return ModelInterface
	 * @throws \Exception if model cannot be created.
	 */
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

		/** @var ModelInterface $model */
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

		/** @var ViewInterface $view */
		$view = new $component['className']($code);

		return $view;
	}

	/**
	 * @return ViewInterface|null
	 */
	public function getView() {
		return $this->view;
	}

	public function setView(ViewInterface $aView) {
		$this->view = $aView;
	}

	public function getViewProxy() {
		if (!$this->viewProxy) {
			include_once __DIR__ . '/ViewControllerProxy.php';
			$this->viewProxy = new ViewControllerProxy($this);
		}

		return $this->viewProxy;
	}

	public function getCode() {
		return $this->code;
	}

	public function getOptions() {
		if (!$this->options) {
			include_once __DIR__ . '/Options.php';
			$this->options = new Options();
		}

		return $this->options;
	}

	public function getPlugins() {
		if (!$this->plugins) {
			include_once __DIR__ . '/ControllerPlugins.php';
			$this->plugins = new ControllerPlugins($this);
		}

		return $this->plugins;
	}

	public function init() {
		//this method provides a hook to resolve plugins, options, etc.

		if (!self::$classAutoloaderRegistered) {
			spl_autoload_register([$this->getClassAutoloader(), 'handleClassAutoload']);
			self::$classAutoloaderRegistered = true;
		}

		$this->resolvePlugins();
		$this->resolveOptions();
	}

	public function __construct($aCode) {
		if (DEBUG_COMPONENT_LIFETIMES) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $aCode . "] was constructed");
		}

		$this->code = (string)$aCode;
	}

	public function __destruct() {
		if (DEBUG_COMPONENT_LIFETIMES) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $this->getCode() . "] was destructed");
		}
	}
}
