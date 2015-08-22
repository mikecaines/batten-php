<?php
namespace Batten;

class ViewControllerProxy implements ViewControllerProxyInterface {
	private $controller;
	private $plugins;

	public function getComponentResolver() {
		return $this->controller->getComponentResolver();
	}

	public function getChain($aCode) {
		return $this->controller->getChain($aCode);
	}

	public function createHints() {
		return $this->controller->createHints();
	}

	public function createInput() {
		return $this->controller->createInput();
	}

	public function createView($aType) {
		return $this->controller->createView($aType);
	}

	public function getPlugins() {
		if (!$this->plugins) {
			$this->plugins = new ControllerPluginsProxy($this->controller->getPlugins());
		}

		return $this->plugins;
	}

	public function addEventListener($aType, $aListener) {
		$this->controller->addEventListener($aType, $aListener);
	}

	public function __construct(ControllerInterface $aController) {
		$this->controller = $aController;
	}
}
