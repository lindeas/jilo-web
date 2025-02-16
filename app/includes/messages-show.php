<?php
if (isset($messages) && is_array($messages)) {
    foreach ($messages as $msg) {
        echo Feedback::render($msg['category'], $msg['key'], $msg['custom_message'] ?? null, $msg['dismissible'] ?? false, $msg['small'] ?? false);
    }
}

?>
