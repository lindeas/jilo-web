<?php

// Set test environment
define('PHPUNIT_RUNNING', true);

// Configure session before starting it
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 1440);

// Start session if not already started
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}

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
    ],
    'folder' => '/',
    'domain' => 'localhost',
    'login' => [
        'max_attempts' => 5,
        'lockout_time' => 900
    ]
];

// Initialize system_messages array
$GLOBALS['system_messages'] = [];

// Set up server variables
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/?page=login';
$_SERVER['HTTPS'] = 'on';

// Define global connectDB function
if (!function_exists('connectDB')) {
    function connectDB($config) {
        global $dbWeb;
        return [
            'db' => $dbWeb
        ];
    }
}
