<?php

/**
 * Logs Plugin Bootstrap
 *
 * Initializes the logs plugin using the App API pattern.
 */

if (!defined('PLUGIN_LOGS_PATH')) {
    define('PLUGIN_LOGS_PATH', __DIR__ . '/');
}

// Load plugin helpers
require_once PLUGIN_LOGS_PATH . 'helpers.php';

// Register route with callable dispatcher
register_plugin_route_prefix('logs', [
    'dispatcher' => function($action, array $context = []) {
        require_once PLUGIN_LOGS_PATH . 'controllers/logs.php';
        if (function_exists('logs_plugin_handle')) {
            return logs_plugin_handle($action, $context);
        }
        return false;
    },
    'access' => 'private',
    'defaults' => ['action' => 'list'],
    'plugin' => 'logs',
]);

// Migration function for admin plugin check
if (!function_exists('logs_ensure_tables')) {
    function logs_ensure_tables(): void {
        static $ensured = false;
        if ($ensured) {
            return;
        }
        $db = \App\App::db();
        if (!$db || !method_exists($db, 'getConnection')) {
            return;
        }
        $pdo = $db->getConnection();
        if (!$pdo instanceof \PDO) {
            return;
        }
        $migrationFile = __DIR__ . '/migrations/create_log_table.sql';
        if (is_readable($migrationFile)) {
            $sql = file_get_contents($migrationFile);
            if ($sql !== false && trim($sql) !== '') {
                $pdo->exec($sql);
            }
        }
        $ensured = true;
    }
}

// Logger plugin bootstrap
register_hook('logger.system_init', function(array $context) {
    // Ensure tables exist
    logs_ensure_tables();
    
    // Load plugin-specific LoggerFactory class
    require_once __DIR__ . '/models/LoggerFactory.php';
    [$logger, $userIP] = LoggerFactory::create($context['db']);

    // Expose to globals for routing logic
    $GLOBALS['logObject'] = $logger;
    $GLOBALS['user_IP']   = $userIP;
});

// Configuration for top menu injection
define('LOGS_MAIN_MENU_SECTION', 'main'); // section of the top menu
define('LOGS_MAIN_MENU_POSITION', 20);    // lower = earlier in menu
register_hook('main_menu', function($ctx) {
    $section = defined('LOGS_MAIN_MENU_SECTION') ? LOGS_MAIN_MENU_SECTION : 'main';
    $position = defined('LOGS_MAIN_MENU_POSITION') ? LOGS_MAIN_MENU_POSITION : 100;
    // We use $section/$position for sorting/insertion logic in the menu template
    echo '
                        <a class="dropdown-item" href="?page=logs">
                            <i class="fas fa-list"></i>Logs
                        </a>';
});
