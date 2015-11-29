<?php
namespace Batten;

class PluginHints implements \Solarfield\Ok\ToArrayInterface {
	/** @type string */ private $installationCode;
	/** @var Hints */ private $hints;

	public function set($aCode, $aValue) {
		$this->hints->set($this->installationCode . '.' . $aCode, $aValue);
	}

	public function get($aCode) {
		return $this->hints->get($this->installationCode . '.' . $aCode);
	}

	public function toArray() {
		return $this->hints->toArray();
	}

	function __construct($aInstallationCode, HintsInterface $aParentHints) {
		$this->hints = $aParentHints;
		$this->installationCode = $aInstallationCode;
	}
}
