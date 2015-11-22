<?php
namespace Batten;

abstract class ViewPlugin {
	private $view;
	private $componentCode;
	private $installationCode;
	private $pluginOptions;
	private $pluginHints;
	private $pluginModel;
	private $lastHints;
	private $lastModel;

	/**
	 * @return \Batten\View
	 */
	public function getView() {
		return $this->view;
	}

	public function getOptions() {
		if (!$this->pluginOptions) {
			$this->pluginOptions = new PluginOptions($this->getInstallationCode(), $this->getView()->getOptions());
		}

		return $this->pluginOptions;
	}

	public function getHints() {
		$currentHints = $this->getView()->getHints();

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
		$currentModel = $this->getView()->getModel();

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

	/**
	 * @return string
	 */
	public function getInstallationCode() {
		return $this->installationCode;
	}

	public function __construct(ViewInterface $aView, $aComponentCode, $aInstallationCode) {
		$this->view = $aView;
		$this->componentCode = (string)$aComponentCode;
		$this->installationCode = (string)$aInstallationCode;
	}
}
