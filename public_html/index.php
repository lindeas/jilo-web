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

// Preparing plugins and hooks
// Initialize HookDispatcher and plugin system
require_once __DIR__ . '/../app/core/HookDispatcher.php';
require_once __DIR__ . '/../app/core/PluginManager.php';
use App\Core\HookDispatcher;
use App\Core\PluginManager;

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
Session::startSession();

// Apply security headers
require_once '../app/includes/security_headers_middleware.php';

// sanitize all input vars that may end up in URLs or forms
require '../app/includes/sanitize.php';

// Check session validity
$validSession = Session::isValidSession();

// Get user ID early if session is valid
$userId = $validSession ? Session::getUserId() : null;

// Initialize feedback message system
require_once '../app/classes/feedback.php';
$system_messages = [];

require '../app/includes/errors.php';

// error reporting, comment out in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// list of available pages
// edit accordingly, add 'pages/PAGE.php'
$allowed_urls = [
    'dashboard',

    'conferences',
    'participants',
    'components',

    'graphs',
    'latest',
    'livejs',

    'agents',

    'config',

    'profile',
    'credentials',

    'settings',
    'security',
    'status',
    'logs',
    'help',

    'login',
    'logout',

    'about',
];

// Let plugins filter/extend allowed_urls
$allowed_urls = filter_allowed_urls($allowed_urls);

// cnfig file
// possible locations, in order of preference
$config_file_locations = [
    __DIR__ . '/../app/config/jilo-web.conf.php',
    __DIR__ . '/../jilo-web.conf.php',
    '/srv/jilo-web/jilo-web.conf.php',
    '/opt/jilo-web/jilo-web.conf.php'
];
$config_file = null;
// try to find the config file
foreach ($config_file_locations as $location) {
    if (file_exists($location)) {
        $config_file = $location;
        break;
    }
}
// if found, use it
if ($config_file) {
    $localConfigPath = str_replace(__DIR__ . '/..', '', $config_file);
    $config = require $config_file;
} else {
    die('Config file not found');
}

$app_root = $config['folder'];

// List of pages that don't require authentication
$public_pages = ['login', 'help', 'about'];

// Let plugins filter/extend public_pages
$public_pages = filter_public_pages($public_pages);

// Dispatch routing and auth
require_once __DIR__ . '/../app/core/Router.php';
$currentUser = \App\Core\Router::checkAuth($config, $app_root, $public_pages, $page);

// connect to db of Jilo Web
require '../app/classes/database.php';
require '../app/includes/database.php';
try {
    $response = connectDB($config);
    if (!$response['db']) {
        throw new Exception('Could not connect to database: ' . $response['error']);
    }
    $dbWeb = $response['db'];
} catch (Exception $e) {
    Feedback::flash('ERROR', 'DEFAULT', getError('Error connecting to the database.', $e->getMessage()));
    include '../app/templates/page-header.php';
    include '../app/helpers/feedback.php';
    include '../app/templates/page-footer.php';
    exit();
}

// start logging
require '../app/classes/log.php';
include '../app/helpers/logs.php';
$logObject = new Log($dbWeb);
$user_IP = getUserIP();

// Initialize security middleware
require_once '../app/includes/csrf_middleware.php';
require_once '../app/helpers/security.php';
$security = SecurityHelper::getInstance();

// Verify CSRF token for POST requests
applyCsrfMiddleware();

// init rate limiter
require '../app/classes/ratelimiter.php';

// get platforms details
require '../app/classes/platform.php';
$platformObject = new Platform($dbWeb);
$platformsAll = $platformObject->getPlatformDetails();

// by default we connect ot the first configured platform
if ($platform_id == '') {
    $platform_id = $platformsAll[0]['id'];
}

$platformDetails = $platformObject->getPlatformDetails($platform_id);

// init user functions
require '../app/classes/user.php';
include '../app/helpers/profile.php';
$userObject = new User($dbWeb);

// logout is a special case, as we can't use session vars for notices
if ($page == 'logout') {
    // clean up session
    Session::destroySession();

    // start new session for the login page
    Session::startSession();

    setcookie('username', "", time() - 100, $config['folder'], $config['domain'], isset($_SERVER['HTTPS']), true);

    // Log successful logout
    $logObject->insertLog($userId, "Logout: User \"$currentUser\" logged out. IP: $user_IP", 'user');

    // Set success message
    Feedback::flash('LOGIN', 'LOGOUT_SUCCESS');

    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    include '../app/pages/login.php';
    include '../app/templates/page-footer.php';

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
        $serverObject = new Server($dbWeb);

        $server_host = '127.0.0.1';
        $server_port = '8080';
        $server_endpoint = '/health';
        $server_status = $serverObject->getServerStatus($server_host, $server_port, $server_endpoint);
        if (!$server_status) {
            Feedback::flash('ERROR', 'DEFAULT', 'The Jilo Server is not running. Some data may be old and incorrect.', false, true);
        }
    }

    // --- Plugin loading logic for all enabled plugins ---
    $plugin_controllers = [];
    foreach ($GLOBALS['enabled_plugins'] as $plugin_name => $plugin_info) {
        $controller_path = $plugin_info['path'] . '/controllers/' . $plugin_name . '.php';
        if (file_exists($controller_path)) {
            $plugin_controllers[$plugin_name] = $controller_path;
        }
    }

    // page building
    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    if ($validSession) {
        include '../app/templates/page-sidebar.php';
    }
    if (in_array($page, $allowed_urls)) {
        // all normal pages
        if (isset($plugin_controllers[$page])) {
            include $plugin_controllers[$page];
            } else {
                include "../app/pages/{$page}.php";
            }
    } else {
        // the page is not in allowed urls, loading "not found" page
        include '../app/templates/error-notfound.php';
    }
    include '../app/templates/page-footer.php';
}

// flush the output buffer and show the page
ob_end_flush();

// clear errors and notices before next page just in case
unset($_SESSION['error']);
unset($_SESSION['notice']);
