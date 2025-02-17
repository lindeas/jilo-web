<?php

// Check if user has any of the required rights
if (!($userObject->hasRight($user_id, 'superuser') ||
      $userObject->hasRight($user_id, 'edit whitelist') ||
      $userObject->hasRight($user_id, 'edit blacklist') ||
      $userObject->hasRight($user_id, 'edit ratelimiting'))) {
    include '../app/templates/error-unauthorized.php';
    exit;
}

if (!isset($currentUser)) {
    include '../app/templates/error-unauthorized.php';
    exit;
}

// Get current section
$section = isset($_POST['section']) ? $_POST['section'] : (isset($_GET['section']) ? $_GET['section'] : 'whitelist');

// Initialize RateLimiter
require_once '../app/classes/ratelimiter.php';
$rateLimiter = new RateLimiter($dbWeb);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    require_once '../app/classes/validator.php';
    $action = $_POST['action'];
    $validator = new Validator($_POST);

    try {
        switch ($action) {
            case 'add_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception('Unauthorized action');
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45 // IPv6 max length
                    ],
                    'description' => [
                        'max' => 255
                    ]
                ];

                if ($validator->validate($rules)) {
                    $is_network = isset($_POST['is_network']) && $_POST['is_network'] === 'on';
                    if (!$rateLimiter->addToWhitelist($_POST['ip_address'], $is_network, $_POST['description'] ?? '', $currentUser, $user_id)) {
                        throw new Exception('Failed to add IP to whitelist');
                    }
                    Feedback::flash('SECURITY', 'WHITELIST_ADD_SUCCESS');
                } else {
                    Feedback::flash('SECURITY', 'WHITELIST_ADD_ERROR', $validator->getFirstError());
                }
                break;

            case 'remove_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception('Unauthorized action');
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45
                    ]
                ];

                if ($validator->validate($rules)) {
                    if (!$rateLimiter->removeFromWhitelist($_POST['ip_address'], $currentUser, $user_id)) {
                        throw new Exception('Failed to remove IP from whitelist');
                    }
                    Feedback::flash('SECURITY', 'WHITELIST_REMOVE_SUCCESS');
                } else {
                    Feedback::flash('SECURITY', 'WHITELIST_REMOVE_ERROR', $validator->getFirstError());
                }
                break;

            case 'add_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception('Unauthorized action');
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45
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

                    if (!$rateLimiter->addToBlacklist($_POST['ip_address'], $is_network, $_POST['reason'], $currentUser, $user_id, $expiry_hours)) {
                        throw new Exception('Failed to add IP to blacklist');
                    }
                    Feedback::flash('SECURITY', 'BLACKLIST_ADD_SUCCESS');
                } else {
                    Feedback::flash('SECURITY', 'BLACKLIST_ADD_ERROR', $validator->getFirstError());
                }
                break;

            case 'remove_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception('Unauthorized action');
                }

                $rules = [
                    'ip_address' => [
                        'required' => true,
                        'max' => 45
                    ]
                ];

                if ($validator->validate($rules)) {
                    if (!$rateLimiter->removeFromBlacklist($_POST['ip_address'], $currentUser, $user_id)) {
                        throw new Exception('Failed to remove IP from blacklist');
                    }
                    Feedback::flash('SECURITY', 'BLACKLIST_REMOVE_SUCCESS');
                } else {
                    Feedback::flash('SECURITY', 'BLACKLIST_REMOVE_ERROR', $validator->getFirstError());
                }
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        Feedback::flash('SECURITY', 'ERROR', $e->getMessage());
    }

    // Redirect back to the appropriate section
    header("Location: $app_root?page=security&section=" . urlencode($section));
    exit;
}

// Always show rate limit info message for rate limiting section
if ($section === 'ratelimit') {
    $messages[] = ['category' => 'SECURITY', 'key' => 'RATE_LIMIT_INFO'];
}

// Get current lists
$whitelisted = $rateLimiter->getWhitelistedIps();
$blacklisted = $rateLimiter->getBlacklistedIps();

// Get any new feedback messages
include '../app/includes/feedback-get.php';
include '../app/includes/feedback-show.php';

// Load the template
include '../app/templates/security.php';

?>
