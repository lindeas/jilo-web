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
        // Session invalid, clean up and redirect
        Session::cleanup($config);

        // Flash session timeout message
        Feedback::flash('LOGIN', 'SESSION_TIMEOUT');

        if (!$isTest) {
            header('Location: ' . $app_root . '?page=login');
            exit();
        }
        return false;
    }

    return true;
}
