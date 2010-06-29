<?php
/**
 * Define constants that will be used throughout the application.
 */
define('COMB_VERSION', '0.0 (very very alpha)');
define('COMB_APPLICATION_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
define('COMB_PROJECT_ROOT', getcwd() . DIRECTORY_SEPARATOR);

/**
 * Set the includepath.
 */
set_include_path(
    realpath(dirname(COMB_APPLICATION_ROOT)) . PATH_SEPARATOR .
    get_include_path()
);

/**
 * Autoloader, locating classes starting with Comb_ automatically.
 * @param string $className the class we're trying to find
 * @return boolean wether or not the file was successfully included
 */
include_once(COMB_APPLICATION_ROOT . 'Comb' . DIRECTORY_SEPARATOR . 'Autoloader.php');
function __autoload($className)
{
    return Comb_Autoloader::load($className);
}

/**
 * Run the application
 */
$app = new Comb_Application();
$app->run();