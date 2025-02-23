<?php

require_once __DIR__ . '/../classes/ratelimiter.php';
require_once __DIR__ . '/../helpers/logs.php';

/**
 * Rate limit middleware for page requests
 * 
 * @param Database $database Database connection
 * @param string $endpoint The endpoint being accessed
 * @param int|null $userId Current user ID if authenticated
 * @return bool True if request is allowed, false if rate limited
 */
function checkRateLimit($database, $endpoint, $userId = null) {
    global $app_root;
    $isTest = defined('PHPUNIT_RUNNING');
    $rateLimiter = new RateLimiter($database);
    $ipAddress = getUserIP();

    // Check if request is allowed
    if (!$rateLimiter->isPageRequestAllowed($ipAddress, $endpoint, $userId)) {
        // Get remaining requests for error message
        $remaining = $rateLimiter->getRemainingPageRequests($ipAddress, $endpoint, $userId);

        if (!$isTest) {
            // Set rate limit headers
            header('X-RateLimit-Remaining: ' . $remaining);
            header('X-RateLimit-Reset: ' . (time() + 60)); // Reset in 1 minute

            // Return 429 Too Many Requests
            http_response_code(429);

            // If AJAX request, return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Too many requests. Please try again in a minute.',
                    'messageData' => Feedback::getMessageData('ERROR', 'DEFAULT', 'Too many requests. Please try again in a minute.', true)
                ]);
            } else {
                // For regular requests, set flash message and redirect
                Feedback::flash('ERROR', 'DEFAULT', 'Too many requests. Please try again in a minute.', true);
                header('Location: ' . htmlspecialchars($app_root));
            }
            exit;
        }

        // In test mode, just set the flash message
        Feedback::flash('ERROR', 'DEFAULT', 'Too many requests. Please try again in a minute.', true);
        return false;
    }

    // Record this request
    $rateLimiter->recordPageRequest($ipAddress, $endpoint);
    return true;
}
