<?php

/**
 * Returns the user's IP address.
 * Uses global $user_IP set by Logger plugin if available, else falls back to server variables.
 *
 * @return string
 */
function getUserIP() {
    global $user_IP;
    if (!empty($user_IP)) {
        return $user_IP;
    }
    // Fallback to HTTP headers or REMOTE_ADDR
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // May contain multiple IPs
        $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($parts[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}
