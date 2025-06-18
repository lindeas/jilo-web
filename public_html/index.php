<?php

/**
 * Jilo web logs observer
 *
 * Description: A web interface to Jilo (JItsi Logs Observer), written in PHP
 * Author: Yasen Pramatarov
 * License: GPLv2
 * Project URL: https://lindeas.com/jilo
 * Year: 2024-2025
 * Version: 0.4
 */

// error reporting, comment out in production
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Prepare config loader
require_once __DIR__ . '/../app/core/ConfigLoader.php';
use App\Core\ConfigLoader;

// Load configuration
$config = ConfigLoader::loadConfig([
    __DIR__ . '/../app/config/jilo-web.conf.php',
    __DIR__ . '/../jilo-web.conf.php',
    '/srv/jilo-web/jilo-web.conf.php',
    '/opt/jilo-web/jilo-web.conf.php',
]);

// Make config available globally
$GLOBALS['config'] = $config;

// Expose config file path for pages
$config_file = ConfigLoader::getConfigPath();
$localConfigPath = str_replace(__DIR__ . '/..', '', $config_file);

// Set app root with default
$app_root = $config['folder'] ?? '/';

// Preparing plugins and hooks
// Initialize HookDispatcher and plugin system
require_once __DIR__ . '/../app/core/HookDispatcher.php';
require_once __DIR__ . '/../app/core/PluginManager.php';
use App\Core\HookDispatcher;
use App\Core\PluginManager;

// Global allowed URLs registration
register_hook('filter_allowed_urls', function($urls) {
    if (isset($GLOBALS['plugin_controllers']) && is_array($GLOBALS['plugin_controllers'])) {
        foreach ($GLOBALS['plugin_controllers'] as $controllers) {
            foreach ($controllers as $ctrl) {
                $urls[] = $ctrl;
            }
        }
    }
    return $urls;
});

// Hook registration and dispatch helpers
function register_hook(string $hook, callable $callback): void {
    HookDispatcher::register($hook, $callback);
}
function do_hook(string $hook, array $context = []): void {
    HookDispatcher::dispatch($hook, $context);
}
function filter_public_pages(array $pages): array {
    return HookDispatcher::applyFilters('filter_public_pages', $pages);
}
function filter_allowed_urls(array $urls): array {
    return HookDispatcher::applyFilters('filter_allowed_urls', $urls);
}

// Load enabled plugins
$plugins_dir = dirname(__DIR__) . '/plugins/';
$enabled_plugins = PluginManager::load($plugins_dir);
$GLOBALS['enabled_plugins'] = $enabled_plugins;

// Define CSRF token include path globally
if (!defined('CSRF_TOKEN_INCLUDE')) {
    define('CSRF_TOKEN_INCLUDE', dirname(__DIR__) . '/app/includes/csrf_token.php');
}

// Global cnstants
require_once '../app/includes/constants.php';

// we start output buffering and
// flush it later only when there is no redirect
ob_start();

// Start session before any session-dependent code
require_once '../app/classes/session.php';

// Initialize themes system after session is started
require_once __DIR__ . '/../app/helpers/theme.php';
use app\Helpers\Theme;

Session::startSession();

// Define page variable early via sanitize
require_once __DIR__ . '/../app/includes/sanitize.php';
// Ensure $page is defined to avoid undefined variable
if (!isset($page)) {
    $page = 'dashboard';
}

// List of pages that don't require authentication
$public_pages = ['login', 'help', 'about'];

// Let plugins filter/extend public_pages
$public_pages = filter_public_pages($public_pages);

// Middleware pipeline for security, sanitization & CSRF
require_once __DIR__ . '/../app/core/MiddlewarePipeline.php';
$pipeline = new \App\Core\MiddlewarePipeline();
$pipeline->add(function() {
    // Apply security headers
    require_once __DIR__ . '/../app/includes/security_headers_middleware.php';
    return true;
});

// For public pages, we don't need to validate the session
// The Router will handle authentication for protected pages
$validSession = false;
$userId = null;

// Only check session for non-public pages
if (!in_array($page, $public_pages)) {
    $validSession = Session::isValidSession(true);
    if ($validSession) {
        $userId = Session::getUserId();
    }
}

// Initialize feedback message system
require_once '../app/classes/feedback.php';
$system_messages = [];

require '../app/includes/errors.php';

// list of available pages
// edit accordingly, add 'pages/PAGE.php'
$allowed_urls = [
    'dashboard',
    'conferences','participants','components',
    'graphs','latest','livejs','agents',
    'profile','credentials','config','security',
    'settings','theme',
    'status',
    'help','about',
    'login','logout',
];

// Let plugins filter/extend allowed_urls
$allowed_urls = filter_allowed_urls($allowed_urls);

// Dispatch routing and auth
require_once __DIR__ . '/../app/core/Router.php';
use App\Core\Router;
$currentUser = Router::checkAuth($config, $app_root, $public_pages, $page);

// Connect to DB via DatabaseConnector
require_once __DIR__ . '/../app/core/DatabaseConnector.php';
use App\Core\DatabaseConnector;
$db = DatabaseConnector::connect($config);

// Logging: default to NullLogger, plugin can override
require_once __DIR__ . '/../app/core/NullLogger.php';
use App\Core\NullLogger;
$logObject = new NullLogger();
// Get the user IP
require_once __DIR__ . '/../app/helpers/ip_helper.php';
$user_IP = '';

// Plugin: initialize logging system plugin if available
do_hook('logger.system_init', ['db' => $db]);

// Override defaults if plugin provided real logger
if (isset($GLOBALS['logObject'])) {
    $logObject = $GLOBALS['logObject'];
}
if (isset($GLOBALS['user_IP'])) {
    $user_IP = $GLOBALS['user_IP'];
}

// CSRF middleware and run pipeline
$pipeline->add(function() {
    // Initialize security middleware
    require_once __DIR__ . '/../app/includes/csrf_middleware.php';
    require_once __DIR__ . '/../app/helpers/security.php';
    $security = SecurityHelper::getInstance();
    // Verify CSRF token for POST requests
    return applyCsrfMiddleware();
});
$pipeline->add(function() {
    // Init rate limiter
    global $db, $page, $userId;
    require_once __DIR__ . '/../app/includes/rate_limit_middleware.php';
    return checkRateLimit($db, $page, $userId);
});
$pipeline->add(function() {
    // Init user functions
    global $db, $userObject;
    require_once __DIR__ . '/../app/classes/user.php';
    include __DIR__ . '/../app/helpers/profile.php';
    $userObject = new User($db);
    return true;
});
if (!$pipeline->run()) {
    exit;
}

// get platforms details
require '../app/classes/platform.php';
$platformObject = new Platform($db);
$platformsAll = $platformObject->getPlatformDetails();

// by default we connect ot the first configured platform
if ($platform_id == '') {
    $platform_id = $platformsAll[0]['id'];
}

$platformDetails = $platformObject->getPlatformDetails($platform_id);

// logout is a special case, as we can't use session vars for notices
if ($page == 'logout') {
    // Save config before destroying session
    $savedConfig = $config;

    // clean up session
    Session::destroySession();

    // start new session for the login page
    Session::startSession();

    // Restore config to global scope
    $config = $savedConfig;
    $GLOBALS['config'] = $config;

    setcookie('username', "", time() - 100, $config['folder'], $config['domain'], isset($_SERVER['HTTPS']), true);

    // Log successful logout
    $logObject->log('info', "Logout: User \"$currentUser\" logged out. IP: $user_IP", ['user_id' => $userId, 'scope' => 'user']);

    // Set success message
    Feedback::flash('LOGIN', 'LOGOUT_SUCCESS');

    // Use theme helper to include templates
    \App\Helpers\Theme::include('page-header');
    \App\Helpers\Theme::include('page-menu');
    include '../app/pages/login.php';
    \App\Helpers\Theme::include('page-footer');

} else {
    // if user is logged in, we need user details and rights
    if ($validSession) {
        // If by error a logged in user requests the login page
        if ($page === 'login') {
            header('Location: ' . htmlspecialchars($app_root));
            exit();
        }
        $userDetails = $userObject->getUserDetails($userId);
        $userRights = $userObject->getUserRights($userId);
        $userTimezone = (!empty($userDetails[0]['timezone'])) ? $userDetails[0]['timezone'] : 'UTC'; // Default to UTC if no timezone is set (or is missing)

        // check if the Jilo Server is running
        require '../app/classes/server.php';
        $serverObject = new Server($db);

        $server_host = '127.0.0.1';
        $server_port = '8080';
        $server_endpoint = '/health';
        $server_status = $serverObject->getServerStatus($server_host, $server_port, $server_endpoint);
        if (!$server_status) {
            Feedback::flash('ERROR', 'DEFAULT', 'The Jilo Server is not running. Some data may be old and incorrect.', false, true);
        }
    }

    // --- Plugin loading logic for all enabled plugins ---
    // Ensure all enabled plugin bootstraps are loaded before mapping controllers
    foreach ($GLOBALS['enabled_plugins'] as $plugin_name => $plugin_info) {
        $bootstrap_path = $plugin_info['path'] . '/bootstrap.php';
        if (file_exists($bootstrap_path)) {
            require_once $bootstrap_path;
        }
    }
    // Plugin controller mapping logic (we add each controller listed in bootstrap as a page)
    $mapped_plugin_controllers = [];
    foreach ($GLOBALS['enabled_plugins'] as $plugin_name => $plugin_info) {
        if (isset($GLOBALS['plugin_controllers'][$plugin_name])) {
            foreach ($GLOBALS['plugin_controllers'][$plugin_name] as $plugin_page) {
                $controller_path = $plugin_info['path'] . '/controllers/' . $plugin_page . '.php';
                if (file_exists($controller_path)) {
                    $mapped_plugin_controllers[$plugin_page] = $controller_path;
                }
            }
        }
    }

    // page building
    if (in_array($page, $allowed_urls)) {
    // The page is in allowed URLs
        if (isset($mapped_plugin_controllers[$page]) && file_exists($mapped_plugin_controllers[$page])) {
        // The page is from a plugin controller
            if (defined('PLUGIN_PAGE_DIRECT_OUTPUT') && PLUGIN_PAGE_DIRECT_OUTPUT === true) {
                // Barebone page controller, we don't output anything extra
                include $mapped_plugin_controllers[$page];
                ob_end_flush();
                exit;
            } else {
                \App\Helpers\Theme::include('page-header');
                \App\Helpers\Theme::include('page-menu');
                if ($validSession) {
                    \App\Helpers\Theme::include('page-sidebar');
                }
                include $mapped_plugin_controllers[$page];
                \App\Helpers\Theme::include('page-footer');
            }
        } else {
        // The page is from a core controller
            \App\Helpers\Theme::include('page-header');
            \App\Helpers\Theme::include('page-menu');
            if ($validSession) {
                \App\Helpers\Theme::include('page-sidebar');
            }
            if (file_exists("../app/pages/{$page}.php")) {
                include "../app/pages/{$page}.php";
            } else {
                include '../app/templates/error-notfound.php';
            }
            \App\Helpers\Theme::include('page-footer');
        }
    } else {
    // The page is not in allowed URLs
        \App\Helpers\Theme::include('page-header');
        \App\Helpers\Theme::include('page-menu');
        if ($validSession) {
            \App\Helpers\Theme::include('page-sidebar');
        }
        include '../app/templates/error-notfound.php';
        \App\Helpers\Theme::include('page-footer');
    }
}

// flush the output buffer and show the page
ob_end_flush();

// clear errors and notices before next page just in case
unset($_SESSION['error']);
unset($_SESSION['notice']);
