<?php

require_once 'classes/database.php';
require 'classes/user.php';

// clear the global error var before login
unset($error);

try {
    $db = new Database($config['database']);
    $user = new User($db);

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // login successful
        if ( $user->login($username, $password) ) {
            // if remember_me is checked, max out the session
            if (isset($_POST['remember_me'])) {
                // 30*24*60*60 = 30 days
                $cookie_lifetime = 30 * 24 * 60 * 60;
                $gc_maxlifetime = 30 * 24 * 60 * 60;
            } else {
                // 0 - session end on browser close
                // 1440 - 24 minutes (default)
                $cookie_lifetime = 0;
                $gc_maxlifetime = 1440;
            }

            // set session lifetime
            ini_set('session.gc_maxlifetime', $gc_maxlifetime);
            session_set_cookie_params([
                'lifetime' => $cookie_lifetime,
                'samesite' => 'Strict',
                'httponly' => true,
                'secure' => isset($_SERVER['HTTPS']),
                'domain' => $config['domain'],
                'path' => $config['folder']
            ]);
            session_start();

            // redirect to index
            $_SESSION['notice'] = "Login successful";
            header('Location: index.php');
            exit();

        // login failed
        } else {
            $_SESSION['error'] = "Login failed.";
            header('Location: index.php');
            exit();
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include 'templates/form-login.php';

?>
