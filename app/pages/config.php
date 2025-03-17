<?php

/**
 * Config management.
 *
 * This page handles the config file.
 */

// Get any new feedback messages
include '../app/helpers/feedback.php';

require '../app/classes/config.php';
$configObject = new Config();

require '../app/includes/rate_limit_middleware.php';

// For AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Check if file is writable
$isWritable = is_writable($config_file);
$configMessage = '';
if (!$isWritable) {
    $configMessage = Feedback::render('ERROR', 'DEFAULT', 'Config file is not writable', false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Apply rate limiting
    checkRateLimit($dbWeb, 'config', $user_id);

    // Ensure no output before this point
    ob_clean();

    // For AJAX requests, get JSON data
    if ($isAjax) {
        header('Content-Type: application/json');

        // Get raw input
        $jsonData = file_get_contents('php://input');

        $postData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();

            Feedback::flash('ERROR', 'DEFAULT', 'Invalid JSON data received: ' . $error, true);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data received: ' . $error
            ]);
            exit;
        }

        // Try to update config file
        $result = $configObject->editConfigFile($postData, $config_file);
        if ($result === true) {
            $messageData = Feedback::getMessageData('NOTICE', 'DEFAULT', 'Config file updated successfully', true);
            echo json_encode([
                'success' => true,
                'message' => 'Config file updated successfully',
                'messageData' => $messageData
            ]);
        } else {
            $messageData = Feedback::getMessageData('ERROR', 'DEFAULT', "Error updating config file: $result", true);
            echo json_encode([
                'success' => false,
                'message' => "Error updating config file: $result",
                'messageData' => $messageData
            ]);
        }
        exit;
    }

    // Handle non-AJAX POST
    $result = $configObject->editConfigFile($_POST, $config_file);
    if ($result === true) {
        Feedback::flash('NOTICE', 'DEFAULT', 'Config file updated successfully', true);
    } else {
        Feedback::flash('ERROR', 'DEFAULT', "Error updating config file: $result", true);
    }

    header('Location: ' . htmlspecialchars($app_root) . '?page=config');
    exit;
}

// Only include template for non-AJAX requests
if (!$isAjax) {
    /**
     * Handles GET requests to display templates.
     */

    if ($userObject->hasRight($user_id, 'view config file')) {
        include '../app/templates/config.php';
    } else {
        $logObject->insertLog($user_id, "Unauthorized: User \"$currentUser\" tried to access \"config\" page. IP: $user_IP", 'system');
        include '../app/templates/error-unauthorized.php';
    }
}
