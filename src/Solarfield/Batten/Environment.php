<?php
namespace Solarfield\Batten;

use ErrorException;
use Exception;
use Solarfield\Ok\MiscUtils;

abstract class Environment {
	static private $logger;
	static private $standardOutput;
	static private $vars;
	static private $config;

	/**
	 * @return Config
	 */
	static public function getConfig() {
		return self::$config;
	}

	static public function getBaseChain() {
		return $chain = [
			'solarfield/batten-php' => [
				'namespace' => __NAMESPACE__,
				'path' => __DIR__,
			],

			'app' => [
				'namespace' => 'App',
				'path' => static::getVars()->get('appPackageFilePath') . '/App',
				'exposeToClient' => true,
			],
		];
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

	static public function getVars() {
		if (!self::$vars) {
			require_once __DIR__ . '/Options.php';
			self::$vars = new Options(['readOnly'=>true]);
		}

		return self::$vars;
	}

	static public function init($aOptions) {
		set_error_handler(function ($aNumber, $aMessage, $aFile, $aLine) {
			throw new ErrorException($aMessage, 0, $aNumber, $aFile, $aLine);
		});

		error_reporting(E_ALL | E_STRICT);

		$vars = static::getVars();


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

		$vars->add('appPackageFilePath', $path);


		//include the config
		require_once __DIR__ . '/Config.php';
		$path = $vars->get('appPackageFilePath') . '/config.php';
		/** @noinspection PhpIncludeInspection */
		self::$config = new Config(file_exists($path) ? MiscUtils::extractInclude($path) : []);

		//define low level debug flag
		if (!defined('App\DEBUG')) define('App\DEBUG', false);


		if (\App\DEBUG) {
			$config = static::getConfig();

			$vars->add('debugComponentResolution', (bool)$config->get('debugComponentResolution'));
			$vars->add('debugComponentLifetimes', (bool)$config->get('debugComponentLifetimes'));
			$vars->add('debugMemUsage', (bool)$config->get('debugMemUsage'));
			$vars->add('debugPaths', (bool)$config->get('debugPaths'));
			$vars->add('debugRouting', (bool)$config->get('debugRouting'));
			$vars->add('debugReflection', (bool)$config->get('debugReflection'));
			$vars->add('debugClassAutoload', (bool)$config->get('debugClassAutoload'));
		}
	}
}
