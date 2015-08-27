<?php
namespace Batten;

use Exception;

class ViewPlugins {
	private $view;
	private $items = [];

	public function register($aComponentCode, $aInstallationCode) {
		if (!array_key_exists($aInstallationCode, $this->items)) {
			$plugin = null;

			$component = $this->view->getController()->getComponentResolver()->resolveComponent(
				$this->view->getController()->getChain($this->view->getCode()),
				'ViewPlugin',
				$this->view->getType(),
				$aComponentCode
			);

			if ($component) {
				/** @noinspection PhpIncludeInspection */
				include_once $component['includeFilePath'];

				if (!class_exists($component['className'])) {
					throw new Exception(
						"Class class '" . $component['className'] . "'"
						. " was not found in file '" . $component['includeFilePath'] . "'."
					);
				}

				$plugin = new $component['className']($this->view, $aComponentCode);
			}

			$this->items[$aInstallationCode] = [
				'plugin' => $plugin,
				'componentCode' => $aComponentCode,
			];
		}
	}

	/**
	 * @param string $aInterface
	 * @param string $aInstallationCode
	 * @return ViewPlugin|null
	 * @throws Exception
	 */
	public function get($aInterface, $aInstallationCode) {
		if (array_key_exists($aInstallationCode, $this->items) && $this->items[$aInstallationCode]['plugin']) {
			if (!($this->items[$aInstallationCode]['plugin'] instanceof $aInterface)) {
				throw new Exception(
					"Plugin installed at '" . $aInstallationCode
					. "', is of type '" . get_class($this->items[$aInstallationCode]['plugin'])
					. "', which does not implement interface '" . $aInterface . "'."
				);
			}

			return $this->items[$aInstallationCode]['plugin'];
		}

		return null;
	}

	public function getRegistrations() {
		$registrations = [];

		foreach ($this->items as $k => $item) {
			$registrations[] = [
				'componentCode' => $item['componentCode'],
				'installationCode' => $k,
			];
		}

		return $registrations;
	}

	public function __construct(View $aView) {
		$this->view = $aView;
	}
}
