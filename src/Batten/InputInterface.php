<?php
namespace Batten;

use Ok\ToArrayInterface;

interface InputInterface extends ToArrayInterface {
	public function importFromGlobals();

	public function merge($aData);

	public function mergeReverse($aData);

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
