<?php
namespace Batten;

class ControllerPluginsProxy {
	private $plugins;

	/** @var ControllerPluginProxy[] */
	private $proxies = [];

	protected function getActualPlugins() {
		return $this->plugins;
	}

	public function get($aInterface, $aInstallationCode) {
		$proxy = null;

		if (array_key_exists($aInstallationCode, $this->proxies)) {
			$proxy = $this->proxies[$aInstallationCode];
		}

		else {
			$plugin = $this->getActualPlugins()->get('Batten\ControllerPlugin', $aInstallationCode);

			if ($plugin) {
				$proxy = $plugin->getProxy();
				$this->proxies[$aInstallationCode] = $proxy;
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
