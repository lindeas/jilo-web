<?php

/**
 * Config management.
 *
 * This page handles the config file.
 */

// Get any new feedback messages
include_once '../app/helpers/feedback.php';

require '../app/classes/config.php';
require '../app/classes/api_response.php';

// Initialize required objects
$userObject = new User($db);
$configObject = new Config();

// For AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set JSON content type for AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
}

// Ensure config file path is set
if (!isset($config_file) || empty($config_file)) {
    if ($isAjax) {
        ApiResponse::error('Config file path not set');
        exit;
    } else {
        Feedback::flash('ERROR', 'DEFAULT', 'Config file path not set');
        header('Location: ' . htmlspecialchars($app_root));
        exit;
    }
}

// Check if file is writable
$isWritable = is_writable($config_file);
$configMessage = '';
if (!$isWritable) {
    $configMessage = Feedback::render('ERROR', 'DEFAULT', 'Config file is not writable', false);
    if ($isAjax) {
        ApiResponse::error('Config file is not writable', null, 403);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user has permission to edit config
    if (!$userObject->hasRight($userId, 'edit config file')) {
        $logObject->log('error', "Unauthorized: User \"$currentUser\" tried to edit config file. IP: $user_IP", ['user_id' => $userId, 'scope' => 'system']);
        if ($isAjax) {
            ApiResponse::error('Forbidden: You do not have permission to edit the config file', null, 403);
            exit;
        } else {
            include '../app/templates/error-unauthorized.php';
            exit;
        }
    }

    // Apply rate limiting
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($db, 'config', $userId);

    // Ensure no output before this point
    ob_clean();

    // For AJAX requests, get JSON data
    if ($isAjax) {
        // Get raw input
        $jsonData = file_get_contents('php://input');
        if ($jsonData === false) {
            $logObject->log('error', "Failed to read request data for config update", ['user_id' => $userId, 'scope' => 'system']);
            ApiResponse::error('Failed to read request data');
            exit;
        }

        // Try to parse JSON
        $postData = json_decode($jsonData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            ApiResponse::error('Invalid JSON data received: ' . $error);
            exit;
        }

        // Try to update config file
        $result = $configObject->editConfigFile($postData, $config_file);
        if ($result['success']) {
            ApiResponse::success($result['updated'], 'Config file updated successfully');
        } else {
            ApiResponse::error($result['error']);
        }
        exit;
    } else {
        // Handle non-AJAX POST
        $result = $configObject->editConfigFile($_POST, $config_file);
        if ($result['success']) {
            Feedback::flash('NOTICE', 'DEFAULT', 'Config file updated successfully', true);
        } else {
            Feedback::flash('ERROR', 'DEFAULT', "Error updating config file: " . $result['error'], true);
        }

        header('Location: ' . htmlspecialchars($app_root) . '?page=config');
        exit;
    }
}

// Only include template for non-AJAX requests
if (!$isAjax) {
    /**
     * Handles GET requests to display templates.
     */

    if ($userObject->hasRight($userId, 'superuser') ||
      $userObject->hasRight($userId, 'view config file')) {
        include '../app/templates/config.php';
    } else {
        $logObject->log('error', "Unauthorized: User \"$currentUser\" tried to access \"config\" page. IP: $user_IP", ['user_id' => $userId, 'scope' => 'system']);
        include '../app/templates/error-unauthorized.php';
    }
}
