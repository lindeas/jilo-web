<?php

/**
 * User registration
 *
 * This page ("register") handles user registration if the feature is enabled in the configuration.
 * It accepts a POST request with a username and password, attempts to register the user,
 * and redirects to the login page on success or displays an error message on failure.
 */

// registration is allowed, go on
if ($config['registration_enabled'] == true) {

    try {

        // connect to database
        $dbWeb = connectDB($config);

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // registering
            $result = $userObject->register($username, $password);

            // redirect to login
            if ($result === true) {
                Messages::flash('NOTICE', 'DEFAULT', "Registration successful.<br />You can log in now.");
                header('Location: ' . htmlspecialchars($app_root));
                exit();
            // registration fail, redirect to login
            } else {
                Messages::flash('ERROR', 'DEFAULT', "Registration failed. $result");
                header('Location: ' . htmlspecialchars($app_root));
                exit();
            }
        }
    } catch (Exception $e) {
        Messages::flash('ERROR', 'DEFAULT', $e->getMessage());
    }

    // Get any new messages
    include '../app/includes/messages.php';
    include '../app/includes/messages-show.php';

    // Load the template
    include '../app/templates/form-register.php';

// registration disabled
} else {
    echo Messages::render('NOTICE', 'DEFAULT', 'Registration is disabled', false);
}

?>
