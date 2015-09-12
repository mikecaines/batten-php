<?php
namespace Batten;

use App\Environment as Env;

class Reflector {
	/**
	 * Returns true if <caller> was called directly (i.e. not via parent::<caller>()).
	 * This can be used to determine if the method has been overridden.
	 * @return bool
	 */
	static public function inSurfaceMethodCall() {
		$backtraceOptions = DEBUG_BACKTRACE_IGNORE_ARGS;

		if (\Batten\DEBUG && Env::getOptions()->get('debugReflection')) {
			$backtraceOptions = $backtraceOptions | DEBUG_BACKTRACE_PROVIDE_OBJECT;
		}

		$backtrace = debug_backtrace($backtraceOptions, 3);
		//NOTE: $backtrace[0] == __FUNCTION__

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

		if (\Batten\DEBUG && Env::getOptions()->get('debugReflection')) {
			Environment::getLogger()->debug(
				get_class($backtrace[1]['object'])
				. '::' . $backtrace[1]['function']
				. '() is surface definition: ' . ($surface ? 'true' : 'false')
			);
		}

		return $surface;
	}

	/**
	 * Returns true if <caller> was called directly, or from a module level parent::<caller>()).
	 * This can be used to determine if the method has been overridden at the App level.
	 * @return bool
	 */
	static public function inSurfaceOrModuleMethodCall() {
		$backtraceOptions = DEBUG_BACKTRACE_IGNORE_ARGS;

		if (\Batten\DEBUG && Env::getOptions()->get('debugReflection')) {
			$backtraceOptions = $backtraceOptions | DEBUG_BACKTRACE_PROVIDE_OBJECT;
		}

		$backtrace = debug_backtrace($backtraceOptions, 3);
		//NOTE: $backtrace[0] == __FUNCTION__

		$proceed = false;

		if (count($backtrace) < 3) {
			//we are in surface call
			$proceed = true;
		}

		else {
			$callee = $backtrace[1];
			$caller = $backtrace[2];

			if ($caller['function'] == $callee['function']) {
				if (is_subclass_of($caller['class'], '\App\Controller')) {
					$proceed = true;
				}
			}

			else {
				$proceed = true;
			}
		}

		if (\Batten\DEBUG && Env::getOptions()->get('debugReflection')) {
			Environment::getLogger()->debug(
				get_class($backtrace[1]['object'])
				. '::' . $backtrace[1]['function']
				. '() is surface definition: ' . ($proceed ? 'true' : 'false')
			);
		}

		return $proceed;
	}
}
