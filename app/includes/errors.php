<?php

/**
 * Generate an error or notice message based on the environment.
 *
 * In a production environment, hides detailed error messages and returns
 * a generic message. In other environments, returns the provided message.
 *
 * @param string $message            A user-friendly message to display.
 * @param string $error              The detailed error message for debugging (optional).
 * @param string|null $environment   The environment type ('production', 'development', etc.). If null, defaults to the configured environment.
 *
 * @return string                    The appropriate message based on the environment.
 */
function getError($message, $error = '', $environment = null) {
    global $config;
    $environment = $config['environment'] ?? 'production';

    if ($environment === 'production') {
        return 'There was an unexpected error. Please try again.';
    } else {
        return $error ?: $message;
    }
}

/**
 * Render a message if it exists, and optionally unset it after display.
 *
 * @param string $message   The message to display.
 * @param string $type      The type of message (e.g., 'error', 'notice').
 * @param bool   $unset     Whether to unset the message after display.
 */
function renderMessage(&$message, $type, $unset = false) {
    if (isset($message)) {
        echo "\t\t<div class=\"{$type}\">" . $message . "</div>\n";
        if ($unset) {
            $message = null;
        }
    }
}

?>
