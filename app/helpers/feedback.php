<?php

/**
 * Feedback Helper
 *
 * Combines functionality to handle retrieving and displaying feedback messages.
 */

// Get any flash messages from previous request
$flash_messages = Feedback::getFlash();
if (!empty($flash_messages)) {
    $system_messages = array_merge($system_messages ?? [], array_map(function($flash) {
        return [
            'category' => $flash['category'],
            'key' => $flash['key'],
            'custom_message' => $flash['custom_message'] ?? null,
            'dismissible' => $flash['dismissible'] ?? false,
            'small' => $flash['small'] ?? false,
            'sanitize' => $flash['sanitize'] ?? true
        ];
    }, $flash_messages));
}

// Show feedback messages
if (isset($system_messages) && is_array($system_messages)) {
    foreach ($system_messages as $msg) {
        echo Feedback::render(
            $msg['category'],
            $msg['key'],
            $msg['custom_message'] ?? null,
            $msg['dismissible'] ?? false,
            $msg['small'] ?? false,
            $msg['sanitize'] ?? true
        );
    }
}
