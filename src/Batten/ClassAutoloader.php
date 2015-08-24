<?php
namespace Batten;

use App\Environment as Env;
use Ok\StructUtils;

class ClassAutoloader {
	private $controller;

	public function handleClassAutoload($aClass) {
		if (preg_match('/^(?:(.+)\\\\)?(.+)$/', $aClass, $matches)) {
			$namespace = $matches[1];
			$className = $matches[2];

			$chain = array_reverse($this->controller->getChain($this->controller->getCode()));

			foreach ($chain as $link) {
				$link = array_replace([
					'namespace' => null,
					'path' => null,
				], $link);

				if ($link['namespace'] === $namespace) {
					$tempPath = $link['path'] . DIRECTORY_SEPARATOR . $className . '.php';

					if (file_exists($tempPath)) {
						/** @noinspection PhpIncludeInspection */
						include_once $tempPath;

						if (\Batten\DEBUG_CLASS_AUTOLOAD) {
							Env::getLogger()->debug('Autoloaded class ' . $aClass . ' from file ' . $tempPath . '.');
						}

						break;
					}
				}
			}
		}
	}

	public function __construct(ControllerInterface $aController) {
		$this->controller = $aController;
	}
}
