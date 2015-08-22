<?php
namespace Batten;

use Ok\StructUtils;

class Hints implements HintsInterface {
	private $data = [];

	public function getAsString($aPath) {
		return StructUtils::get($this->data, $aPath);
	}

	public function getAsArray($aPath) {
		$value = StructUtils::get($this->data, $aPath);
		return is_array($value) ? $value : [];
	}

	public function toArray() {
		return $this->data;
	}

	public function set($aPath, $aValue) {
		StructUtils::set($this->data, $aPath, $aValue);
	}

	public function merge($aData) {
		$incomingData = StructUtils::toArray($aData, true);
		$incomingData = StructUtils::unflatten($incomingData, '.');

		$this->data = StructUtils::merge($this->data, $incomingData);
	}

	public function mergeReverse($aData) {
		$incomingData = StructUtils::toArray($aData, true);
		$incomingData = StructUtils::unflatten($incomingData, '.');

		$this->data = StructUtils::merge($incomingData, $this->data);
	}
}
