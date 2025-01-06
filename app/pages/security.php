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
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'add_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception(Messages::get('SECURITY', 'PERMISSION_DENIED')['message']);
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception(Messages::get('SECURITY', 'IP_REQUIRED')['message']);
                }
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                if (!$rateLimiter->addToWhitelist($_POST['ip_address'], $is_network, $_POST['description'] ?? '', $currentUser, $user_id)) {
                    throw new Exception(Messages::get('SECURITY', 'WHITELIST_ADD_ERROR')['message']);
                }
                Messages::flash('SECURITY', 'WHITELIST_ADD_SUCCESS');
                break;

            case 'remove_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception(Messages::get('SECURITY', 'PERMISSION_DENIED')['message']);
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception(Messages::get('SECURITY', 'IP_REQUIRED')['message']);
                }
                if (!$rateLimiter->removeFromWhitelist($_POST['ip_address'], $currentUser, $user_id)) {
                    throw new Exception(Messages::get('SECURITY', 'WHITELIST_REMOVE_ERROR')['message']);
                }
                Messages::flash('SECURITY', 'WHITELIST_REMOVE_SUCCESS');
                break;

            case 'add_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception(Messages::get('SECURITY', 'PERMISSION_DENIED')['message']);
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception(Messages::get('SECURITY', 'IP_REQUIRED')['message']);
                }
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                $expiry_hours = !empty($_POST['expiry_hours']) ? intval($_POST['expiry_hours']) : null;
                if (!$rateLimiter->addToBlacklist($_POST['ip_address'], $is_network, $_POST['reason'] ?? '', $currentUser, $user_id, $expiry_hours)) {
                    throw new Exception(Messages::get('SECURITY', 'BLACKLIST_ADD_ERROR')['message']);
                }
                Messages::flash('SECURITY', 'BLACKLIST_ADD_SUCCESS');
                break;

            case 'remove_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception(Messages::get('SECURITY', 'PERMISSION_DENIED')['message']);
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception(Messages::get('SECURITY', 'IP_REQUIRED')['message']);
                }
                if (!$rateLimiter->removeFromBlacklist($_POST['ip_address'], $currentUser, $user_id)) {
                    throw new Exception(Messages::get('SECURITY', 'BLACKLIST_REMOVE_ERROR')['message']);
                }
                Messages::flash('SECURITY', 'BLACKLIST_REMOVE_SUCCESS');
                break;
        }
    } catch (Exception $e) {
        $messages[] = ['category' => 'SECURITY', 'key' => 'CUSTOM_ERROR', 'custom_message' => $e->getMessage()];
        Messages::flash('SECURITY', 'CUSTOM_ERROR', 'custom_message');
    }

    if (empty($messages)) {
        // Only redirect if there were no errors
        header("Location: {$app_root}?page=security&section={$section}");
        exit;
    }
}

// Always show rate limit info message for rate limiting section
if ($section === 'ratelimit') {
    $messages[] = ['category' => 'SECURITY', 'key' => 'RATE_LIMIT_INFO'];
}

// Get current lists
$whitelisted = $rateLimiter->getWhitelistedIps();
$blacklisted = $rateLimiter->getBlacklistedIps();

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

// Load the template
include '../app/templates/security.php';

?>
