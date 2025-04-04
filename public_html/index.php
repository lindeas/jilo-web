<?php

/**
 * Jilo web logs observer
 *
 * Description: A web interface to Jilo (JItsi Logs Observer), written in PHP
 * Author: Yasen Pramatarov
 * License: GPLv2
 * Project URL: https://lindeas.com/jilo
 * Year: 2024-2025
 * Version: 0.3
 */

// we start output buffering and.
// flush it later only when there is no redirect
ob_start();

// Apply security headers
require_once '../app/includes/security_headers_middleware.php';

// sanitize all input vars that may end up in URLs or forms
require '../app/includes/sanitize.php';

session_name('jilo');
session_start();

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

    'settings',
    'security',
    'status',
    'logs',
    'help',

    'login',
    'logout',
    'register',
];

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

// check if logged in
unset($currentUser);
if (isset($_COOKIE['username'])) {
    if ( !isset($_SESSION['username']) ) {
        $_SESSION['username'] = $_COOKIE['username'];
    }
    $currentUser = htmlspecialchars($_SESSION['username']);
}

// redirect to login
if ( !isset($_COOKIE['username']) && ($page !== 'login' && $page !== 'register') ) {
    header('Location: ' . htmlspecialchars($app_root) . '?page=login');
    exit();
}

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
    // get user info before destroying session
    $user_id = $userObject->getUserId($currentUser)[0]['id'];

    // clean up session
    session_unset();
    session_destroy();

    // start new session for the login page
    session_start();

    setcookie('username', "", time() - 100, $config['folder'], $config['domain'], isset($_SERVER['HTTPS']), true);

    // Log successful logout
    $logObject->insertLog($user_id, "Logout: User \"$currentUser\" logged out. IP: $user_IP", 'user');

    // Set success message
    Feedback::flash('LOGIN', 'LOGOUT_SUCCESS');

    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    include '../app/pages/login.php';
    include '../app/templates/page-footer.php';

} else {

    // if user is logged in, we need user details and rights
    if (isset($currentUser)) {

        // If by error a logged in user requests the login page
        if ($page === 'login') {
            header('Location: ' . htmlspecialchars($app_root));
            exit();
        }
        $user_id = $userObject->getUserId($currentUser)[0]['id'];
        $userDetails = $userObject->getUserDetails($user_id);
        $userRights = $userObject->getUserRights($user_id);
        $userTimezone = isset($userDetails[0]['timezone']) ? $userDetails[0]['timezone'] : 'UTC'; // Default to UTC if no timezone is set


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

    // List of pages that don't require authentication
    $public_pages = ['login', 'register'];

    // Check if the requested page requires authentication
    if (!in_array($page, $public_pages)) {
        require_once '../app/includes/session_middleware.php';
    }

    // page building
    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    if (isset($currentUser)) {
        include '../app/templates/page-sidebar.php';
    }
    if (in_array($page, $allowed_urls)) {
        // all normal pages
        include "../app/pages/{$page}.php";
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
