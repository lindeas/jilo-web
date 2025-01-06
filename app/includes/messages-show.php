<?php

if (isset($messages) && is_array($messages)) {
    foreach ($messages as $msg) {
        echo Messages::render($msg['category'], $msg['key'], $msg['custom_message'] ?? null);
    }
}

?>
