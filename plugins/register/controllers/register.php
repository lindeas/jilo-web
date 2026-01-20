<?php

/**
 * Register Plugin Controller
 *
 * Procedural handler used by the callable dispatcher.
 */

require_once APP_PATH . 'classes/feedback.php';
require_once APP_PATH . 'classes/user.php';
require_once APP_PATH . 'classes/validator.php';
require_once APP_PATH . 'helpers/security.php';
require_once APP_PATH . 'helpers/theme.php';
require_once APP_PATH . 'includes/rate_limit_middleware.php';
require_once PLUGIN_REGISTER_PATH . 'models/register.php';

function register_plugin_handle_register(string $action, array $context = []): bool {
    $validSession = (bool)($context['valid_session'] ?? false);
    $app_root = $context['app_root'] ?? (\App\App::get('app_root') ?? '/');
    $config = $context['config'] ?? \App\App::config();
    $db = $context['db'] ?? \App\App::db();
    $logger = $context['logger'] ?? \App\App::get('logger');

    if (!$db) {
        \Feedback::flash('ERROR', 'DEFAULT', 'Registration service unavailable. Please try again later.');
        register_plugin_render_form($validSession, $app_root, ['registrationEnabled' => false]);
        return true;
    }

    if (!(bool)($config['registration_enabled'] ?? false)) {
        \Feedback::flash('NOTICE', 'DEFAULT', 'Registration is currently disabled.');
        register_plugin_render_form($validSession, $app_root, ['registrationEnabled' => false]);
        return true;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        register_plugin_handle_submission($validSession, $app_root, $db, $logger);
        return true;
    }

    register_plugin_render_form($validSession, $app_root);
    return true;
}

function register_plugin_handle_submission(bool $validSession, string $app_root, $db, $logger = null): void {
    checkRateLimit($db, 'register');

    $security = \SecurityHelper::getInstance();
    $formData = $security->sanitizeArray(
        $_POST,
        ['username', 'password', 'confirm_password', 'csrf_token', 'terms']
    );

    if (!$security->verifyCsrfToken($formData['csrf_token'] ?? '')) {
        \Feedback::flash('ERROR', 'DEFAULT', 'Invalid security token. Please try again.');
        register_plugin_render_form($validSession, $app_root, [
            'values' => ['username' => $formData['username'] ?? ''],
        ]);
        return;
    }

    $validator = new \Validator($formData);
    $rules = [
        'username' => [
            'required' => true,
            'min' => 3,
            'max' => 20,
        ],
        'password' => [
            'required' => true,
            'min' => 8,
            'max' => 255,
        ],
        'confirm_password' => [
            'required' => true,
            'matches' => 'password',
        ],
        'terms' => [
            'required' => true,
            'accepted' => true,
        ],
    ];

    if (!$validator->validate($rules)) {
        \Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
        register_plugin_render_form($validSession, $app_root, [
            'values' => ['username' => $formData['username'] ?? ''],
        ]);
        return;
    }

    $username = trim($formData['username']);
    $password = $formData['password'];

    $pdo = $db instanceof \PDO ? $db : $db->getConnection();

    try {
        $register = new \Register($pdo);
        $result = $register->register($username, $password);

        if ($result === true) {
            register_plugin_log_success($username, $db, $logger);
            \Feedback::flash('NOTICE', 'DEFAULT', 'Registration successful. You can log in now.');
            header('Location: ' . $app_root . '?page=login');
            exit;
        }

        \Feedback::flash('ERROR', 'DEFAULT', 'Registration failed: ' . $result);
        register_plugin_render_form($validSession, $app_root, [
            'values' => ['username' => $username],
        ]);
    } catch (Exception $e) {
        \Feedback::flash('ERROR', 'DEFAULT', 'Registration failed: ' . $e->getMessage());
        register_plugin_render_form($validSession, $app_root, [
            'values' => ['username' => $username],
        ]);
    }
}

function register_plugin_log_success(string $username, $db, $logger = null): void {
    if (!$logger) {
        return;
    }

    try {
        $userModel = new \User($db);
        $userRecord = $userModel->getUserId($username);
        $userId = $userRecord[0]['id'] ?? null;
        $userIP = $_SERVER['REMOTE_ADDR'] ?? '';

        $logger->log(
            'info',
            sprintf('Registration: New user "%s" registered successfully. IP: %s', $username, $userIP),
            ['user_id' => $userId, 'scope' => 'user']
        );
    } catch (Exception $e) {
        app_log('warning', 'Register plugin logging failed: ' . $e->getMessage(), ['scope' => 'plugin']);
    }
}

function register_plugin_render_form(bool $validSession, string $app_root, array $data = []): void {
    $formValues = $data['values'] ?? ['username' => ''];
    $registrationEnabled = $data['registrationEnabled'] ?? true;

    // Get any new feedback messages
    include_once APP_PATH . 'helpers/feedback.php';

    $csrf_token = \SecurityHelper::getInstance()->generateCsrfToken();
    $registerCsrfToken = $csrf_token;

    // Load the view
    include PLUGIN_REGISTER_PATH . 'views/form-register.php';
}
