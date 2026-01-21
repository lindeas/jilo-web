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

// Define APP_PATH for components that expect the constant
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__) . '/app/');
}

// load the main App registry and plugin route registry
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/PluginRouteRegistry.php';

// Load plugin Log model and IP helper early so fallback wrapper is bypassed
require_once __DIR__ . '/../app/helpers/ip_helper.php';
require_once __DIR__ . '/../app/helpers/logger_loader.php';

// Initialize global user_IP for tests
global $user_IP;
$user_IP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Load Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Ensure core NullLogger is available during tests
require_once __DIR__ . '/../app/core/NullLogger.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define global variables needed by the application
$GLOBALS['app_root'] = '/';
$GLOBALS['config'] = [
    'db_type' => getenv('DB_TYPE') ?: 'mariadb',
    'sql' => [
        'sql_host' => getenv('DB_HOST') ?: 'localhost',
        'sql_port' => getenv('DB_PORT') ?: '3306',
        'sql_database' => getenv('DB_DATABASE') ?: 'jilo_test',
        'sql_username' => getenv('DB_USERNAME') ?: 'test_jilo',
        'sql_password' => getenv('DB_PASSWORD') ?: '',
    ],
    'environment' => 'testing'
];

// Define global connectDB function
if (!function_exists('connectDB')) {
    function connectDB($config) {
        global $db;
        return [
            'db' => $db
        ];
    }
}

// Set up server variables
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
$_SERVER['HTTPS'] = 'on';
