<?php
namespace Batten;

abstract class ControllerPlugin {
	private $controller;
	private $code;

	public function getController() {
		return $this->controller;
	}

	public function getCode() {
		return $this->code;
	}

	public function getViewProxy() {
		return null;
	}

	public function __construct(ControllerInterface $aController, $aCode) {
		$this->controller = $aController;
		$this->code = (string)$aCode;
	}
}
