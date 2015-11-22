<?php
namespace Batten;

abstract class ControllerPlugin {
	use EventTargetTrait;

	private $controller;
	private $componentCode;
	private $installationCode;
	private $pluginOptions;
	private $pluginHints;
	private $pluginModel;
	private $lastHints;
	private $lastModel;

	/**
	 * @return ControllerInterface
	 */
	public function getController() {
		return $this->controller;
	}

	public function getOptions() {
		if (!$this->pluginOptions) {
			$this->pluginOptions = new PluginOptions($this->getInstallationCode(), $this->getController()->getOptions());
		}

		return $this->pluginOptions;
	}

	public function getHints() {
		$currentHints = $this->getController()->getHints();

		//if we don't have a PluginHints yet, or the parent's Hints changed
		if (!$this->pluginHints || $currentHints !== $this->lastHints) {
			if ($currentHints) {
				$this->pluginHints = new PluginHints($this->getInstallationCode(), $currentHints);
			}
			else {
				$this->pluginHints = null;
			}

			$this->lastHints = $currentHints;
		}

		return $this->pluginHints;
	}

	public function getModel() {
		$currentModel = $this->getController()->getModel();

		//if we don't have a PluginModel yet, or the parent's Model changed
		if (!$this->pluginModel || $currentModel !== $this->lastModel) {
			if ($currentModel) {
				$this->pluginModel = new PluginModel($this->getInstallationCode(), $currentModel);
			}
			else {
				$this->pluginModel = null;
			}

			$this->lastModel = $currentModel;
		}

		return $this->pluginModel;
	}

	public function getCode() {
		return $this->componentCode;
	}

	public function getInstallationCode() {
		return $this->installationCode;
	}

	/**
	 * @return ControllerPluginProxy|null
	 */
	public function getProxy() {
		return null;
	}

	public function __construct(ControllerInterface $aController, $aComponentCode, $aInstallationCode) {
		$this->controller = $aController;
		$this->componentCode = (string)$aComponentCode;
		$this->installationCode = (string)$aInstallationCode;
	}
}
