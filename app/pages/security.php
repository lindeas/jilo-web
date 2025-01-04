<?php

// Check if user has any of the required rights
if (!($userObject->hasRight($user_id, 'superuser') ||
      $userObject->hasRight($user_id, 'edit whitelist') ||
      $userObject->hasRight($user_id, 'edit blacklist') ||
      $userObject->hasRight($user_id, 'edit ratelimiting'))) {
    include '../app/templates/error-unauthorized.php';
    exit;
}

$action = $_GET['action'] ?? 'view';
$section = $_GET['section'] ?? 'whitelist';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'add_whitelist':
            if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit whitelist')) {
                $ip = $_POST['ip_address'];
                $description = $_POST['description'];
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                $rateLimiter->addToWhitelist($ip, $is_network, $description, $currentUser);
            }
            break;

        case 'remove_whitelist':
            if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit whitelist')) {
                $ip = $_POST['ip_address'];
                $rateLimiter->removeFromWhitelist($ip, $user_id, $currentUser);
            }
            break;

        case 'add_blacklist':
            if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit blacklist')) {
                $ip = $_POST['ip_address'];
                $reason = $_POST['reason'];
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                $expiry_hours = empty($_POST['expiry_hours']) ? null : intval($_POST['expiry_hours']);
                $rateLimiter->addToBlacklist($ip, $is_network, $reason, $currentUser, null, $expiry_hours);
            }
            break;

        case 'remove_blacklist':
            if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit blacklist')) {
                $ip = $_POST['ip_address'];
                $rateLimiter->removeFromBlacklist($ip, $user_id, $currentUser);
            }
            break;
    }
    
    // Redirect to prevent form resubmission
    header("Location: {$app_root}?page=security&section={$section}");
    exit;
}

// Get the lists
$whitelisted = $rateLimiter->getWhitelistedIps();
$blacklisted = $rateLimiter->getBlacklistedIps();

// Include the template
include '../app/templates/security.php';
?>
