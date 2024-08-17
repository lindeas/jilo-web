<?php

function getError($message, $error = '', $environment = null) {
    global $config;
    $environment = $config['environment'] ?? 'production';

    if ($environment === 'production') {
        return 'There was an unexpected error. Please try again.';
    } else {
        return $error ?: $message;
    }
}

?>
