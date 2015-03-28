<?php
namespace batten;

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

	public function setController(ViewControllerProxyInterface $aController);

	/**
	 * @return ViewControllerProxyInterface|null
	 */
	public function getController();

	public function render();

	public function init();
}
