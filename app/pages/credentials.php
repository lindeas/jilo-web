<?php

/**
 * User credentials management
 *
 * This page ("credentials") handles all credential-related actions including:
 * - Two-factor authentication (2FA) setup, verification, and management
 * - Password changes and resets
 *
 * Actions handled:
 * - `setup`: Initial 2FA setup and verification
 * - `verify`: Verify 2FA codes during login
 * - `disable`: Disable 2FA
 * - `password`: Change password
 */

$action = $_REQUEST['action'] ?? '';
$item = $_REQUEST['item'] ?? '';

// if a form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    require_once '../app/helpers/security.php';
    $security = SecurityHelper::getInstance();
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        Feedback::flash('ERROR', 'DEFAULT', 'Invalid security token. Please try again.');
        header("Location: $app_root?page=credentials");
        exit();
    }

    // Apply rate limiting
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($dbWeb, 'credentials', $user_id);

    switch ($item) {
        case '2fa':
            require_once '../app/helpers/2fa.php';

            switch ($action) {
                case 'setup':
                    // Validate the setup code
                    $code = $_POST['code'] ?? '';
                    $secret = $_POST['secret'] ?? '';

                    if ($userObject->enableTwoFactor($user_id, $secret, $code)) {
                        Feedback::flash('NOTICE', 'DEFAULT', 'Two-factor authentication has been enabled successfully.');
                        header("Location: $app_root?page=credentials");
                        exit();
                    } else {
                        Feedback::flash('ERROR', 'DEFAULT', 'Invalid verification code. Please try again.');
                        header("Location: $app_root?page=credentials&action=edit");
                        exit();
                    }
                    break;

                case 'verify':
                    $code = $_POST['code'] ?? '';
                    if ($userObject->verifyTwoFactor($user_id, $code)) {
                        $_SESSION['2fa_verified'] = true;
                        header("Location: $app_root?page=dashboard");
                        exit();
                    } else {
                        Feedback::flash('ERROR', 'DEFAULT', 'Invalid verification code. Please try again.');
                        header("Location: $app_root?page=credentials&action=verify");
                        exit();
                    }
                    break;

                case 'disable':
                    if ($userObject->disableTwoFactor($user_id)) {
                        Feedback::flash('NOTICE', 'DEFAULT', 'Two-factor authentication has been disabled.');
                    } else {
                        Feedback::flash('ERROR', 'DEFAULT', 'Failed to disable two-factor authentication.');
                    }
                    header("Location: $app_root?page=credentials");
                    exit();
                    break;
            }
            break;

        case 'password':
            require_once '../app/classes/validator.php';

            $validator = new Validator($_POST);
            $rules = [
                'current_password' => [
                    'required' => true,
                    'min' => 8
                ],
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
                Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
                header("Location: $app_root?page=credentials");
                exit();
            }

            if ($userObject->changePassword($user_id, $_POST['current_password'], $_POST['new_password'])) {
                Feedback::flash('NOTICE', 'DEFAULT', 'Password has been changed successfully.');
            } else {
                Feedback::flash('ERROR', 'DEFAULT', 'Failed to change password. Please verify your current password.');
            }
            header("Location: $app_root?page=credentials");
            exit();
            break;
    }

// no form submitted, show the templates
} else {
    // Get user timezone for templates
    $userTimezone = !empty($userDetails[0]['timezone']) ? $userDetails[0]['timezone'] : 'UTC';

    // Generate CSRF token if not exists
    require_once '../app/helpers/security.php';
    $security = SecurityHelper::getInstance();
    $security->generateCsrfToken();

    // Get 2FA status for the template
    $has2fa = $userObject->isTwoFactorEnabled($user_id);

    switch ($action) {
        case 'edit':
            if (!$has2fa) {
                require_once '../app/helpers/2fa.php';
                $secret = $userObject->generateTwoFactorSecret();
                $qrCode = $userObject->generateTwoFactorQR($user_id, $secret);
                $backupCodes = $userObject->generateBackupCodes();
            }
            // Get any new feedback messages
            include '../app/helpers/feedback.php';

            // Load the 2FA setup template
            include '../app/templates/credentials-2fa-setup.php';
            break;

        case 'verify':
            // Get any new feedback messages
            include '../app/helpers/feedback.php';

            // Load the 2FA verification template
            include '../app/templates/credentials-2fa-verify.php';
            break;

        default:
            // Get any new feedback messages
            include '../app/helpers/feedback.php';

            // Load the combined management template
            include '../app/templates/credentials-manage.php';
    }
}
