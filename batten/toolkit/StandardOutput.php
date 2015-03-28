<?php
namespace batten;

include_once __DIR__ . '/EventTargetTrait.php';
include_once __DIR__ . '/StandardOutputEvent.php';

class StandardOutput {
	use EventTargetTrait;

	public function write($aText) {
		$this->dispatchEvent(new StandardOutputEvent($this, $aText));
	}
}
