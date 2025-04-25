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
    require_once '../app/helpers/ip_helper.php';
    $user_IP = getUserIP();

    $action = $_REQUEST['action'] ?? '';

    if ($action === 'verify' && isset($_SESSION['2fa_pending_user_id'])) {
        // Handle 2FA verification
        $code = $_POST['code'] ?? '';
        $pending2FA = Session::get2FAPending();

        if (!$pending2FA) {
            header('Location: ' . htmlspecialchars($app_root) . '?page=login');
            exit();
        }

        require_once '../app/classes/twoFactorAuth.php';
        $twoFactorAuth = new TwoFactorAuthentication($db);

        if ($twoFactorAuth->verify($pending2FA['user_id'], $code)) {
            // Complete login
            handleSuccessfulLogin($pending2FA['user_id'], $pending2FA['username'],
                $pending2FA['remember_me'], $config, $app_root, $logObject, $user_IP);

            // Clean up 2FA session data
            Session::clear2FAPending();

            exit();
        }

        // If we get here (and we have code submitted), verification failed
        if (!empty($code)) {
            Feedback::flash('ERROR', 'DEFAULT', 'Invalid verification code');
        }

        // Get any new feedback messages
        include '../app/helpers/feedback.php';

        // Make userId available to template
        $userId = $pending2FA['user_id'];

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
                    'required' => true
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
            }

            // Attempt login
            $loginResult = $userObject->login($username, $password);

            if (is_array($loginResult)) {
                switch ($loginResult['status']) {
                    case 'requires_2fa':
                        // Store pending 2FA info
                        Session::store2FAPending($loginResult['user_id'], $loginResult['username'],
                            isset($formData['remember_me']));

                        // Redirect to 2FA verification
                        header('Location: ?page=login&action=verify');
                        exit();

                    case 'success':
                        // Complete login
                        handleSuccessfulLogin($loginResult['user_id'], $loginResult['username'],
                            isset($formData['remember_me']), $config, $app_root, $logObject, $user_IP);
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
                $userId = $userObject->getUserId($username)[0]['id'] ?? 0;
                $logObject->insertLog($userId, "Login: Failed login attempt for user \"$username\". IP: $user_IP. Reason: {$e->getMessage()}", 'user');
                $rateLimiter->attempt($username, $user_IP);
            }
        }
    }
} catch (Exception $e) {
    Feedback::flash('ERROR', 'DEFAULT');
}

// Show configured login message if any
if (!empty($config['login_message'])) {
    echo Feedback::render('NOTICE', 'DEFAULT', $config['login_message'], false, false, false);
}

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/form-login.php';

/**
 * Handle successful login by setting up session and cookies
 */
function handleSuccessfulLogin($userId, $username, $rememberMe, $config, $app_root, $logObject, $userIP) {
    // Create authenticated session
    Session::createAuthSession($userId, $username, $rememberMe, $config);

    // Log successful login
    $logObject->insertLog($userId, "Login: User \"$username\" logged in. IP: $userIP", 'user');

    // Set success message
    Feedback::flash('LOGIN', 'LOGIN_SUCCESS');
    header('Location: ' . htmlspecialchars($app_root));

    // After successful login, redirect to original page if provided in URL param or POST
    $redirect = $app_root;
    $candidate = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
    $trimmed = trim($candidate, '/?');
    if (
        (strpos($candidate, '/') === 0 || strpos($candidate, '?') === 0)
        && !in_array($trimmed, INVALID_REDIRECT_PAGES, true)
    ) {
        $redirect = $candidate;
    }
    header('Location: ' . htmlspecialchars($redirect));
    exit();
}
