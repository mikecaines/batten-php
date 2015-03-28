<?php
namespace batten;

include_once __DIR__ . '/ControllerPlugins.php';

class ControllerPluginsProxy {
	private $plugins;

	public function getRegisteredCodes() {
		return $this->plugins->getRegisteredCodes();
	}

	public function __construct(ControllerPlugins $aPlugins) {
		$this->plugins = $aPlugins;
	}
}
