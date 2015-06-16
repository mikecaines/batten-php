<?php
namespace Batten;

interface ViewControllerProxyInterface {
	/**
	 * @return ComponentResolver
	 */
	public function getComponentResolver();

	/**
	 * @param string $aCode
	 * @return array
	 */
	public function getChain($aCode);

	/**
	 * @param string $aType
	 * @return ViewInterface
	 */
	public function createView($aType);

	/**
	 * @return InputInterface
	 */
	public function createInput();

	public function addEventListener($aEventType, $aListener);

	/**
	 * @return ControllerPluginsProxy
	 */
	public function getPlugins();
}
