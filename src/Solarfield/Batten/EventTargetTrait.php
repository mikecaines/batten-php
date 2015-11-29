<?php
namespace Solarfield\Batten;

trait EventTargetTrait {
	private $listeners = [];

	protected function hasEventListener($aEventType, $aListener) {
		if (array_key_exists($aEventType, $this->listeners)) {
			foreach ($this->listeners[$aEventType] as $k => $listener) {
				if ($listener === $aListener) {
					return $k;
				}
			}
		}

		return null;
	}

	public function addEventListener($aEventType, $aListener) {
		if (!$this->hasEventListener($aEventType, $aListener)) {
			if (!array_key_exists($aEventType, $this->listeners)) {
				$this->listeners[$aEventType] = [];
			}

			$this->listeners[$aEventType][] = $aListener;
		}
	}

	protected function dispatchEvent(EventInterface $aEvent) {
		$type = $aEvent->getType();

		if (array_key_exists($type, $this->listeners)) {
			foreach ($this->listeners[$type] as $listener) {
				$listener($aEvent);
			}
		}
	}
}
