<?php
namespace Batten;

use Exception;
use Ok\MiscUtils;

require_once __DIR__ . '/main.php';

abstract class Environment {
	static private $logger;
	static private $standardOutput;
	static private $options;

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
		if (!self::$options) {
			require __DIR__ . '/Options.php';
			self::$options = new Options(['readOnly'=>true]);
		}

		return self::$options;
	}

	static public function init($aOptions) {
		$options = static::getOptions();

		$options->add('requestId', MiscUtils::guid());
		$options->add('appDependenciesFilePath', APP_DEPENDENCIES_FILE_PATH);


		//validate app package file path

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

		$options->add('appPackageFilePath', $path);
	}
}
