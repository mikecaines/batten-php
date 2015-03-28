<?php
namespace batten;

abstract class ViewPlugin {
	private $view;

	/**
	 * @return \batten\View
	 */
	public function getView() {
		return $this->view;
	}

	public function __construct(ViewInterface $aView) {
		$this->view = $aView;
	}
}
