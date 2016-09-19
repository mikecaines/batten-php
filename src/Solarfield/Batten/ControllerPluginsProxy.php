<?php
namespace Solarfield\Batten;

class ControllerPluginsProxy {
	private $plugins;

	/** @var ControllerPluginProxy[] */
	private $proxies = [];

	protected function getActualPlugins() {
		return $this->plugins;
	}

	public function get($aComponentCode) {
		$proxy = null;

		if (array_key_exists($aComponentCode, $this->proxies)) {
			$proxy = $this->proxies[$aComponentCode];
		}

		else {
			$plugin = $this->getActualPlugins()->get($aComponentCode);

			if ($plugin) {
				$proxy = $plugin->getProxy();
				$this->proxies[$aComponentCode] = $proxy;
			}
		}

		return $proxy;
	}

	public function getRegistrations() {
		return $this->plugins->getRegistrations();
	}

	public function __construct(ControllerPlugins $aPlugins) {
		$this->plugins = $aPlugins;
	}
}
