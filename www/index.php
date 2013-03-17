<?php

//define the autoload function to load chasses
function  __autoload($className)
{
	$className = implode("/", explode("_", $className)) . '.php';
	$paths = explode(PATH_SEPARATOR,get_include_path());

	foreach ($paths as $p) {
		if (file_exists(rtrim($p, '/') . "/$className")) {
			require $className;
			return;
		}
	}

	throw new Exception("The file $className does not exist");
}

define('APPLICATION_ENV', 'development');

//define config file and library path
define('CONFIG_FILE', '../site/config/config.ini');
define('LIBRARY_PATH', '../library');

//define the include path
set_include_path(
LIBRARY_PATH . PATH_SEPARATOR .
get_include_path());

Butterfly_Factory::setNamespaces(array('%PROJECTNAME%', 'Butterfly'));
$front = Butterfly_FrontController::getInstance();
$front->run();
