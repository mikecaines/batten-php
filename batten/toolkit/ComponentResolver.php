<?php
namespace batten;

use app\Environment as Env;

include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-string.php';
include_once OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-struct.php';

class ComponentResolver {
	public function resolveComponent($aChain, $aClassNamePart, $aViewTypeCode = null, $aPluginCode = null) {
		$chain = $aChain;
		$chain = array_reverse($chain, true);

		$component = null;

		foreach ($chain as $link) {
			$link = ok_arrayMergeStruct([
				'namespace' => '\\',
				'path' => null,
				'classPath' => null,
			], $link);

			$className = $this->generateClassName($link, $aClassNamePart, $aViewTypeCode, $aPluginCode);
			$qualifiedClassName = $link['namespace'] . '\\' . $className;
			$classFileName = $className . '.php';

			$includePath = $link['path'];
			if ($aPluginCode) {
				$includePath .= DIRECTORY_SEPARATOR . 'plugins';
				$includePath .= DIRECTORY_SEPARATOR . strtolower(ok_strCamelToDash($aPluginCode));
			}
			if ($link['classPath']) $includePath .= $link['classPath'];
			$includePath .= '/' . $classFileName;

			$realIncludePath = realpath($includePath);

			if ($realIncludePath !== false) {
				$component = [
					'className' => $qualifiedClassName,
					'includeFilePath' => $realIncludePath,
				];

				break;
			}
		}

		if (DEBUG_COMPONENT_RESOLUTION) {
			Env::getLogger()->debug(
				get_called_class() . "::" . __FUNCTION__ . "() resolved '"
				. ucfirst($aViewTypeCode) . $aClassNamePart . "' component " . ok_varInfo($component)
				. " from chain " . ok_varInfo($chain)
			);
		}

		return $component;
	}

	public function generateClassName($aLink, $aClassNamePart, $aViewTypeCode = null, $aPluginCode = null) {
		$link = array_merge([
			'moduleClassNamePart' => null,
		], $aLink);

		$className = $link['moduleClassNamePart'];

		if ($aPluginCode != null) $className .= ucfirst($aPluginCode);

		if ($aViewTypeCode != null) $className .= ucfirst($aViewTypeCode);

		$className .= $aClassNamePart;

		return $className;
	}
}
