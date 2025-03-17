<?php

function getUserIP() {
    // get directly the user IP
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    // if user is behind some proxy
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // get only the first IP if there are more
    if (strpos($ip, ',') !== false) {
        $ip = explode(',', $ip)[0];
    }

    return trim($ip);
}
