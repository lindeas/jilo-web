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
    $dbWeb = connectDB($config);

    // Initialize RateLimiter
    require_once '../app/classes/ratelimiter.php';
    $rateLimiter = new RateLimiter($dbWeb['db']);

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

            $username = $_POST['username'];
            $password = $_POST['password'];

            // Check if IP is blacklisted
            if ($rateLimiter->isIpBlacklisted($user_IP)) {
                throw new Exception(Feedback::get('LOGIN', 'IP_BLACKLISTED')['message']);
            }

            // Check rate limiting (but skip if IP is whitelisted)
            if (!$rateLimiter->isIpWhitelisted($user_IP)) {
                $attempts = $rateLimiter->getRecentAttempts($user_IP);
                if ($attempts >= $rateLimiter->maxAttempts) {
                    throw new Exception(Feedback::get('LOGIN', 'LOGIN_BLOCKED')['message']);
                }
            }

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

                // Configure secure session settings
                ini_set('session.cookie_httponly', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
                ini_set('session.cookie_samesite', 'Strict');
                ini_set('session.gc_maxlifetime', $gc_maxlifetime);

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
                Feedback::flash('LOGIN', 'LOGIN_SUCCESS', null, true);
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
    Feedback::flash('ERROR', 'DEFAULT', 'There was an unexpected error. Please try again.');
}

// Show configured login message if any
if (!empty($config['login_message'])) {
    echo Feedback::render('NOTICE', 'DEFAULT', $config['login_message'], false, false, false);
}

// Get any new feedback messages
include '../app/includes/feedback-get.php';
include '../app/includes/feedback-show.php';

// Load the template
include '../app/templates/form-login.php';

?>
