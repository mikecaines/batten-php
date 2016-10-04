<?php
namespace Solarfield\Batten;

use Solarfield\Ok\EventTargetTrait;

abstract class ControllerPlugin {
	use EventTargetTrait;

	private $controller;
	private $componentCode;
	private $proxy;

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
		if (!$this->proxy) {
			$this->proxy = new ControllerPluginProxy($this);
		}

		return $this->proxy;
	}

	public function __construct(ControllerInterface $aController, $aComponentCode) {
		$this->controller = $aController;
		$this->componentCode = (string)$aComponentCode;
	}
}
