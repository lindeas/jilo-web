<?php

/**
 * Session Middleware
 * 
 * Validates session status and handles session timeout.
 * This middleware should be included in all protected pages.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['USER_ID'])) {
    header('Location: ' . $app_root . '?page=login');
    exit();
}

// Check session timeout
$session_timeout = isset($_SESSION['REMEMBER_ME']) ? (30 * 24 * 60 * 60) : 1440; // 30 days or 24 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    setcookie('username', '', [
        'expires' => time() - 3600,
        'path' => $config['folder'],
        'domain' => $config['domain'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    header('Location: ' . $app_root . '?page=login&timeout=1');
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerate session ID periodically (every 30 minutes)
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    // Regenerate session ID and update creation time
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
