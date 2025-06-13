<?php

namespace App\Core;

use Session;
use Feedback;

class Router {
    /**
     * Check session validity and handle redirection for protected pages.
     * Returns current username if session is valid, null otherwise.
     */
    public static function checkAuth(array $config, string $app_root, array $public_pages, string $page): ?string {
        // Always allow login page to be accessed
        if ($page === 'login') {
            return null;
        }

        // Check if this is a public page
        $isPublicPage = in_array($page, $public_pages, true);

        // For public pages, don't validate session
        if ($isPublicPage) {
            return null;
        }

        // For protected pages, check if we have a valid session
        $validSession = Session::isValidSession(true);

        // If session is valid, return the username
        if ($validSession) {
            return Session::getUsername();
        }

        // If we get here, we need to redirect to login
        // Only show timeout message if we had an active session before
        if (isset($_SESSION['LAST_ACTIVITY']) && !isset($_SESSION['session_timeout_shown'])) {
            Feedback::flash('LOGIN', 'SESSION_TIMEOUT');
            $_SESSION['session_timeout_shown'] = true;
        }

        // Preserve flash messages
        $flash_messages = $_SESSION['flash_messages'] ?? [];
        Session::cleanup($config);
        $_SESSION['flash_messages'] = $flash_messages;

        // Build login URL with redirect if appropriate
        $loginUrl = $app_root . '?page=login';
        $trimmed = trim($page, '/?');
        if (!empty($trimmed) && !in_array($trimmed, INVALID_REDIRECT_PAGES, true)) {
            $loginUrl .= '&redirect=' . urlencode($_SERVER['REQUEST_URI']);
        }

        header('Location: ' . $loginUrl);
        exit();
    }
}
