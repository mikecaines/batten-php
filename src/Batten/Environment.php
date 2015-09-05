<?php
namespace Batten;

use Exception;
use Ok\MiscUtils;

require_once __DIR__ . '/main.php';

abstract class Environment {
	static private $requestId;
	static private $logger;
	static private $standardOutput;
	static private $appPackageFilePath;
	static private $options;

	static public function getAppPackageFilePath() {
		return self::$appPackageFilePath;
	}

	static public function getAppDependenciesFilePath() {
		return APP_DEPENDENCIES_FILE_PATH;
	}

	static public function getRequestId() {
		if (!self::$requestId) {
			self::$requestId = MiscUtils::guid();
		}

		return self::$requestId;
	}

	/**
	 * @return Logger
	 */
	static public function getLogger() {
		if (!self::$logger) {
			self::$logger = new Logger();
		}

		return self::$logger;
	}

	/**
	 * @return StandardOutput
	 */
	static public function getStandardOutput() {
		if (!self::$standardOutput) {
			self::$standardOutput = new StandardOutput();
		}

		return self::$standardOutput;
	}

	static public function getOptions() {
		if (!self::$options) self::$options = new Options(['readOnly'=>true]);
		return self::$options;
	}

	static public function init($aOptions) {
		//validate app package path

		if (!array_key_exists('appPackageFilePath', $aOptions)) {
			throw new Exception(
				"The appPackageFilePath option must be specified when calling " . __METHOD__ . "."
			);
		}

		$path = realpath($aOptions['appPackageFilePath']);

		if (!$path) {
			throw new Exception(
				"Invalid appPackageFilePath: '" . $aOptions['appPackageFilePath'] . "'."
			);
		}

		self::$appPackageFilePath = $path;
	}
}
