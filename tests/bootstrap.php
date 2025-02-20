<?php

// Set test environment
define('PHPUNIT_RUNNING', true);

// Configure session before any output
if (!headers_sent()) {
    // Configure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', 1440); // 24 minutes
}

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define global variables needed by the application
$GLOBALS['app_root'] = '/';
$GLOBALS['config'] = [
    'db' => [
        'type' => 'sqlite',
        'dbFile' => ':memory:'
    ]
];

// Define global connectDB function
if (!function_exists('connectDB')) {
    function connectDB($config) {
        global $dbWeb;
        return [
            'db' => $dbWeb
        ];
    }
}

// Set up server variables
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
$_SERVER['HTTPS'] = 'on';
