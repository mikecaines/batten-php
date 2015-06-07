<?php
namespace batten;

include_once \batten\OKKIT_PKG_FILE_PATH . '/toolkit/ok_ToArrayInterface.php';

class Options implements \ok_ToArrayInterface {
	private $data = [];

	public function add($aCode, $aValue) {
		if (!$this->has($aCode)) {
			$this->set($aCode, $aValue);
		}
	}

	public function set($aCode, $aValue) {
		if (!(is_scalar($aValue) || $aValue === null)) {
			throw new \Exception(
				"Option values must be scalar or null."
			);
		}

		$this->data[(string)$aCode] = $aValue;
	}

	public function get($aCode) {
		if (!array_key_exists($aCode, $this->data)) {
			throw new \Exception(
				"Unknown option: '" . $aCode . "'."
			);
		}

		return $this->data[$aCode];
	}

	public function has($aCode) {
		return array_key_exists($aCode, $this->data);
	}

	public function toArray() {
		return $this->data;
	}
}
