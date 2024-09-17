<?php

/**
 * Jilo web logs observer
 *
 * Description: A web interface to Jilo (JItsi Logs Observer), written in PHP
 * Author: Yasen Pramatarov
 * License: GPLv2
 * Project URL: https://lindeas.com/jilo
 * Year: 2024
 * Version: 0.2
 */

// we start output buffering and.
// flush it later only when there is no redirect
ob_start();

require '../app/helpers/errors.php';

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

    'agents',

    'profile',
    'config',
    'logs',

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
    $config = require $config_file;
} else {
    die('Config file not found');
}

$app_root = $config['folder'];

session_name('jilo');
session_start();

if (isset($_REQUEST['page'])) {
    $page = $_REQUEST['page'];
} else {
    $page = 'dashboard';
}
if (isset($_REQUEST['item'])) {
    $item = $_REQUEST['item'];
} else {
    $item = '';
}

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
    header('Location: index.php?page=login');
    exit();
}

// we use 'notice' for all non-critical messages and 'error' for errors
if (isset($_SESSION['notice'])) {
    $notice = $_SESSION['notice'];
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
}

// connect to db of Jilo Web
require '../app/classes/database.php';
require '../app/helpers/database.php';
$dbWeb = connectDB($config);

// start logging
require '../app/classes/log.php';
include '../app/helpers/logs.php';
$logObject = new Log($dbWeb);
$user_IP = getUserIP();

// get platforms details
require '../app/classes/platform.php';
$platformObject = new Platform($dbWeb);
$platformsAll = $platformObject->getPlatformDetails();

// by default we connect ot the first configured platform
$firstPlatform = $platformsAll[0]['id'];
$platform_id = $_REQUEST['platform'] ?? $firstPlatform;
$platformDetails = $platformObject->getPlatformDetails($platform_id);

// init user functions
require '../app/classes/user.php';
include '../app/helpers/profile.php';
$userObject = new User($dbWeb);

// logout is a special case, as we can't use session vars for notices
if ($page == 'logout') {

    $notice = "You were logged out.<br />You can log in again.";
    $user_id = $userObject->getUserId($currentUser)[0]['id'];
    $logObject->insertLog($user_id, "Logout: User \"$currentUser\" logged out. IP: $user_IP", 'user');

    // clean up session
    session_unset();
    session_destroy();
    setcookie('username', "", time() - 100, $config['folder'], $config['domain'], isset($_SERVER['HTTPS']), true);

    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    include '../app/templates/block-message.php';
    include '../app/pages/login.php';

} else {

    // if user is logged in, we need user details and rights
    if (isset($currentUser)) {
        $user_id = $userObject->getUserId($currentUser)[0]['id'];
        $userDetails = $userObject->getUserDetails($user_id);
        $userRights = $userObject->getUserRights($user_id);
        $userTimezone = isset($userDetails[0]['timezone']) ? $userDetails[0]['timezone'] : 'UTC'; // Default to UTC if no timezone is set
    }

    // page building
    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    include '../app/templates/block-message.php';
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
}
// end with the footer
include '../app/templates/page-footer.php';

// flush the output buffer and show the page
ob_end_flush();

// clear errors and notices before next page just in case
unset($_SESSION['error']);
unset($_SESSION['notice']);

?>
