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

// load the main App registry, hook dispatcher, and plugin route registry
require_once __DIR__ . '/../app/core/App.php';
require_once __DIR__ . '/../app/core/HookDispatcher.php';
require_once __DIR__ . '/../app/core/PluginRouteRegistry.php';

// Define hook helpers used by plugin bootstraps
if (!function_exists('register_hook')) {
    function register_hook(string $hook, callable $callback): void {
        \App\Core\HookDispatcher::register($hook, $callback);
    }
}

if (!function_exists('do_hook')) {
    function do_hook(string $hook, array $context = []): void {
        \App\Core\HookDispatcher::dispatch($hook, $context);
    }
}

if (!function_exists('filter_public_pages')) {
    function filter_public_pages(array $pages): array {
        return \App\Core\HookDispatcher::applyFilters('filter_public_pages', $pages);
    }
}

if (!function_exists('filter_allowed_urls')) {
    function filter_allowed_urls(array $urls): array {
        return \App\Core\HookDispatcher::applyFilters('filter_allowed_urls', $urls);
    }
}

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

// Set error reporting (suppress deprecations from vendor libs during tests)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set timezone
date_default_timezone_set('UTC');

// Define global variables needed by the application
$GLOBALS['config'] = [
    'site_name' => getenv('TEST_SITE_NAME') ?: 'Jilo-web Test',
    'domain' => getenv('TEST_SITE_DOMAIN') ?: 'localhost',
    'folder' => getenv('TEST_SITE_FOLDER') ?: '/jilo-web',
    'db_type' => getenv('DB_TYPE') ?: 'mariadb',
    'sql' => [
        'sql_host' => getenv('DB_HOST') ?: '127.0.0.1',
        'sql_port' => getenv('DB_PORT') ?: '3306',
        'sql_database' => getenv('DB_DATABASE') ?: 'jilo_test',
        'sql_username' => getenv('DB_USERNAME') ?: 'test_jilo',
        'sql_password' => getenv('DB_PASSWORD') ?: '',
    ],
    'environment' => 'testing'
];
$GLOBALS['app_root'] = $GLOBALS['config']['folder'] ?: '/';
\App\App::set('config', $GLOBALS['config']);
\App\App::set('app_root', $GLOBALS['app_root']);
$GLOBALS['_TEST_BASE_CONFIG'] = $GLOBALS['config'];

if (!function_exists('test_site_branding')) {
    function test_site_branding(): array
    {
        $config = test_app_config();

        return [
            'site_name' => (string)$config['site_name'],
            'domain' => (string)$config['domain'],
            'folder' => (string)($config['folder'] ?? ''),
        ];
    }
}

if (!function_exists('test_app_config')) {
    function test_app_config(?string $key = null)
    {
        $config = $GLOBALS['config'];
        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? null;
    }
}

if (!function_exists('test_db_config')) {
    function test_db_config(): array
    {
        $config = test_app_config();
        $sql = $config['sql'] ?? [];

        if (defined('CI_DB_HOST')) {
            $sql['sql_host'] = CI_DB_HOST;
        }
        if (defined('CI_DB_PORT')) {
            $sql['sql_port'] = CI_DB_PORT;
        }
        if (defined('CI_DB_DATABASE')) {
            $sql['sql_database'] = CI_DB_DATABASE;
        }
        if (defined('CI_DB_USERNAME')) {
            $sql['sql_username'] = CI_DB_USERNAME;
        }
        if (defined('CI_DB_PASSWORD')) {
            $sql['sql_password'] = CI_DB_PASSWORD;
        }

        return [
            'type' => (string)($config['db_type'] ?? 'mariadb'),
            'host' => (string)($sql['sql_host'] ?? '127.0.0.1'),
            'port' => (string)($sql['sql_port'] ?? '3306'),
            'dbname' => (string)($sql['sql_database'] ?? 'jilo_test'),
            'user' => (string)($sql['sql_username'] ?? 'test_jilo'),
            'password' => (string)($sql['sql_password'] ?? ''),
        ];
    }
}

if (!function_exists('test_set_app_config')) {
    function test_set_app_config(array $overrides = [], bool $mergeWithCurrent = false): array
    {
        $base = $mergeWithCurrent
            ? test_app_config()
            : ($GLOBALS['_TEST_BASE_CONFIG'] ?? test_app_config());
        $config = array_replace_recursive($base, $overrides);
        $GLOBALS['config'] = $config;
        \App\App::set('config', $config);

        $folder = $config['folder'] ?? '/';
        $GLOBALS['app_root'] = $folder ?: '/';
        \App\App::set('app_root', $GLOBALS['app_root']);

        return $config;
    }
}

if (!function_exists('seed_test_server_context')) {
    function seed_test_server_context(array $overrides = []): void
    {
        $config = test_app_config();
        $folder = rtrim((string)($config['folder'] ?? ''), '/');
        $defaultUri = ($folder ?: '') . '/index.php';
        $defaults = [
            'PHP_SELF' => '/index.php',
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'PHPUnit Test Browser',
            'HTTPS' => 'on',
            'HTTP_HOST' => (string)($config['domain'] ?? 'localhost'),
            'REQUEST_URI' => $defaultUri ?: '/index.php',
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }

        foreach ($overrides as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }
}

// Define global connectDB function
if (!function_exists('connectDB')) {
    function connectDB($config) {
        global $db;
        return [
            'db' => $db
        ];
    }
}

seed_test_server_context();

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
                            strpos($errorMsg, "doesn't exist") === false &&
                            strpos($errorMsg, 'Duplicate key') === false &&
                            strpos($errorMsg, 'errno: 121') === false) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
}
