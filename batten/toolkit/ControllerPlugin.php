<?php
namespace batten;

abstract class ControllerPlugin {
	private $controller;

	/**
	 * @return ControllerInterface
	 */
	public function getController() {
		return $this->controller;
	}

	public function __construct(ControllerInterface $aController) {
		$this->controller = $aController;
	}
}
