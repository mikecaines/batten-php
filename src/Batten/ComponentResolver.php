<?php
namespace Batten;

use app\Environment as Env;
use Ok\MiscUtils;
use Ok\StringUtils;
use Ok\StructUtils;

class ComponentResolver {
	public function resolveComponent($aChain, $aClassNamePart, $aViewTypeCode = null, $aPluginCode = null) {
		$chain = $aChain;
		$chain = array_reverse($chain, true);

		$component = null;

		foreach ($chain as $link) {
			$link = StructUtils::merge([
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
				$includePath .= DIRECTORY_SEPARATOR . strtolower(StringUtils::camelToDash($aPluginCode));
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
				. ucfirst($aPluginCode) . ucfirst($aViewTypeCode) . $aClassNamePart
				. "' component " . MiscUtils::varInfo($component)
				. " from chain " . MiscUtils::varInfo($chain)
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
