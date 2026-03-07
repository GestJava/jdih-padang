<?php

use CodeIgniter\Config\Services;

// Check PHP version
$minPHPVersion = '7.4';
if (version_compare(PHP_VERSION, $minPHPVersion, '<')) {
    die('Your PHP version must be ' . $minPHPVersion . ' or higher to run CodeIgniter. Current version: ' . PHP_VERSION);
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

// Load our paths config file
require FCPATH . 'app/Config/Paths.php';

$paths = new Config\Paths();

// Location of the framework bootstrap file.
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();
$app->setContext('web');

// Load environment settings from .env files
$env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
$env->load();

// Define ENVIRONMENT
if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'development'));
}

/*
 *---------------------------------------------------------------
 * LAUNCH THE APPLICATION
 *---------------------------------------------------------------
 * Now that everything is setup, it's time to actually fire
 * up the engines and make this app do its thang.
 */
$app->run();
