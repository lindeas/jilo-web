<?php

/**
 * User login
 *
 * This page ("login") handles user login, session management, cookie handling, and error logging.
 * Supports "remember me" functionality to extend session duration.
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
    $dbWeb = connectDB($config)['db'];

    // Initialize RateLimiter
    require_once '../app/classes/ratelimiter.php';
    $rateLimiter = new RateLimiter($dbWeb);

    // Get user IP
    $user_IP = getUserIP();

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        try {
            // Validate form data
            $security = SecurityHelper::getInstance();
            $formData = $security->sanitizeArray($_POST, ['username', 'password', 'remember_me', 'csrf_token']);

            $validationRules = [
                'username' => [
                    'type' => 'string',
                    'required' => true,
                    'min' => 3,
                    'max' => 20
                ],
                'password' => [
                    'type' => 'string',
                    'required' => true,
                    'min' => 2
                ]
            ];

            $errors = $security->validateFormData($formData, $validationRules);
            if (!empty($errors)) {
                throw new Exception("Invalid input: " . implode(", ", $errors));
            }

            $username = $formData['username'];
            $password = $formData['password'];

            // Skip all checks if IP is whitelisted
            if (!$rateLimiter->isIpWhitelisted($user_IP)) {
                // Check if IP is blacklisted
                if ($rateLimiter->isIpBlacklisted($user_IP)) {
                    throw new Exception(Feedback::get('LOGIN', 'IP_BLACKLISTED')['message']);
                }

                // Check rate limiting before recording attempt
                if ($rateLimiter->tooManyAttempts($username, $user_IP)) {
                    throw new Exception(Feedback::get('LOGIN', 'TOO_MANY_ATTEMPTS')['message']);
                }

                // Record this attempt
                $rateLimiter->attempt($username, $user_IP);
            }

            // login successful
            if ( $userObject->login($username, $password) ) {
                // if remember_me is checked, max out the session
                if (isset($formData['remember_me'])) {
                    // 30*24*60*60 = 30 days
                    $cookie_lifetime = 30 * 24 * 60 * 60;
                    $setcookie_lifetime = time() + 30 * 24 * 60 * 60;
                } else {
                    // 0 - session end on browser close
                    $cookie_lifetime = 0;
                    $setcookie_lifetime = 0;
                }

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // set session lifetime and cookies
                setcookie('username', $username, [
                    'expires'	=> $setcookie_lifetime,
                    'path'		=> $config['folder'],
                    'domain'	=> $config['domain'],
                    'secure'	=> isset($_SERVER['HTTPS']),
                    'httponly'	=> true,
                    'samesite'	=> 'Strict'
                ]);

                // Set session variables
                $_SESSION['USER_ID'] = $userObject->getUserId($username)[0]['id'];
                $_SESSION['USERNAME'] = $username;
                $_SESSION['LAST_ACTIVITY'] = time();
                if (isset($formData['remember_me'])) {
                    $_SESSION['REMEMBER_ME'] = true;
                }

                // Log successful login
                $user_id = $userObject->getUserId($username)[0]['id'];
                $logObject->insertLog($user_id, "Login: User \"$username\" logged in. IP: $user_IP", 'user');

                // Set success message and redirect
                Feedback::flash('LOGIN', 'LOGIN_SUCCESS');
                header('Location: ' . htmlspecialchars($app_root));
                exit();
            } else {
                throw new Exception(Feedback::get('LOGIN', 'LOGIN_FAILED')['message']);
            }
        } catch (Exception $e) {
            // Log the failed attempt
            Feedback::flash('ERROR', 'DEFAULT', $e->getMessage());
            if (isset($username)) {
                $user_id = $userObject->getUserId($username)[0]['id'] ?? 0;
                $logObject->insertLog($user_id, "Login: Failed login attempt for user \"$username\". IP: $user_IP. Reason: {$e->getMessage()}", 'user');
            }
        }
    }
} catch (Exception $e) {
    Feedback::flash('ERROR', 'DEFAULT');
}

// Show configured login message if any
if (!empty($config['login_message'])) {
    echo Feedback::render('NOTICE', 'DEFAULT', $config['login_message'], false);
}

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/form-login.php';
