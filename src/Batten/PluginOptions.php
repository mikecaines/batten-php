<?php
namespace Batten;

class PluginOptions implements \Solarfield\Ok\ToArrayInterface {
	/** @type string */ private $installationCode;
	/** @var Options */ private $options;

	public function add($aCode, $aValue) {
		$this->options->add($this->installationCode . '.' . $aCode, $aValue);
	}

	public function set($aCode, $aValue) {
		$this->options->set($this->installationCode . '.' . $aCode, $aValue);
	}

	public function get($aCode) {
		return $this->options->get($this->installationCode . '.' . $aCode);
	}

	public function has($aCode) {
		return $this->options->has($this->installationCode . '.' . $aCode);
	}

	public function toArray() {
		return $this->options->toArray();
	}

	function __construct($aInstallationCode, Options $aParentOptions) {
		$this->options = $aParentOptions;
		$this->installationCode = $aInstallationCode;
	}
}
