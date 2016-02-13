<?php
namespace Solarfield\Batten;

require_once \App\DEPENDENCIES_FILE_PATH . '/solarfield/ok-kit-php/src/Solarfield/Ok/ToArrayInterface.php';

use Solarfield\Ok\ToArrayInterface;

class Flags implements ToArrayInterface {
	private $data = [];

	public function set($aCode) {
		$this->data[(string)$aCode] = null;
	}

	public function has($aCode) {
		return array_key_exists($aCode, $this->data);
	}

	public function toArray() {
		return array_keys($this->data);
	}
}
