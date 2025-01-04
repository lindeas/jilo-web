<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Messages class
require_once __DIR__ . '/../classes/messages.php';

// Initialize messages array
$messages = [];

// Get any flash messages from previous requests
$flash_messages = Messages::getFlash();
if (!empty($flash_messages)) {
    $messages = array_merge($messages, $flash_messages);
}
