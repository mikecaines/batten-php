<?php
namespace batten;

interface InputInterface extends \ok_ToArrayInterface {
	public function importFromGlobals();

	public function merge($aData);

	/**
	 * @param string $aPath
	 * @param array|string $aValue
	 */
	public function set($aPath, $aValue);

	/**
	 * @param $aPath
	 * @return string
	 */
	public function getAsString($aPath);

	/**
	 * @param $aPath
	 * @return array
	 */
	public function getAsArray($aPath);
}