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

// Define plugin route registration function used by plugin bootstraps
if (!function_exists('register_plugin_route_prefix')) {
    function register_plugin_route_prefix(string $prefix, array $definition = []): void {
        \App\Core\PluginRouteRegistry::registerPrefix($prefix, $definition);
    }
}

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

/**
 * Setup test database schema by applying main.sql and migrations
 * 
 * @param PDO $pdo Database connection
 * @return void
 */
function setupTestDatabaseSchema(PDO $pdo): void
{
    // Apply main.sql schema
    $mainSqlPath = __DIR__ . '/../doc/database/main.sql';
    if (file_exists($mainSqlPath)) {
        $sql = file_get_contents($mainSqlPath);
        // Add IF NOT EXISTS to CREATE TABLE statements
        $sql = preg_replace('/CREATE TABLE `/', 'CREATE TABLE IF NOT EXISTS `', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Skip errors for INSERT statements on existing data
                    if (strpos($statement, 'INSERT') === false) {
                        throw $e;
                    }
                }
            }
        }
    }

    // Apply migrations from doc/database/migrations/ (excluding subfolders)
    $migrationsDir = __DIR__ . '/../doc/database/migrations';
    if (is_dir($migrationsDir)) {
        $files = glob($migrationsDir . '/*.sql');
        sort($files); // Apply in chronological order
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            // Add IF NOT EXISTS to CREATE TABLE statements
            $sql = preg_replace('/CREATE TABLE `/', 'CREATE TABLE IF NOT EXISTS `', $sql);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Skip errors for:
                        // - Duplicate columns (already exists)
                        // - Table doesn't exist (plugin tables not yet created)
                        $errorMsg = $e->getMessage();
                        if (strpos($errorMsg, 'Duplicate column') === false && 
                            strpos($errorMsg, "doesn't exist") === false) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
}
