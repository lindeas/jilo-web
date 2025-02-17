<?php

if (isset($system_messages) && is_array($system_messages)) {
    foreach ($system_messages as $msg) {
        echo Feedback::render(
            $msg['category'],
            $msg['key'],
            $msg['custom_message'] ?? null,
            $msg['dismissible'] ?? false,
            $msg['small'] ?? false
        );
    }
}

?>
