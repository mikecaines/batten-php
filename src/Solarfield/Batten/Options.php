<?php
namespace Solarfield\Batten;

require_once \App\DEPENDENCIES_FILE_PATH . '/solarfield/ok-kit-php/src/Solarfield/Ok/ToArrayInterface.php';

use Exception;
use Solarfield\Ok\ToArrayInterface;

class Options implements ToArrayInterface {
	private $data = [];

	public function add($aCode, $aValue) {
		if (!$this->has($aCode)) {
			$this->set($aCode, $aValue);
		}
	}

	public function set($aCode, $aValue) {
		if ($this->readOnly && $this->has($aCode)) {
			throw new Exception(
				"Option '$aCode' is read only."
			);
		}

		if (!(is_scalar($aValue) || $aValue === null)) {
			throw new Exception(
				"Option values must be scalar or null."
			);
		}

		$this->data[(string)$aCode] = $aValue;
	}

	public function get($aCode) {
		if (!$this->has($aCode)) {
			throw new Exception(
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

	function __construct($aOptions = []) {
		$this->readOnly = array_key_exists('readOnly', $aOptions) ? (bool)$aOptions['readOnly'] : false;
	}
}
