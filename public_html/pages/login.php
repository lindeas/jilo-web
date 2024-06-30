<?php

require_once 'classes/database.php';
require 'classes/user.php';
unset($error);

try {
    $db = new Database('./jilo-web.db');
    $user = new User($db);

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // login successful
        if ( $user->login($username, $password) ) {
            // if remember_me is checked, max out the session
            if (isset($_POST['remember_me'])) {
                // 30*24*60*60 = 30 days
                $cookie_lifetime = '30 * 24 * 60 * 60';
                $gc_maxlifetime = '30 * 24 * 60 * 60';
            } else {
                // 0 - session end on browser close
                // 1440 - 24 minutes (default)
                $cookie_lifetime = '0';
                $gc_maxlifetime = '1440';
            }

            // set session lifetime
            ini_set('session.cookie_lifetime', $cookie_lifetime);
            ini_set('session.gc_maxlifetime', $gc_maxlifetime);
            session_set_cookie_params([
                'lifetime' => $lifetime,
                'samesite' => 'Strict',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']),
                'domain' => $domain,
                'path' => '/jilo-web/'
            ]);
            // redirect to index
            header('Location: index.php');
            exit();

        // login failed
        } else {
            $error = "Login failed.";
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include 'templates/form-login.php';

?>
