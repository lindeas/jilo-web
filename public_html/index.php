<?php

/**
 * Jilo web logs observer
 *
 * Description: A web interface to Jilo (JItsi Logs Observer), written in PHP
 * Author: Yasen Pramatarov
 * License: GPLv2
 * Project URL: https://lindeas.com/jilo
 * Year: 2024-2025
 * Version: 0.4.1
 */

// error reporting, comment out in production
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

// Define main app path
define('APP_PATH', __DIR__ . '/../app/');

// Prepare config loader
require_once APP_PATH . 'core/ConfigLoader.php';
require_once APP_PATH . 'core/App.php';
require_once APP_PATH . 'core/PluginRouteRegistry.php';
use App\Core\ConfigLoader;
use App\App;
use App\Core\PluginRouteRegistry;

// Load configuration
$config = ConfigLoader::loadConfig([
    APP_PATH . 'config/jilo-web.conf.php',
    __DIR__ . '/../jilo-web.conf.php',
    '/srv/jilo-web/jilo-web.conf.php',
    '/opt/jilo-web/jilo-web.conf.php',
]);

// Make config available globally
$GLOBALS['config'] = $config; // FIXME we use old globals and includes before migrating fully to App\App
App::set('config', $config);

// Expose config file path for pages
$config_file = ConfigLoader::getConfigPath();
$localConfigPath = str_replace(__DIR__ . '/..', '', $config_file);
App::set('config_path', $config_file);

// Set app root with default
$app_root = $config['folder'] ?? '/';
App::set('app_root', $app_root);

// Preparing plugins and hooks
// Initialize HookDispatcher and plugin system
require_once APP_PATH . 'core/HookDispatcher.php';
require_once APP_PATH . 'core/PluginManager.php';
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
function register_plugin_route_prefix(string $prefix, array $definition = []): void {
    PluginRouteRegistry::registerPrefix($prefix, $definition);
}

// Define CSRF token include path globally
if (!defined('CSRF_TOKEN_INCLUDE')) {
    define('CSRF_TOKEN_INCLUDE', APP_PATH . 'includes/csrf_token.php');
}

// Global cnstants
require_once APP_PATH . 'includes/constants.php';

// we start output buffering and
// flush it later only when there is no redirect
ob_start();

// Start session before any session-dependent code
require_once APP_PATH . 'classes/session.php';

// Initialize themes system after session is started
require_once APP_PATH . 'helpers/theme.php';
use app\Helpers\Theme;

Session::startSession();

// Define page variable early via sanitize
require_once APP_PATH . 'includes/sanitize.php';
// Ensure $page is defined to avoid undefined variable
if (!isset($page)) {
    $page = 'dashboard';
}

// List of pages that don't require authentication
$public_pages = ['login', 'register', 'help', 'about', 'theme-asset', 'plugin-asset'];

// Let plugins filter/extend public_pages
$public_pages = filter_public_pages($public_pages);
$public_pages = PluginRouteRegistry::injectPublicPages($public_pages);

// Middleware pipeline for security, sanitization & CSRF
require_once APP_PATH . 'core/MiddlewarePipeline.php';
$pipeline = new \App\Core\MiddlewarePipeline();
$pipeline->add(function() {
    // Apply security headers
    require_once APP_PATH . 'includes/security_headers_middleware.php';
    return true;
});

// Always detect authenticated session so templates shared
// between public and private pages behave consistently.
$validSession = Session::isValidSession(true);
$userId = $validSession ? Session::getUserId() : null;
App::set('valid_session', $validSession);
App::set('user_id', $userId);

// Initialize feedback message system
require_once APP_PATH . 'classes/feedback.php';
$system_messages = [];
App::set('feedback', $system_messages);

require APP_PATH . 'includes/errors.php';

// list of available pages
// edit accordingly, add 'pages/PAGE.php'
$allowed_urls = [
    'dashboard',
    'conferences','participants','components',
    'graphs','latest','livejs','agents',
    'profile','credentials','config','security',
    'settings','theme','theme-asset','plugin-asset',
    'admin','status',
    'help','about',
    'login','register','logout',
];

// Let plugins filter/extend allowed_urls
$allowed_urls = filter_allowed_urls($allowed_urls);
$allowed_urls = PluginRouteRegistry::injectAllowedPages($allowed_urls);

// Dispatch routing and auth
require_once APP_PATH . 'core/Router.php';
use App\Core\Router;
$currentUser = Router::checkAuth($config, $app_root, $public_pages, $page);
if ($currentUser === null && $validSession) {
    $currentUser = Session::getUsername();
}

// Connect to DB via DatabaseConnector
require_once APP_PATH . 'core/DatabaseConnector.php';
use App\Core\DatabaseConnector;
$db = DatabaseConnector::connect($config);
App::set('db', $db);

// Load enabled plugins (we need this after DB connection is established)
$plugins_dir = dirname(__DIR__) . '/plugins/';
$enabled_plugins = PluginManager::load($plugins_dir);
$GLOBALS['enabled_plugins'] = $enabled_plugins;

// Initialize Log throttler
require_once APP_PATH . 'core/LogThrottler.php';
use App\Core\LogThrottler;

// Logging: default to NullLogger, plugin can override
require_once APP_PATH . 'core/NullLogger.php';
use App\Core\NullLogger;
$logObject = new NullLogger();
App::set('logger', $logObject);

require_once APP_PATH . 'helpers/logger_loader.php';
// Get the user IP
require_once APP_PATH . 'helpers/ip_helper.php';
$user_IP = '';

// Plugin: initialize logging system plugin if available
do_hook('logger.system_init', ['db' => $db]);

// Override defaults if plugin provided real logger
if (isset($GLOBALS['logObject'])) {
    $logObject = $GLOBALS['logObject'];
    App::set('logger', $logObject);
}
if (isset($GLOBALS['user_IP'])) {
    $user_IP = $GLOBALS['user_IP'];
}
App::set('user_ip', $user_IP);

// Check for pending DB migrations (non-intrusive: warn only)
// Only show for authenticated users and not on login page
try {
    $migrationsDir = APP_PATH . '../doc/database/migrations';
    if (is_dir($migrationsDir) && $userId !== null && $page !== 'login') {
        require_once APP_PATH . 'core/MigrationRunner.php';
        $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
        if ($runner->hasPendingMigrations()) {
            $pending = $runner->listPendingMigrations();
            $msg = 'Database schema is out of date. There are pending migrations. Run "<code>php scripts/migrate.php up</code>" or use the <a href="?page=admin">Admin center</a>';
            // Check if migration message already exists to prevent duplicates
            $hasMigrationMessage = false;
            if (isset($_SESSION['flash_messages'])) {
                foreach ($_SESSION['flash_messages'] as $flash) {
                    if ($flash['category'] === 'SYSTEM' && $flash['key'] === 'MIGRATIONS_PENDING') {
                        $hasMigrationMessage = true;
                        break;
                    }
                }
            }
            // Log (throttled) and show as a system message only if not already added
            if (!$hasMigrationMessage) {
                LogThrottler::logThrottled($logObject, $db, 'migrations_pending', 86400, 'warning', $msg, ['scope' => 'system']);
                Feedback::flash('SYSTEM', 'MIGRATIONS_PENDING', $msg, false, true, false);
            }
        }
    }
} catch (\Throwable $e) {
    // Do not break the app; log only
    app_log('error', 'Migration check failed: ' . $e->getMessage(), [
        'scope' => 'system',
    ]);
}

// CSRF middleware and run pipeline
$pipeline->add(function() {
    // Initialize security middleware
    require_once APP_PATH . 'includes/csrf_middleware.php';
    require_once APP_PATH . 'helpers/security.php';
    $security = SecurityHelper::getInstance();
    // Verify CSRF token for POST requests
    return applyCsrfMiddleware();
});
$pipeline->add(function() {
    // Init rate limiter
    global $db, $page, $userId;
    require_once APP_PATH . 'includes/rate_limit_middleware.php';
    return checkRateLimit($db, $page, $userId);
});
$pipeline->add(function() {
    // Init user functions
    global $db, $userObject;
    require_once APP_PATH . 'classes/user.php';
    include APP_PATH . 'helpers/profile.php';
    $userObject = new User($db);
    return true;
});
if (!$pipeline->run()) {
    exit;
}

// Maintenance mode: show maintenance page to non-superusers
try {
    require_once APP_PATH . 'core/Maintenance.php';
    if (\App\Core\Maintenance::isEnabled()) {
        $isSuperuser = false;
        if ($validSession && isset($userId) && isset($userObject) && method_exists($userObject, 'hasRight')) {
            // user 1 is always superuser per implementation, but also check explicit right
            $isSuperuser = ($userId === 1) || (bool)$userObject->hasRight($userId, 'superuser');
        }
        if (!$isSuperuser) {
            http_response_code(503);
            // Advise clients to retry after 10 minutes (600 seconds; configure here)
            header('Retry-After: 600');
            // Show themed maintenance page
            \App\Helpers\Theme::include('page-header');
            \App\Helpers\Theme::include('page-menu');
            include APP_PATH . 'templates/maintenance.php';
            \App\Helpers\Theme::include('page-footer');
            ob_end_flush();
            exit;
        } else {
            // Superusers bypass maintenance; show a small banner
            $maintMsg = \App\Core\Maintenance::getMessage();
            $custom = 'Maintenance mode is enabled.';
            if (!empty($maintMsg)) {
                $custom .= ' <em>' . htmlspecialchars($maintMsg) . '</em>';
            }
            $custom .= ' Control it from the <a href="' . htmlspecialchars($app_root) . '?page=admin">Admin center</a>';
            // Non-dismissible and small, do not sanitize to allow link and <em>
            Feedback::flash('SYSTEM', 'MAINTENANCE_ON', $custom, false, true, false);
        }
    }
} catch (\Throwable $e) {
    // Do not break app if maintenance check fails
}

// Apply per-user theme from DB into session (without persisting) once user is known
if ($validSession && isset($userId) && isset($userObject) && is_object($userObject) && method_exists($userObject, 'getUserTheme')) {
    try {
        $dbTheme = $userObject->getUserTheme((int)$userId);
        if ($dbTheme) {
            \App\Helpers\Theme::setCurrentTheme($dbTheme, false);
        }
    } catch (\Throwable $e) {
        // Non-fatal if theme load fails
    }
}

// get platforms details
require APP_PATH . 'classes/platform.php';
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
    include APP_PATH . 'pages/login.php';
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
        $timeNow = new DateTime('now', new DateTimeZone($userTimezone)); // We init local viewer's time as early as possible
        App::set('user_details', $userDetails);
        App::set('user_timezone', $userTimezone);
        App::set('time_now', $timeNow);
        App::set('user_object', $userObject);

        // check if the Jilo Server is running
        require APP_PATH . 'classes/server.php';
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
    if (!empty($mapped_plugin_controllers)) {
        $allowed_urls = array_unique(array_merge($allowed_urls, array_keys($mapped_plugin_controllers)));
    }

    // Check if the requested page is handled by a plugin route dispatcher.
    $routeContext = [
        'page' => $page,
        'request' => $_REQUEST,
        'get' => $_GET,
        'post' => $_POST,
        'user_id' => $userId,
        'user_object' => $userObject ?? null,
        'valid_session' => $validSession,
        'app_root' => $app_root,
        'db' => $db,
        'config' => $config,
        'logger' => $logObject,
        'time_now' => $timeNow ?? null,
    ];
    if (PluginRouteRegistry::match($page)) {
        $handled = PluginRouteRegistry::dispatch($page, $routeContext);
        if ($handled !== false) {
            ob_end_flush();
            exit;
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
            if (file_exists(APP_PATH . "pages/{$page}.php")) {
                include APP_PATH . "pages/{$page}.php";
            } else {
                include APP_PATH . 'templates/error-notfound.php';
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
        include APP_PATH . 'templates/error-notfound.php';
        \App\Helpers\Theme::include('page-footer');
    }
}

// flush the output buffer and show the page
ob_end_flush();

// clear errors and notices before next page just in case
unset($_SESSION['error']);
unset($_SESSION['notice']);
