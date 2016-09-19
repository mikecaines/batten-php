<?php
namespace Solarfield\Batten;

use Solarfield\Ok\EventTargetTrait;

abstract class ControllerPlugin {
	use EventTargetTrait;

	private $controller;
	private $componentCode;

	/**
	 * @return ControllerInterface
	 */
	public function getController() {
		return $this->controller;
	}

	public function getCode() {
		return $this->componentCode;
	}

	/**
	 * @return ControllerPluginProxy|null
	 */
	public function getProxy() {
		return null;
	}

	public function __construct(ControllerInterface $aController, $aComponentCode) {
		$this->controller = $aController;
		$this->componentCode = (string)$aComponentCode;
	}
}
