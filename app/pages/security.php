<?php

// Check if user has any of the required rights
if (!($userObject->hasRight($user_id, 'superuser') ||
      $userObject->hasRight($user_id, 'edit whitelist') ||
      $userObject->hasRight($user_id, 'edit blacklist') ||
      $userObject->hasRight($user_id, 'edit ratelimiting'))) {
    include '../app/templates/error-unauthorized.php';
    exit;
}

// Initialize variables for feedback messages
$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $section = isset($_POST['section']) ? $_POST['section'] : (isset($_GET['section']) ? $_GET['section'] : 'whitelist');

    try {
        switch ($action) {
            case 'add_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception('You do not have permission to modify the whitelist.');
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception('IP address is required.');
                }
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                if (!$rateLimiter->addToWhitelist($_POST['ip_address'], $is_network, $_POST['description'] ?? '', $currentUser, $user_id)) {
                    throw new Exception('Failed to add IP to whitelist. Please check the IP format.');
                }
                $success_message = 'IP address successfully added to whitelist.';
                break;

            case 'remove_whitelist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit whitelist')) {
                    throw new Exception('You do not have permission to modify the whitelist.');
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception('IP address is required.');
                }
                if (!$rateLimiter->removeFromWhitelist($_POST['ip_address'], $currentUser, $user_id)) {
                    throw new Exception('Failed to remove IP from whitelist.');
                }
                $success_message = 'IP address successfully removed from whitelist.';
                break;

            case 'add_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception('You do not have permission to modify the blacklist.');
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception('IP address is required.');
                }
                $is_network = isset($_POST['is_network']) ? 1 : 0;
                $expiry_hours = !empty($_POST['expiry_hours']) ? intval($_POST['expiry_hours']) : null;
                if (!$rateLimiter->addToBlacklist($_POST['ip_address'], $is_network, $_POST['reason'] ?? '', $currentUser, $user_id, $expiry_hours)) {
                    throw new Exception('Failed to add IP to blacklist. Please check the IP format.');
                }
                $success_message = 'IP address successfully added to blacklist.';
                break;

            case 'remove_blacklist':
                if (!$userObject->hasRight($user_id, 'superuser') && !$userObject->hasRight($user_id, 'edit blacklist')) {
                    throw new Exception('You do not have permission to modify the blacklist.');
                }
                if (empty($_POST['ip_address'])) {
                    throw new Exception('IP address is required.');
                }
                if (!$rateLimiter->removeFromBlacklist($_POST['ip_address'], $currentUser, $user_id)) {
                    throw new Exception('Failed to remove IP from blacklist.');
                }
                $success_message = 'IP address successfully removed from blacklist.';
                break;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }

    if (empty($error_message)) {
        // Only redirect if there was no error
        header("Location: {$app_root}?page=security&section={$section}" . 
               ($success_message ? '&success=' . urlencode($success_message) : ''));
        exit;
    }
}

// Get success message from URL if redirected after successful action
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Get current lists
$whitelisted = $rateLimiter->getWhitelistedIps();
$blacklisted = $rateLimiter->getBlacklistedIps();

// Get current section
$section = isset($_GET['section']) ? $_GET['section'] : 'whitelist';

// Include template
include '../app/templates/security.php';

?>
