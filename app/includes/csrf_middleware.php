<?php

require_once __DIR__ . '/../helpers/security.php';

function verifyCsrfToken() {
    $security = SecurityHelper::getInstance();

    // Skip CSRF check for GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return true;
    }

    // Skip CSRF check for initial login attempt
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
        isset($_GET['page']) && $_GET['page'] === 'login' && 
        !isset($_SESSION['username'])) {
        return true;
    }

    // Check CSRF token for all other POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$security->verifyCsrfToken($token)) {
            // Log CSRF attempt
            error_log("CSRF attempt detected from IP: " . $_SERVER['REMOTE_ADDR']);
            $logObject->insertLog(0, "CSRF attempt detected from IP: " . $_SERVER['REMOTE_ADDR'], 'system');

            // Return error message
            http_response_code(403);
            die('Invalid CSRF token. Please try again.');
        }
    }

    return true;
}
