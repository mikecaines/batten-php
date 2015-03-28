<?php
namespace batten;

interface ModelInterface extends \ok_ToArrayInterface {
	public function getCode();
	public function set($aPath, $aObject);
	public function merge($aData);
	public function get($aPath);
	public function getAsArray($aPath);
	public function init();
}
