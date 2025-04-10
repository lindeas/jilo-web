<?php

/**
 * User login
 *
 * This page ("login") handles user login, session management, cookie handling, and error logging.
 * Supports "remember me" functionality to extend session duration and two-factor authentication.
 *
 * Actions Performed:
 * - Validates login credentials
 * - Handles two-factor authentication if enabled
 * - Manages session and cookies based on "remember me" option
 * - Logs successful and failed login attempts
 * - Displays login form and optional custom messages
 */

// clear the global error var before login
unset($error);

try {
    // connect to database
    $db = connectDB($config)['db'];

    // Initialize RateLimiter
    require_once '../app/classes/ratelimiter.php';
    $rateLimiter = new RateLimiter($db);

    // Get user IP
    $user_IP = getUserIP();

    $action = $_REQUEST['action'] ?? '';

    if ($action === 'verify' && isset($_SESSION['2fa_pending_user_id'])) {
        // Handle 2FA verification
        $code = $_POST['code'] ?? '';
        $userId = $_SESSION['2fa_pending_user_id'];
        $username = $_SESSION['2fa_pending_username'];
        $rememberMe = isset($_SESSION['2fa_pending_remember']);

        require_once '../app/classes/twoFactorAuth.php';
        $twoFactorAuth = new TwoFactorAuthentication($db);

        if ($twoFactorAuth->verify($userId, $code)) {
            // Complete login
            handleSuccessfulLogin($userId, $username, $rememberMe, $config, $logObject, $user_IP);

            // Clean up 2FA session data
            unset($_SESSION['2fa_pending_user_id']);
            unset($_SESSION['2fa_pending_username']);
            unset($_SESSION['2fa_pending_remember']);

            exit();
        }

        // If we get here (and we have code submitted), verification failed
        if (!empty($code)) {
            Feedback::flash('ERROR', 'DEFAULT', 'Invalid verification code');
        }

        // Get any new feedback messages
        include '../app/helpers/feedback.php';

        // Load the 2FA verification template
        include '../app/templates/credentials-2fa-verify.php';
        exit();
    } elseif ($action === 'forgot') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle password reset request
            try {
                // Validate CSRF token
                $security = SecurityHelper::getInstance();
                if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token. Please try again.');
                }

                // Apply rate limiting
                if (!$rateLimiter->isIpWhitelisted($user_IP)) {
                    if ($rateLimiter->isIpBlacklisted($user_IP)) {
                        throw new Exception(Feedback::get('LOGIN', 'IP_BLACKLISTED')['message']);
                    }
                    if ($rateLimiter->tooManyAttempts('password_reset', $user_IP)) {
                        throw new Exception(Feedback::get('LOGIN', 'TOO_MANY_ATTEMPTS')['message']);
                    }
                    $rateLimiter->attempt('password_reset', $user_IP);
                }

                // Validate email
                $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
                if (!$email) {
                    throw new Exception('Please enter a valid email address.');
                }

                // Process reset request
                require_once '../app/classes/passwordReset.php';
                $resetHandler = new PasswordReset($db);
                $result = $resetHandler->requestReset($email);

                // Always show same message whether email exists or not for security
                Feedback::flash('NOTICE', 'DEFAULT', $result['message']);
                header("Location: $app_root?page=login");
                exit();

            } catch (Exception $e) {
                Feedback::flash('ERROR', 'DEFAULT', $e->getMessage());
            }
        }

        // Generate CSRF token
        $security = SecurityHelper::getInstance();
        $security->generateCsrfToken();

        // Load the forgot password form
        include '../app/helpers/feedback.php';
        include '../app/templates/form-password-forgot.php';
        exit();

    } elseif ($action === 'reset' && isset($_GET['token'])) {
        // Handle password reset
        try {
            require_once '../app/classes/passwordReset.php';
            $resetHandler = new PasswordReset($db);
            $token = $_GET['token'];

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validate CSRF token
                $security = SecurityHelper::getInstance();
                if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    throw new Exception('Invalid security token. Please try again.');
                }

                // Apply rate limiting
                if (!$rateLimiter->isIpWhitelisted($user_IP)) {
                    if ($rateLimiter->tooManyAttempts('password_reset', $user_IP)) {
                        throw new Exception(Feedback::get('LOGIN', 'TOO_MANY_ATTEMPTS')['message']);
                    }
                    $rateLimiter->attempt('password_reset', $user_IP);
                }

                // Validate password
                require_once '../app/classes/validator.php';
                $validator = new Validator($_POST);
                $rules = [
                    'new_password' => [
                        'required' => true,
                        'min' => 8
                    ],
                    'confirm_password' => [
                        'required' => true,
                        'matches' => 'new_password'
                    ]
                ];

                if (!$validator->validate($rules)) {
                    throw new Exception($validator->getFirstError());
                }

                // Reset password
                if ($resetHandler->resetPassword($token, $_POST['new_password'])) {
                    Feedback::flash('NOTICE', 'DEFAULT', 'Your password has been reset successfully. You can now log in.');
                    header("Location: $app_root?page=login");
                    exit();
                }
                throw new Exception('Invalid or expired reset link. Please request a new one.');
            }

            // Verify token is valid
            $validation = $resetHandler->validateToken($token);
            if (!$validation['valid']) {
                throw new Exception('Invalid or expired reset link. Please request a new one.');
            }

            // Show reset password form
            include '../app/helpers/feedback.php';
            include '../app/templates/form-password-reset.php';
            exit();

        } catch (Exception $e) {
            Feedback::flash('ERROR', 'DEFAULT', $e->getMessage());
            header("Location: $app_root?page=login&action=forgot");
            exit();
        }
    }

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' && $action !== 'verify' ) {
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
                    'min' => 5
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

            // Attempt login
            $loginResult = $userObject->login($username, $password);

            if (is_array($loginResult)) {
                switch ($loginResult['status']) {
                    case 'requires_2fa':
                        // Store pending 2FA info
                        $_SESSION['2fa_pending_user_id'] = $loginResult['user_id'];
                        $_SESSION['2fa_pending_username'] = $loginResult['username'];
                        if (isset($formData['remember_me'])) {
                            $_SESSION['2fa_pending_remember'] = true;
                        }

                        // Redirect to 2FA verification
                        header('Location: ?page=login&action=verify');
                        exit();

                    case 'success':
                        // Complete login
                        handleSuccessfulLogin($loginResult['user_id'], $loginResult['username'],
                            isset($formData['remember_me']), $config, $logObject, $user_IP);
                        exit();

                    default:
                        throw new Exception($loginResult['message'] ?? 'Login failed');
                }
            }

            throw new Exception(Feedback::get('LOGIN', 'LOGIN_FAILED')['message']);
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

/**
 * Handle successful login by setting up session and cookies
 */
function handleSuccessfulLogin($userId, $username, $rememberMe, $config, $logObject, $userIP) {
    if ($rememberMe) {
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
        'expires' => $setcookie_lifetime,
        'path'    => $config['folder'],
        'domain'  => $config['domain'],
        'secure'  => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['USERNAME'] = $username;
    $_SESSION['LAST_ACTIVITY'] = time();
    if ($rememberMe) {
        $_SESSION['REMEMBER_ME'] = true;
    }

    // Log successful login
    $logObject->insertLog($userId, "Login: User \"$username\" logged in. IP: $userIP", 'user');

    // Set success message and redirect
    Feedback::flash('LOGIN', 'LOGIN_SUCCESS');
    header('Location: ' . htmlspecialchars($app_root));
}
