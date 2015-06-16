<?php
namespace Batten;

//define some low level debug flags
if (!defined('Batten\DEBUG')) define('Batten\DEBUG', false);
if (!defined('Batten\DEBUG_COMPONENT_RESOLUTION')) define('Batten\DEBUG_COMPONENT_RESOLUTION', false);
if (!defined('Batten\DEBUG_COMPONENT_LIFETIMES')) define('Batten\DEBUG_COMPONENT_LIFETIMES', false);
if (!defined('Batten\DEBUG_MEM_USAGE')) define('Batten\DEBUG_MEM_USAGE', false);
if (!defined('Batten\DEBUG_PATHS')) define('Batten\DEBUG_PATHS', false);
if (!defined('Batten\DEBUG_ROUTING')) define('Batten\DEBUG_ROUTING', false);
if (!defined('Batten\DEBUG_REFLECTION')) define('Batten\DEBUG_REFLECTION', false);
if (!defined('Batten\DEBUG_CLASS_AUTOLOAD')) define('Batten\DEBUG_CLASS_AUTOLOAD', false);
