<?php
namespace batten;

include_once __DIR__ . '/Event.php';

class StandardOutputEvent extends Event {
	private $output = '';

	public function getText() {
		return $this->output;
	}

	public function __construct(StandardOutput $aStandardOutput, $aOutput) {
		parent::__construct('standard-output', [
			'target' => $aStandardOutput,
		]);

		$this->output = (string)$aOutput;
	}
}
