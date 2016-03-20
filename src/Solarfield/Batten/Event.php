<?php
namespace Solarfield\Batten;

class Event implements EventInterface {
	private $type;
	private $target;

	public function getType() {
		return $this->type;
	}

	public function getTarget() {
		return $this->target;
	}

	public function __construct($aType, $aInfo = []) {
		$this->type = (string)$aType;
		$this->target = array_key_exists('target', $aInfo) ? $aInfo['target'] : null;
	}
}
