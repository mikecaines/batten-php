<?php
namespace Batten;

class Reflector {
	static public function inSurfaceMethodCall() {
		$backtraceOptions = DEBUG_BACKTRACE_IGNORE_ARGS;

		if (DEBUG_REFLECTION) {
			$backtraceOptions = $backtraceOptions | DEBUG_BACKTRACE_PROVIDE_OBJECT;
		}

		$backtrace = debug_backtrace($backtraceOptions, 3);

		$surface = true;
		if (count($backtrace) == 3) {
			$callee = $backtrace[1];
			$caller = $backtrace[2];

			if ($caller['function'] == $callee['function']) {
				if (is_subclass_of($caller['class'], $callee['class'])) {
					$surface = false;
				}
			}
		}

		if (DEBUG_REFLECTION) {
			Environment::getLogger()->debug(
				get_class($backtrace[1]['object'])
				. '::' . $backtrace[1]['function']
				. '() is surface definition: ' . ($surface ? 'true' : 'false')
			);
		}

		return $surface;
	}
}
