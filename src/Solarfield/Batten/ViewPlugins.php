<?php
namespace Solarfield\Batten;

use Exception;

class ViewPlugins {
	private $view;
	private $items = [];

	public function register($aComponentCode, $aInstallationCode, $aOptions = []) {
		if (array_key_exists($aInstallationCode, $this->items)) {
			$existingComponentCode = $this->items[$aInstallationCode]['componentCode'];

			if ($aComponentCode != $existingComponentCode) {
				throw new Exception(
					"Cannot register '$aComponentCode' at '$aInstallationCode' because '$existingComponentCode' is already there."
				);
			}
		}

		else {
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

				$plugin = new $component['className']($this->view, $aComponentCode, $aInstallationCode);
			}

			$this->items[$aInstallationCode] = [
				'plugin' => $plugin,
				'componentCode' => $aComponentCode,
			];
		}

		return $this->get($aInstallationCode);
	}

	/**
	 * @param string $aInstallationCode
	 * @return ViewPlugin|null
	 * @throws Exception
	 */
	public function get($aInstallationCode) {
		if (array_key_exists($aInstallationCode, $this->items) && $this->items[$aInstallationCode]['plugin']) {
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
