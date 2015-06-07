<?php
namespace batten;

abstract class ViewPlugin {
	private $view;
	private $code;

	/**
	 * @return \batten\View
	 */
	public function getView() {
		return $this->view;
	}

	public function getCode() {
		return $this->code;
	}

	public function __construct(ViewInterface $aView, $aCode) {
		$this->view = $aView;
		$this->code = (string)$aCode;
	}
}
