<?php

use CodeIgniter\Config\Services;

// Check PHP version
$minPHPVersion = '7.4';
if (version_compare(PHP_VERSION, $minPHPVersion, '<')) {
    // Log error instead of displaying version info
    error_log('PHP version requirement not met. Required: ' . $minPHPVersion . ', Current: ' . PHP_VERSION);
    http_response_code(500);
    die('Server configuration error. Please contact administrator.');
}

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
chdir(__DIR__);

// Validate and load paths config file
$pathsConfigFile = FCPATH . 'jdih/app/Config/Paths.php';
if (!file_exists($pathsConfigFile)) {
    error_log('Paths.php configuration file not found: ' . $pathsConfigFile);
    http_response_code(500);
    die('Configuration error. Please contact administrator.');
}

require $pathsConfigFile;
$paths = new Config\Paths();

// Set system directory path
$paths->systemDirectory = FCPATH . 'jdih/system';
define('ROOTPATH', FCPATH . 'jdih/');

// Validate system directory exists
if (!is_dir($paths->systemDirectory)) {
    error_log('CodeIgniter system directory not found: ' . $paths->systemDirectory);
    http_response_code(500);
    die('System configuration error. Please contact administrator.');
}

// Load bootstrap
$bootstrapFile = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
if (!file_exists($bootstrapFile)) {
    error_log('Bootstrap file not found: ' . $bootstrapFile);
    http_response_code(500);
    die('System configuration error. Please contact administrator.');
}

require $bootstrapFile;

$app = Services::codeigniter();
$app->initialize();
$app->setContext('web');

// Load .env with error handling
try {
    $env = new \CodeIgniter\Config\DotEnv(ROOTPATH);
    $env->load();
} catch (Exception $e) {
    error_log('Error loading .env file: ' . $e->getMessage());
    // Continue without .env if it doesn't exist
}

if (! defined('ENVIRONMENT')) {
    define('ENVIRONMENT', env('CI_ENVIRONMENT', 'production'));
}

$app->run();
