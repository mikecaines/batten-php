<?php
namespace batten;

include_once __DIR__ . '/EventInterface.php';

class Event implements EventInterface {
	private $type;
	private $target;
	private $relatedTarget;

	public function getType() {
		return $this->type;
	}

	public function getTarget() {
		return $this->target;
	}

	public function getRelatedTarget() {
		return $this->relatedTarget;
	}

	public function __construct($aType, $aInfo = []) {
		$this->type = (string)$aType;
		$this->target = array_key_exists('target', $aInfo) ? $aInfo['target'] : null;
		$this->relatedTarget = array_key_exists('relatedTarget', $aInfo) ? $aInfo['relatedTarget'] : null;
	}
}
