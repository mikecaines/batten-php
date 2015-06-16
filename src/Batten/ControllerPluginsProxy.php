<?php
namespace Batten;

class ControllerPluginsProxy {
	private $plugins;

	/** @var ControllerPlugin[] */
	private $proxies = [];

	protected function getActualPlugins() {
		return $this->plugins;
	}

	public function get($aPluginCode) {
		$proxy = null;

		if (array_key_exists($aPluginCode, $this->proxies)) {
			$proxy = $this->proxies[$aPluginCode];
		}

		else {
			$plugin = $this->getActualPlugins()->get($aPluginCode);

			if ($plugin) {
				$proxy = $plugin->getViewProxy();
				$this->proxies[$aPluginCode] = $proxy;
			}
		}

		return $proxy;
	}

	public function getRegisteredCodes() {
		return $this->plugins->getRegisteredCodes();
	}

	public function __construct(ControllerPlugins $aPlugins) {
		$this->plugins = $aPlugins;
	}
}
