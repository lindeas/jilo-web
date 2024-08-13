<?php

/**
 * Jilo web logs observer
 *
 * Description: A web interface to Jilo (JItsi Logs Observer), written in PHP
 * Author: Yasen Pramatarov
 * License: GPLv2
 * Project URL: https://lindeas.com/jilo
 * Year: 2024
 * Version: 0.1.1
 */

// error reporting, comment out in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// list of available pages
// edit accordingly, add 'pages/PAGE.php'
$allowed_urls = [
    'front',
    'login',
    'logout',
    'register',
    'profile',
    'config',
    'conferences',
    'participants',
    'components',
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

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} elseif (isset($_POST['page'])) {
    $page = $_POST['page'];
} else {
    $page = 'front';
}

// check if logged in
unset($user);
if (isset($_COOKIE['username'])) {
    if ( !isset($_SESSION['username']) ) {
        $_SESSION['username'] = $_COOKIE['username'];
    }
    $user = htmlspecialchars($_SESSION['username']);
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

// page building
if (in_array($page, $allowed_urls)) {
    // logout is a special case, as we can't use session vars for notices
    if ($page == 'logout') {

        // clean up session
        session_unset();
        session_destroy();
        setcookie('username', "", time() - 100, $config['folder'], $config['domain'], isset($_SERVER['HTTPS']), true);

        $notice = "You were logged out.<br />You can log in again.";
        include '../app/templates/page-header.php';
        include '../app/templates/page-menu.php';
        include '../app/templates/block-message.php';
        include '../app/pages/login.php';

    // all other normal pages
    } else {
        include '../app/templates/page-header.php';
        include '../app/templates/page-menu.php';
        include '../app/templates/block-message.php';
        if (isset($user)) {
            include '../app/templates/page-sidebar.php';
        }
        include "../app/pages/{$page}.php";
    }

// the page is not in allowed urls, loading front page
} else {
    $error = 'The page "' . $page . '" is not found';
    include '../app/templates/page-header.php';
    include '../app/templates/page-menu.php';
    include '../app/templates/block-message.php';
    if (isset($user)) {
        include '../app/templates/page-sidebar.php';
    }
    include '../app/pages/front.php';
}
include '../app/templates/page-footer.php';

// clear errors and notices before next page just in case
unset($_SESSION['error']);
unset($_SESSION['notice']);

?>
