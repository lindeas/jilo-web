<?php

require_once __DIR__ . '/../helpers/security.php';

function applyCsrfMiddleware() {
    $security = SecurityHelper::getInstance();

    // Skip CSRF check for GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
        $token = $_POST['csrf_token'] ?? '';
        if (!$security->verifyCsrfToken($token)) {
            // Log CSRF attempt
            $logMessage = sprintf(
                "CSRF attempt detected - IP: %s, Page: %s, User: %s",
                $_SERVER['REMOTE_ADDR'],
                $_GET['page'] ?? 'unknown',
                $_SESSION['username'] ?? 'anonymous'
            );
            $logObject->insertLog(0, $logMessage, 'system');

            // Return error message
            http_response_code(403);
            die('Invalid CSRF token. Please try again.');
        }
    }

    return true;
}
