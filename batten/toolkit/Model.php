<?php
namespace batten;

use \app\Environment as Env;

include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-struct.php';
include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok_ToArrayInterface.php';
include_once __DIR__ . '/ModelInterface.php';

class Model implements ModelInterface {
	private $code;
	private $data = [];

	public function getCode() {
		return $this->code;
	}

	public function set($aPath, $aObject) {
		ok_arraySet($this->data, $aPath, $aObject);
	}

	public function push($aPath, $aObject) {
		ok_arrayPushSet($this->data, $aPath, $aObject);
	}

	public function merge($aData) {
		$this->data = ok_arrayMergeStruct($this->data, $aData);
	}

	public function get($aPath) {
		return ok_arrayGet($this->data, $aPath);
	}

	public function getAsArray($aPath) {
		$value = ok_arrayGet($this->data, $aPath);
		return is_array($value) ? $value : [];
	}

	public function toArray() {
		return $this->data;
	}

	public function init() {
		//this method provides a hook to resolve plugins, options, etc.
	}

	public function __construct($aCode) {
		if (DEBUG_COMPONENT_LIFETIMES) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $aCode . "] was constructed.");
		}

		$this->code = (string)$aCode;
	}

	public function __destruct() {
		if (DEBUG_COMPONENT_LIFETIMES) {
			Env::getLogger()->debug(get_class($this) . "[code=" . $this->getCode() . "] was destructed.");
		}
	}
}
