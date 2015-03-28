<?php
namespace batten;

use app\Environment as Env;

include_once __DIR__ . '/LoggerInterface.php';

class Logger implements LoggerInterface {
	protected function processEntry($aMessage, $aContext = null, $aType) {
		$output = '[request=' . Env::getRequestId() . ']';

		$output .= ' [date=' . date('c') . ']';

		switch ($aType) {
			case \batten\LOG_LEVEL_INFO: $typeName = 'INFO'; break;
			case \batten\LOG_LEVEL_WARNING: $typeName = 'WARNING'; break;
			case \batten\LOG_LEVEL_ERROR: $typeName = 'ERROR'; break;
			case \batten\LOG_LEVEL_DEBUG: $typeName = 'DEBUG'; break;
			default: $typeName = 'OTHER';
		}
		$output .= ' [type=' . $typeName . ']';

		$output .= ': ' . $aMessage;

		if ($aContext !== null) {
			$output .= ' Details: ' . ok_varInfo($aContext);
		}

		error_log($output);
	}

	public function info($aMessage, $aContext = null) {
		$this->processEntry($aMessage, $aContext, LOG_LEVEL_INFO);
	}

	public function warn($aMessage, $aContext = null) {
		$this->processEntry($aMessage, $aContext, LOG_LEVEL_WARNING);
	}

	public function error($aMessage, $aContext = null) {
		$this->processEntry($aMessage, $aContext, LOG_LEVEL_ERROR);
	}

	public function debug($aMessage, $aContext = null) {
		$this->processEntry($aMessage, $aContext, LOG_LEVEL_DEBUG);
	}
}
