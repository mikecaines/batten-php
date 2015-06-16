<?php
namespace Batten;

abstract class ViewPlugin {
	private $view;
	private $code;

	/**
	 * @return \Batten\View
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
