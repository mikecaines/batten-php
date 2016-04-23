<?php
namespace Solarfield\Batten;

use App\Environment as Env;
use Exception;

class ControllerPlugins {
	private $controller;
	private $items = [];
	private $itemsByClass = [];

	public function register($aComponentCode, $aInstallationCode = null, $aOptions = []) {
		$installationCode = $aInstallationCode != null ? $aInstallationCode : lcfirst($aComponentCode);

		if (array_key_exists($installationCode, $this->items)) {
			$existingComponentCode = $this->items[$installationCode]['componentCode'];

			throw new Exception(
				"Cannot register '$aComponentCode' at '$installationCode' because '$existingComponentCode' is already there."
			);
		}

		else {
			$plugin = null;

			$component = $this->controller->getComponentResolver()->resolveComponent(
				$this->controller->getChain($this->controller->getCode()),
				'ControllerPlugin',
				null,
				$aComponentCode
			);

			if ($component) {
				/** @noinspection PhpIncludeInspection */
				include_once $component['includeFilePath'];

				if (!class_exists($component['className'])) {
					throw new Exception(
						"Class class '" . $component['className'] . "'"
						. " was not found in file '" . $component['includeFilePath'] . "'."
					);
				}

				$plugin = new $component['className']($this->controller, $aComponentCode, $installationCode);
			}

			$this->items[$installationCode] = [
				'plugin' => $plugin,
				'componentCode' => $aComponentCode,
			];
		}

		return $this->get($installationCode);
	}

	/**
	 * @param string $aInstallationCode
	 * @return ControllerPlugin|null
	 * @throws Exception
	 */
	public function get($aInstallationCode) {
		if (array_key_exists($aInstallationCode, $this->items) && $this->items[$aInstallationCode]['plugin']) {
			return $this->items[$aInstallationCode]['plugin'];
		}

		return null;
	}

	public function getByClass($aClass) {
		$plugin = null;

		if (array_key_exists($aClass, $this->itemsByClass)) {
			return $this->itemsByClass[$aClass];
		}

		else {
			foreach ($this->getRegistrations() as $registration) {
				if (($item = $this->get($registration['installationCode'])) && $item instanceof $aClass) {
					if ($plugin) {
						Env::getLogger()->warn("Could not retrieve plugin because multiple instances of " . $aClass . " are registered.");
						break;
					}

					$plugin = $item;
				}
			}

			$this->itemsByClass[$aClass] = $plugin;

			return $plugin;
		}
	}

	public function getRegistrations() {
		$registrations = [];

		foreach ($this->items as $k => $item) {
			$registrations[] = [
				'componentCode' => $item['componentCode'],
				'installationCode' => $k,
			];
		}

		return $registrations;
	}

	public function __construct(Controller $aController) {
		$this->controller = $aController;
	}
}
