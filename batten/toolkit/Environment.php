<?php
namespace batten;

include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-misc.php';

abstract class Environment {
	static private $requestId;
	static private $logger;
	static private $standardOutput;

	static public function getRequestId() {
		if (!self::$requestId) {
			self::$requestId = ok_guid();
		}

		return self::$requestId;
	}

	/**
	 * @return Logger
	 */
	static public function getLogger() {
		if (!self::$logger) {
			include_once __DIR__ . '/Logger.php';
			self::$logger = new Logger();
		}

		return self::$logger;
	}

	/**
	 * @return StandardOutput
	 */
	static public function getStandardOutput() {
		if (!self::$standardOutput) {
			include_once __DIR__ . '/StandardOutput.php';
			self::$standardOutput = new StandardOutput();
		}

		return self::$standardOutput;
	}
}
