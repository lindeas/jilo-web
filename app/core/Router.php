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
        $validSession = Session::isValidSession();
        if ($validSession) {
            return Session::getUsername();
        }

        if (!in_array($page, $public_pages, true)) {
            // flash session timeout if needed
            if (isset($_SESSION['LAST_ACTIVITY']) && !isset($_SESSION['session_timeout_shown'])) {
                Feedback::flash('LOGIN', 'SESSION_TIMEOUT');
                $_SESSION['session_timeout_shown'] = true;
            }
            // preserve flash messages
            $flash_messages = $_SESSION['flash_messages'] ?? [];
            Session::cleanup($config);
            $_SESSION['flash_messages'] = $flash_messages;

            // build login URL
            $loginUrl = $app_root . '?page=login';
            $trimmed = trim($page, '/?');
            if (!in_array($trimmed, INVALID_REDIRECT_PAGES, true)) {
                $loginUrl .= '&redirect=' . urlencode($_SERVER['REQUEST_URI']);
            }
            header('Location: ' . $loginUrl);
            exit();
        }

        return null;
    }
}
