<?php
namespace Batten;

abstract class ControllerPlugin {
	use EventTargetTrait;

	private $controller;
	private $code;
	private $options;

	public function getController() {
		return $this->controller;
	}

	public function getOptions() {
		if (!$this->options) {
			$this->options = new Options();
		}

		return $this->options;
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
