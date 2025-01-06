<?php

// Get any flash messages from previous request
$flash_messages = Messages::getFlash();
if (!empty($flash_messages)) {
    $messages = array_merge($messages, array_map(function($flash) {
        return [
            'category' => $flash['category'],
            'key' => $flash['key'],
            'custom_message' => $flash['custom_message']
        ];
    }, $flash_messages));
}

?>
