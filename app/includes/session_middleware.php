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
                'gc_maxlifetime' => 1440 // 24 minutes
            ]);
        }
    }

    // Check if user is logged in
    if (!isset($_SESSION['USER_ID'])) {
        if (!$isTest) {
            header('Location: ' . $app_root . '?page=login');
            exit();
        }
        return false;
    }

    // Check session timeout
    $session_timeout = isset($_SESSION['REMEMBER_ME']) ? (30 * 24 * 60 * 60) : 1440; // 30 days or 24 minutes
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
        // Session has expired
        $oldSessionData = $_SESSION;
        $_SESSION = array();

        if (!$isTest && session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();

            // Start a new session to prevent errors
            if (!headers_sent()) {
                session_start([
                    'cookie_httponly' => 1,
                    'cookie_secure' => 1,
                    'cookie_samesite' => 'Strict',
                    'gc_maxlifetime' => 1440
                ]);
            }
        }

        if (!$isTest && !headers_sent()) {
            setcookie('username', '', [
                'expires' => time() - 3600,
                'path' => $config['folder'],
                'domain' => $config['domain'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        if (!$isTest) {
            header('Location: ' . $app_root . '?page=login&timeout=1');
            exit();
        }
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
