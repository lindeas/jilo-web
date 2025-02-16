<?php

// Get any flash messages from previous request
$flash_messages = Feedback::getFlash();
if (!empty($flash_messages)) {
    $messages = array_merge($messages, array_map(function($flash) {
        return [
            'category' => $flash['category'],
            'key' => $flash['key'],
            'custom_message' => $flash['custom_message'] ?? null,
            'dismissible' => $flash['dismissible'] ?? false,
            'small' => $flash['small'] ?? false
        ];
    }, $flash_messages));
}

?>
