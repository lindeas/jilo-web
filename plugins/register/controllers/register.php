<?php

/**
 * User registration
 *
 * This page ("register") handles user registration if the feature is enabled in the configuration.
 * It accepts a POST request with a username and password, attempts to register the user,
 * and redirects to the login page on success or displays an error message on failure.
 */

// Define plugin base path if not already defined
if (!defined('PLUGIN_REGISTER_PATH')) {
    define('PLUGIN_REGISTER_PATH', dirname(__FILE__, 2) . '/');
}
require_once PLUGIN_REGISTER_PATH . 'models/register.php';
require_once dirname(__FILE__, 4) . '/app/classes/user.php';
require_once dirname(__FILE__, 4) . '/app/classes/validator.php';
require_once dirname(__FILE__, 4) . '/app/helpers/security.php';

// registration is allowed, go on
if ($config['registration_enabled'] == true) {

    try {
        global $dbWeb, $logObject, $userObject;

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

            // Apply rate limiting
            require_once dirname(__FILE__, 4) . '/app/includes/rate_limit_middleware.php';
            checkRateLimit($db, 'register');

            $security = SecurityHelper::getInstance();

            // Sanitize input
            $formData = $security->sanitizeArray($_POST, ['username', 'password', 'confirm_password', 'csrf_token', 'terms']);

            // Validate CSRF token
            if (!$security->verifyCsrfToken($formData['csrf_token'] ?? '')) {
                throw new Exception(Feedback::get('ERROR', 'CSRF_INVALID')['message']);
            }

            $validator = new Validator($formData);
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
                ],
                'terms' => [
                    'required' => true,
                    'equals' => 'on'
                ]
            ];

            $username = $formData['username'] ?? 'unknown';

            if ($validator->validate($rules)) {
                $password = $formData['password'];

                // registering
                $register = new Register($db);
                $result = $register->register($username, $password);

                // redirect to login
                if ($result === true) {
                    // Get the new user's ID for logging
                    $userId = $userObject->getUserId($username)[0]['id'];
                    $logObject->insertLog($userId, "Registration: New user \"$username\" registered successfully. IP: $user_IP", 'user');
                    Feedback::flash('NOTICE', 'DEFAULT', "Registration successful. You can log in now.");
                    header('Location: ' . htmlspecialchars($app_root . '?page=login'));
                    exit();
                // registration fail, redirect to login
                } else {
                    $logObject->insertLog(null, "Registration: Failed registration attempt for user \"$username\". IP: $user_IP. Reason: $result", 'system');
                    Feedback::flash('ERROR', 'DEFAULT', "Registration failed. $result");
                    header('Location: ' . htmlspecialchars($app_root . '?page=register'));
                    exit();
                }
            } else {
                $error = $validator->getFirstError();
                $logObject->insertLog(null, "Registration: Failed validation for user \"" . ($username ?? 'unknown') . "\". IP: $user_IP. Reason: $error", 'system');
                Feedback::flash('ERROR', 'DEFAULT', $error);
                header('Location: ' . htmlspecialchars($app_root . '?page=register'));
                exit();
            }
        }
    } catch (Exception $e) {
        $logObject->insertLog(null, "Registration: System error. IP: $user_IP. Error: " . $e->getMessage(), 'system');
        Feedback::flash('ERROR', 'DEFAULT', $e->getMessage());
    }

    // Get any new feedback messages
    include dirname(__FILE__, 4) . '/app/helpers/feedback.php';

    // Load the template
    include PLUGIN_REGISTER_PATH . 'views/form-register.php';

// registration disabled
} else {
    echo Feedback::render('NOTICE', 'DEFAULT', 'Registration is disabled', false);
}
