<?php
namespace Batten;

interface ControllerInterface {
	/**
	 * @param string $aCode
	 * @return ControllerInterface
	 */
	static public function fromCode($aCode);

	/**
	 * @param string $aCode
	 * @return array
	 */
	static public function getChain($aCode);

	/**
	 * @return ComponentResolver
	 */
	static public function getComponentResolver();

	static public function boot();

	/**
	 * @param array $aInfo
	 * @param array|null $aHintsData
	 * @return
	 */
	static public function reboot($aInfo, $aHintsData = null);

	/**
	 * @return string
	 */
	static public function getInitialRoute();

	/**
	 * @param $aInfo
	 * @param array|null $aHintsData
	 * @return ControllerInterface|null
	 */
	public function resolveController($aInfo, $aHintsData = null);

	/**
	 * Called if the controller is resolved to the final controller in resolveController().
	 */
	public function markResolved();

	public function processRoute($aInfo);

	public function go();

	public function goTasks();

	public function goRender();

	public function doTask();

	public function handleException(\Exception $aEx);

	/**
	 * @return string|null
	 */
	public function getDefaultViewType();

	/**
	 * @return string|null
	 */
	public function getRequestedViewType();

	/**
	 * @return HintsInterface
	 */
	public function createHints();

	/**
	 * @return HintsInterface|null
	 */
	public function getHints();

	public function setHints(HintsInterface $aHints);

	/**
	 * @return InputInterface
	 */
	public function createInput();

	/**
	 * @return InputInterface|null
	 */
	public function getInput();

	public function setInput(InputInterface $aInput);

	/**
	 * @return ModelInterface
	 */
	public function createModel();

	/**
	 * @return ModelInterface|null
	 */
	public function getModel();

	public function setModel(ModelInterface $aModel);

	/**
	 * @param string $aType
	 * @return ViewInterface
	 */
	public function createView($aType);

	/**
	 * @return ViewInterface|null
	 */
	public function getView();

	public function setView(ViewInterface $aView);

	public function getCode();

	/**
	 * @return ControllerPlugins
	 */
	public function getPlugins();

	/**
	 * @return Options
	 */
	public function getOptions();

	public function addEventListener($aEventType, $aListener);

	public function init();
}
