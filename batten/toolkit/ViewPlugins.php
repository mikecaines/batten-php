<?php
namespace batten;

class ViewPlugins {
	private $view;
	private $items = [];

	public function register($aPluginCode) {
		$plugin = null;

		if (!array_key_exists($aPluginCode, $this->items)) {
			$component = $this->view->getController()->getComponentResolver()->resolveComponent(
				$this->view->getController()->getChain($this->view->getCode()),
				'ViewPlugin',
				$this->view->getType(),
				$aPluginCode
			);

			if ($component) {
				/** @noinspection PhpIncludeInspection */
				include_once $component['includeFilePath'];

				$plugin = new $component['className']($this->view, $aPluginCode);
			}

			$this->items[$aPluginCode] = $plugin;
		}

		return $plugin;
	}

	public function get($aPluginCode) {
		return array_key_exists($aPluginCode, $this->items)
			? $this->items[$aPluginCode]
			: null
		;
	}

	public function getRegisteredCodes() {
		return array_keys($this->items);
	}

	public function __construct(View $aView) {
		$this->view = $aView;
	}
}
