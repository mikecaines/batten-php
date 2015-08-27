<?php
namespace Batten;

use Exception;

class ControllerPlugins {
	private $controller;
	private $items = [];

	public function register($aComponentCode, $aInstallationCode) {
		if (!array_key_exists($aInstallationCode, $this->items)) {
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

				$plugin = new $component['className']($this->controller, $aComponentCode);
			}

			$this->items[$aInstallationCode] = [
				'plugin' => $plugin,
				'componentCode' => $aComponentCode,
			];
		}
	}

	/**
	 * @param string $aInterface
	 * @param string $aInstallationCode
	 * @return ControllerPlugin|null
	 * @throws Exception
	 */
	public function get($aInterface, $aInstallationCode) {
		if (array_key_exists($aInstallationCode, $this->items) && $this->items[$aInstallationCode]['plugin']) {
			if (!($this->items[$aInstallationCode]['plugin'] instanceof $aInterface)) {
				throw new Exception(
					"Plugin installed at '" . $aInstallationCode
					. "', is of type '" . get_class($this->items[$aInstallationCode]['plugin'])
					. "', which does not implement interface '" . $aInterface . "'."
				);
			}

			return $this->items[$aInstallationCode]['plugin'];
		}

		return null;
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
