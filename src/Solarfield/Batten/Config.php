<?php
namespace Solarfield\Batten;

class Config {
	private $data;

	public function get($aName) {
		return array_key_exists($aName, $this->data) ? $this->data[$aName] : null;
	}

	public function __construct(array $aData) {
		$this->data = $aData;
	}
}