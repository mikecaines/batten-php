<?php
namespace Solarfield\Batten;

class StandardOutput {
	use EventTargetTrait;

	public function write($aText) {
		$this->dispatchEvent(new StandardOutputEvent($this, $aText));
	}
}
