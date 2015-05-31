<?php
namespace batten;

//define some low level debug flags
if (!defined('batten\DEBUG')) define('batten\DEBUG', false);
if (!defined('batten\DEBUG_COMPONENT_RESOLUTION')) define('batten\DEBUG_COMPONENT_RESOLUTION', false);
if (!defined('batten\DEBUG_COMPONENT_LIFETIMES')) define('batten\DEBUG_COMPONENT_LIFETIMES', false);
if (!defined('batten\DEBUG_MEM_USAGE')) define('batten\DEBUG_MEM_USAGE', false);
if (!defined('batten\DEBUG_PATHS')) define('batten\DEBUG_PATHS', false);
if (!defined('batten\DEBUG_ROUTING')) define('batten\DEBUG_ROUTING', false);
if (!defined('batten\DEBUG_REFLECTION')) define('batten\DEBUG_REFLECTION', false);
if (!defined('batten\DEBUG_CLASS_AUTOLOAD')) define('batten\DEBUG_CLASS_AUTOLOAD', false);

//setup BATTEN_PKG_FILE_PATH
if (!defined('batten\BATTEN_PKG_FILE_PATH')) define('batten\BATTEN_PKG_FILE_PATH', null);
if (!is_dir(\batten\BATTEN_PKG_FILE_PATH)) {
	error_log("ERROR: \\batten\\BATTEN_PKG_FILE_PATH is invalid: '" . print_r(\batten\BATTEN_PKG_FILE_PATH, true) . "'.");
	die(1);
}

//setup some additional batten paths
define('batten\BATTEN_NS_FILE_PATH', realpath(__DIR__ . '/../'));

//setup ok-kit paths (dependency)
if (!defined('batten\OKKIT_PKG_FILE_PATH')) define('batten\OKKIT_PKG_FILE_PATH', realpath(__DIR__ . '/../../../ok-kit-php'));
if (!is_dir(\batten\OKKIT_PKG_FILE_PATH)) {
	error_log("ERROR: \\batten\\OKKIT_PKG_FILE_PATH is invalid: '" . print_r(\batten\OKKIT_PKG_FILE_PATH, true) . "'.");
	die(1);
}

//setup APP_PKG_FILE_PATH
if (!defined('batten\APP_PKG_FILE_PATH')) define('batten\APP_PKG_FILE_PATH', null);
if (!is_dir(\batten\APP_PKG_FILE_PATH)) {
	error_log("ERROR: \\batten\\APP_PKG_FILE_PATH is invalid: '" . print_r(\batten\APP_PKG_FILE_PATH, true) . "'.");
	die(1);
}

//setup some additional app paths
define('batten\APP_NS_FILE_PATH', realpath(APP_PKG_FILE_PATH . '/app'));
define('batten\APP_BASE_FILE_PATH', realpath(APP_NS_FILE_PATH . '/base'));

//catch all errors and rethrow them as exceptions
require_once \batten\OKKIT_PKG_FILE_PATH . '/toolkit/ok-lib-error.php';
set_error_handler('ok_handleErrorAndThrowException');
