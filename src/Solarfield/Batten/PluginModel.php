<?php
namespace Solarfield\Batten;

class PluginModel {
	/** @type string */ private $installationCode;
	/** @var Model */ private $model;

	public function set($aPath, $aObject) {
		$this->model->set($this->installationCode . '.' . $aPath, $aObject);
	}

	public function push($aPath, $aObject) {
		$this->model->push($this->installationCode . '.' . $aPath, $aObject);
	}

	public function get($aPath) {
		return $this->model->get($this->installationCode . '.' . $aPath);
	}

	public function getAsArray($aPath) {
		return $this->model->getAsArray($this->installationCode . '.' . $aPath);
	}

	public function toArray() {
		return $this->model->toArray();
	}

	function __construct($aInstallationCode, ModelInterface $aParentModel) {
		$this->model = $aParentModel;
		$this->installationCode = $aInstallationCode;
	}
}
