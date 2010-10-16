<?php
define('COMB_VERSION', '0.0 (very very alpha)');
define('COMB_APPLICATION_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('COMB_PROJECT_ROOT', getcwd() . DIRECTORY_SEPARATOR);

set_include_path(
    realpath(COMB_APPLICATION_ROOT) . PATH_SEPARATOR .
    get_include_path()
);

include_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'Autoloader.php');
include_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'ConnectorInterface.php');
function __autoload($className)
{
    return Comb_Autoloader::load($className);
}