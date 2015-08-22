<?php
namespace Batten;

interface ViewInterface {
	public function getCode();

	public function setModel(ModelInterface $aModel);

	/**
	 * @return ModelInterface|null
	 */
	public function getModel();

	/**
	 * @return InputInterface
	 */
	public function getHintedInput();

	/**
	 * @return HintsInterface
	 */
	public function getHints();

	public function setController(ViewControllerProxyInterface $aController);

	/**
	 * @return ViewControllerProxyInterface|null
	 */
	public function getController();

	public function render();

	public function addEventListener($aEventType, $aListener);

	public function init();
}
