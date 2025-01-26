<?php

/**
 * Config management.
 *
 * This page handles the config file.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

require '../app/classes/config.php';

$configObject = new Config();

// For AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Check if file is writable
$isWritable = is_writable($config_file);
$configMessage = '';
if (!$isWritable) {
    $configMessage = Messages::render('ERROR', 'DEFAULT', 'Config file is not writable', false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure no output before this point
    ob_clean();

    // For AJAX requests, get JSON data
    if ($isAjax) {
        header('Content-Type: application/json');

        // Get raw input
        $jsonData = file_get_contents('php://input');
//DEBUG        error_log("Received JSON data: " . $jsonData);

        $postData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
//DEBUG            error_log("JSON decode error: " . $error);

            Messages::flash('ERROR', 'DEFAULT', 'Invalid JSON data received: ' . $error, true);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data received: ' . $error
            ]);
            exit;
        }

        // Try to update config file
        $result = $configObject->editConfigFile($postData, $config_file);
        if ($result === true) {
            $messageData = Messages::getMessageData('NOTICE', 'DEFAULT', 'Config file updated successfully', true);
//DEBUG            error_log("Config updated successfully");
            echo json_encode([
                'success' => true,
                'message' => 'Config file updated successfully',
                'messageData' => $messageData
            ]);
        } else {
//DEBUG            error_log("Config update error: " . $result);
            $messageData = Messages::getMessageData('ERROR', 'DEFAULT', "Error updating config file: $result", true);
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
        Messages::flash('NOTICE', 'DEFAULT', 'Config file updated successfully', true);
    } else {
        Messages::flash('ERROR', 'DEFAULT', "Error updating config file: $result", true);
    }

    header('Location: ' . htmlspecialchars($app_root) . '?page=config');
    exit;
}

// Only include template for non-AJAX requests
if (!$isAjax) {
    include '../app/templates/config.php';
}
?>
