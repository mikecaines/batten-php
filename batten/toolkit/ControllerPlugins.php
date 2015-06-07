<?php
namespace batten;

class ControllerPlugins {
	private $controller;
	private $items = [];

	public function register($aPluginCode) {
		$plugin = null;

		if (!array_key_exists($aPluginCode, $this->items)) {
			$component = $this->controller->getComponentResolver()->resolveComponent(
				$this->controller->getChain($this->controller->getCode()),
				'ControllerPlugin',
				null,
				$aPluginCode
			);

			if ($component) {
				/** @noinspection PhpIncludeInspection */
				include_once $component['includeFilePath'];

				$plugin = new $component['className']($this->controller, $aPluginCode);
			}

			$this->items[$aPluginCode] = $plugin;
		}

		return $plugin;
	}

	/**
	 * @param string $aPluginCode
	 * @return ControllerPlugin|null
	 */
	public function get($aPluginCode) {
		return array_key_exists($aPluginCode, $this->items)
			? $this->items[$aPluginCode]
			: null
		;
	}

	public function getRegisteredCodes() {
		return array_keys($this->items);
	}

	public function __construct(Controller $aController) {
		$this->controller = $aController;
	}
}
