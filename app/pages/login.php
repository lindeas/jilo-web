<?php

/**
 * User Login
 *
 * This page ("login") handles user login, session management, cookie handling, and error logging.
 * Supports "remember me" functionality to extend session duration.
 *
 * Global Variables Used:
 * - `$userObject`: Instance of the user management class.
 * - `$logObject`: Instance of the logging class.
 * - `$config`: Configuration array containing settings for cookies, domain, and folder.
 * - `$app_root`: Base URL of the application.
 * - `$user_IP`: Captured IP address of the user.
 *
 * Actions Performed:
 * - Validates login credentials.
 * - Manages session and cookies based on "remember me" option.
 * - Logs successful and failed login attempts.
 * - Displays login form and optional custom messages.
 */

// clear the global error var before login
unset($error);

try {

    // connect to database
    $dbWeb = connectDB($config);

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // login successful
        if ( $userObject->login($username, $password) ) {
            // if remember_me is checked, max out the session
            if (isset($_POST['remember_me'])) {
                // 30*24*60*60 = 30 days
                $cookie_lifetime = 30 * 24 * 60 * 60;
                $setcookie_lifetime = time() + 30 * 24 * 60 * 60;
                $gc_maxlifetime = 30 * 24 * 60 * 60;
            } else {
                // 0 - session end on browser close
                // 1440 - 24 minutes (default)
                $cookie_lifetime = 0;
                $setcookie_lifetime = 0;
                $gc_maxlifetime = 1440;
            }

            // set session lifetime and cookies
            setcookie('username', $username, [
                'expires'	=> $setcookie_lifetime,
                'path'		=> $config['folder'],
                'domain'	=> $config['domain'],
                'secure'	=> isset($_SERVER['HTTPS']),
                'httponly'	=> true,
                'samesite'	=> 'Strict'
            ]);

            // redirect to index
            $_SESSION['notice'] = "Login successful";
            $user_id = $userObject->getUserId($username)[0]['id'];
            $logObject->insertLog($user_id, "Login: User \"$username\" logged in. IP: $user_IP", 'user');
            header('Location: ' . htmlspecialchars($app_root));
            exit();

        // login failed
        } else {
            $_SESSION['error'] = "Login failed.";
            $user_id = $userObject->getUserId($username)[0]['id'];
            $logObject->insertLog($user_id, "Login: Failed login attempt for user \"$username\". IP: $user_IP", 'user');
            header('Location: ' . htmlspecialchars($app_root));
            exit();
        }
    }
} catch (Exception $e) {
    $error = getError('There was an unexpected error. Please try again.', $e->getMessage());
}

if (!empty($config['login_message'])) {
    $notice = $config['login_message'];
    include '../app/templates/block-message.php';
}

include '../app/templates/form-login.php';

?>
