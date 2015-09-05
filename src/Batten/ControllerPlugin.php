<?php
namespace Batten;

abstract class ControllerPlugin {
	use EventTargetTrait;

	private $controller;
	private $code;

	public function getController() {
		return $this->controller;
	}

	public function getCode() {
		return $this->code;
	}

	/**
	 * @return ControllerPluginProxy|null
	 */
	public function getProxy() {
		return null;
	}

	public function __construct(ControllerInterface $aController, $aCode) {
		$this->controller = $aController;
		$this->code = (string)$aCode;
	}
}
