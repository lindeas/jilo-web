<?php

// Check if user has any of the required rights
if (!($userObject->hasRight($userId, 'superuser') ||
      $userObject->hasRight($userId, 'edit whitelist') ||
      $userObject->hasRight($userId, 'edit blacklist') ||
      $userObject->hasRight($userId, 'edit ratelimiting'))) {
    include '../app/templates/error-unauthorized.php';
    exit;
}

// Get current section
$section = isset($_POST['section']) ? $_POST['section'] : (isset($_GET['section']) ? $_GET['section'] : 'whitelist');

// Initialize RateLimiter
require_once '../app/classes/ratelimiter.php';
$rateLimiter = new RateLimiter($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once '../app/classes/validator.php';

    // Apply rate limiting for security operations
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($db, 'security', $userId);

    $action = $_POST['action'];
    $validator = new Validator($_POST);

    try {
        switch ($action) {
            case 'add_whitelist':
                if (!$userObject->hasRight($userId, 'superuser') && !$userObject->hasRight($userId, 'edit whitelist')) {
                    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
                    break;
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45, // Max length for IPv6
                        'ip' => true
                    ],
                    'description' => [
                        'required' => true,
                        'max' => 255
                    ]
                ];

                if ($validator->validate($rules)) {
                    $is_network = isset($_POST['is_network']) && $_POST['is_network'] === 'on';
                    if (!$rateLimiter->addToWhitelist($_POST['ip_address'], $is_network, $_POST['description'] ?? '', $currentUser, $userId)) {
                        Feedback::flash('SECURITY', 'WHITELIST_ADD_FAILED');
                    } else {
                        Feedback::flash('SECURITY', 'WHITELIST_ADD_SUCCESS');
                    }
                } else {
                    Feedback::flash('SECURITY', 'WHITELIST_ADD_ERROR_IP', $validator->getFirstError());
                }
                break;

            case 'remove_whitelist':
                if (!$userObject->hasRight($userId, 'superuser') && !$userObject->hasRight($userId, 'edit whitelist')) {
                    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
                    break;
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45,
                        'ip' => true
                    ]
                ];

                if ($validator->validate($rules)) {
                    if (!$rateLimiter->removeFromWhitelist($_POST['ip_address'], $currentUser, $userId)) {
                        Feedback::flash('SECURITY', 'WHITELIST_REMOVE_FAILED');
                    } else {
                        Feedback::flash('SECURITY', 'WHITELIST_REMOVE_SUCCESS');
                    }
                } else {
                    Feedback::flash('SECURITY', 'WHITELIST_REMOVE_FAILED', $validator->getFirstError());
                }
                break;

            case 'add_blacklist':
                if (!$userObject->hasRight($userId, 'superuser') && !$userObject->hasRight($userId, 'edit blacklist')) {
                    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
                    break;
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45,
                        'ip' => true
                    ],
                    'reason' => [
                        'required' => true,
                        'max' => 255
                    ],
                    'expiry_hours' => [
                        'numeric' => true,
                        'min' => 0,
                        'max' => 8760 // 1 year in hours
                    ]
                ];

                if ($validator->validate($rules)) {
                    $is_network = isset($_POST['is_network']) && $_POST['is_network'] === 'on';
                    $expiry_hours = !empty($_POST['expiry_hours']) ? (int)$_POST['expiry_hours'] : null;

                    if (!$rateLimiter->addToBlacklist($_POST['ip_address'], $is_network, $_POST['reason'], $currentUser, $userId, $expiry_hours)) {
                        Feedback::flash('SECURITY', 'BLACKLIST_ADD_FAILED');
                    } else {
                        Feedback::flash('SECURITY', 'BLACKLIST_ADD_SUCCESS');
                    }
                } else {
                    Feedback::flash('SECURITY', 'BLACKLIST_ADD_ERROR_IP', $validator->getFirstError());
                }
                break;

            case 'remove_blacklist':
                if (!$userObject->hasRight($userId, 'superuser') && !$userObject->hasRight($userId, 'edit blacklist')) {
                    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
                    break;
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45,
                        'ip' => true
                    ]
                ];

                if ($validator->validate($rules)) {
                    if (!$rateLimiter->removeFromBlacklist($_POST['ip_address'], $currentUser, $userId)) {
                        Feedback::flash('SECURITY', 'BLACKLIST_REMOVE_FAILED');
                    } else {
                        Feedback::flash('SECURITY', 'BLACKLIST_REMOVE_SUCCESS');
                    }
                } else {
                    Feedback::flash('SECURITY', 'BLACKLIST_REMOVE_FAILED', $validator->getFirstError());
                }
                break;

            default:
                Feedback::flash('ERROR', 'INVALID_ACTION');
        }
    } catch (Exception $e) {
        Feedback::flash('ERROR', $e->getMessage());
    }

    // Redirect back to the appropriate section
    header("Location: $app_root?page=security&section=" . urlencode($section));
    exit;
}

// Always show rate limit info message for rate limiting section
if ($section === 'ratelimit') {
    $system_messages[] = ['category' => 'SECURITY', 'key' => 'RATE_LIMIT_INFO'];
}

// Get current lists
$whitelisted = $rateLimiter->getWhitelistedIps();
$blacklisted = $rateLimiter->getBlacklistedIps();

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/security.php';
