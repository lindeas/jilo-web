<?php

/**
 * Session Middleware
 *
 * Validates session status and handles session timeout.
 * This middleware should be included in all protected pages.
 */

function applySessionMiddleware($config, $app_root) {
    $isTest = defined('PHPUNIT_RUNNING');

    // Access $_SESSION directly in test mode
    if (!$isTest) {
        // Start session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_start([
                'cookie_httponly' => 1,
                'cookie_secure' => 1,
                'cookie_samesite' => 'Strict',
                'gc_maxlifetime' => 7200 // 2 hours
            ]);
        }
    }

    // Check if user is logged in with all required session variables
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        cleanupSession($config, $app_root, $isTest);
        return false;
    }

    // Check session timeout
    $session_timeout = isset($_SESSION['REMEMBER_ME']) ? (30 * 24 * 60 * 60) : 7200; // 30 days or 2 hours
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
        // Session has expired
        cleanupSession($config, $app_root, $isTest);
        return false;
    }

    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();

    // Regenerate session ID periodically (every 30 minutes)
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // Regenerate session ID and update creation time
        if (!$isTest && !headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
            $oldData = $_SESSION;
            session_regenerate_id(true);
            $_SESSION = $oldData;
            $_SESSION['CREATED'] = time();
        }
    }

    return true;
}

/**
 * Helper function to clean up session data and redirect
 */
function cleanupSession($config, $app_root, $isTest) {
    if (!$isTest) {
        // Clear session data
        $_SESSION = array();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();

            // Start a new session to prevent errors
            if (!headers_sent()) {
                session_start([
                    'cookie_httponly' => 1,
                    'cookie_secure' => 1,
                    'cookie_samesite' => 'Strict',
                    'gc_maxlifetime' => 7200
                ]);
            }
        }

        // Clear cookies
        if (!headers_sent()) {
            setcookie('username', '', [
                'expires' => time() - 3600,
                'path' => $config['folder'],
                'domain' => $config['domain'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        header('Location: ' . $app_root . '?page=login&timeout=1');
        exit();
    }
}
