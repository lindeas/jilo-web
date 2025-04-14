<?php

/**
 * Session Middleware
 *
 * Validates session status and handles session timeout.
 * If session is invalid, redirects to login page.
 */

function applySessionMiddleware($config, $app_root, $isTest = false) {
    // Start session if not already started
    if (session_status() !== PHP_SESSION_ACTIVE) {
        Session::startSession();
    }

    // Check session validity
    if (!Session::isValidSession()) {
        // Only show session timeout message if there was an active session
        // and we haven't shown it yet
        if (isset($_SESSION['LAST_ACTIVITY']) && !isset($_SESSION['session_timeout_shown'])) {
            Feedback::flash('LOGIN', 'SESSION_TIMEOUT');
            $_SESSION['session_timeout_shown'] = true;
        }

        // Session invalid, clean up and redirect
        Session::cleanup($config);

        if (!$isTest) {
            header('Location: ' . $app_root . '?page=login');
            exit();
        }
        return false;
    }

    return true;
}
