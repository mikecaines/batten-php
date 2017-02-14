<?php
namespace Solarfield\Batten;

use Solarfield\Ok\EventTargetTrait;

abstract class ViewPlugin {
	use EventTargetTrait;
	
	private $view;
	private $componentCode;

	/**
	 * @return \Solarfield\Batten\ViewInterface
	 */
	public function getView() {
		return $this->view;
	}

	public function getCode() {
		return $this->componentCode;
	}

	public function __construct(ViewInterface $aView, $aComponentCode) {
		$this->view = $aView;
		$this->componentCode = (string)$aComponentCode;
	}
}
