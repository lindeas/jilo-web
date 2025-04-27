<?php

require_once __DIR__ . '/../helpers/security.php';

function applyCsrfMiddleware() {
    global $logObject, $user_IP;
    $security = SecurityHelper::getInstance();

    // Skip CSRF check for GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }

    // Skip CSRF check for initial login, registration, and 2FA verification attempts
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_GET['page']) && isset($_GET['action']) &&
        $_GET['page'] === 'login' && $_GET['action'] === 'verify' &&
        isset($_SESSION['2fa_pending_user_id'])) {
        return true;
    }

    // Skip CSRF check for initial login and registration attempts
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_GET['page']) &&
        in_array($_GET['page'], ['login', 'register']) &&
        !isset($_SESSION['username'])) {
        return true;
    }

    // Check CSRF token for all other POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check for token in POST data or headers
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$security->verifyCsrfToken($token)) {
            // Log CSRF attempt
            $ipAddress = $user_IP;
            $logMessage = sprintf(
                "CSRF attempt detected - IP: %s, Page: %s, User: %s",
                $ipAddress,
                $_GET['page'] ?? 'unknown',
                $_SESSION['username'] ?? 'anonymous'
            );
            $logObject->log('error', $logMessage, ['user_id' => null, 'scope' => 'system']);

            // Return error message
            http_response_code(403);
            die('Invalid CSRF token. Please try again.');
        }
    }

    return true;
}
