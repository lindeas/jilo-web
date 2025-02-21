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
        $dbWeb = connectDB($config)['db'];

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

            // Apply rate limiting
            require '../app/includes/rate_limit_middleware.php';
            checkRateLimit($dbWeb, 'register');

            require_once '../app/classes/validator.php';

            $validator = new Validator($_POST);
            $rules = [
                'username' => [
                    'required' => true,
                    'min' => 3,
                    'max' => 20
                ],
                'password' => [
                    'required' => true,
                    'min' => 8,
                    'max' => 100
                ],
                'confirm_password' => [
                    'required' => true,
                    'matches' => 'password'
                ]
            ];

            if ($validator->validate($rules)) {
                $username = $_POST['username'];
                $password = $_POST['password'];

                // registering
                $result = $userObject->register($username, $password);

                // redirect to login
                if ($result === true) {
                    Feedback::flash('NOTICE', 'DEFAULT', "Registration successful. You can log in now.");
                    header('Location: ' . htmlspecialchars($app_root));
                    exit();
                // registration fail, redirect to login
                } else {
                    Feedback::flash('ERROR', 'DEFAULT', "Registration failed. $result");
                    header('Location: ' . htmlspecialchars($app_root));
                    exit();
                }
            } else {
                Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
                header('Location: ' . htmlspecialchars($app_root . '?page=register'));
                exit();
            }
        }
    } catch (Exception $e) {
        Feedback::flash('ERROR', 'DEFAULT', $e->getMessage());
    }

    // Get any new feedback messages
    include '../app/helpers/feedback.php';

    // Load the template
    include '../app/templates/form-register.php';

// registration disabled
} else {
    echo Feedback::render('NOTICE', 'DEFAULT', 'Registration is disabled', false);
}

?>
