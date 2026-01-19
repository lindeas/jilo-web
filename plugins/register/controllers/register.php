<?php

/**
 * User Registration API Controller
 *
 * Provides RESTful endpoints for user registration.
 * Follows the API pattern used by other plugins.
 */

namespace Plugins\Register\Controllers;

use App\App;
use App\Helpers\Theme;
use Exception;
use PDO;

require_once APP_PATH . 'classes/feedback.php';
require_once APP_PATH . 'classes/user.php';
require_once APP_PATH . 'classes/validator.php';
require_once APP_PATH . 'helpers/security.php';
require_once APP_PATH . 'helpers/theme.php';
require_once APP_PATH . 'includes/rate_limit_middleware.php';
require_once PLUGIN_REGISTER_PATH . 'models/register.php';

class RegisterController
{
    /** @var \Database|\PDO|null */
    private $db;
    private array $config;
    private string $appRoot;
    private $logger;

    public function __construct()
    {
        $this->db = App::db();
        $this->config = App::config();
        $this->appRoot = App::get('app_root') ?? '/';
        $this->logger = App::get('logObject');
    }

    public function handle(string $action, array $context = []): bool
    {
        $validSession = (bool)($context['valid_session'] ?? false);
        $app_root = $context['app_root'] ?? $this->appRoot;

        if (!$this->db) {
            \Feedback::flash('ERROR', 'DEFAULT', 'Registration service unavailable. Please try again later.');
            $this->renderForm($validSession, $app_root, ['registrationEnabled' => false]);
            return true;
        }

        if (!$this->isRegistrationEnabled()) {
            \Feedback::flash('NOTICE', 'DEFAULT', 'Registration is currently disabled.');
            $this->renderForm($validSession, $app_root, ['registrationEnabled' => false]);
            return true;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSubmission($validSession, $app_root);
            return true;
        }

        $this->renderForm($validSession, $app_root);
        return true;
    }

    private function isRegistrationEnabled(): bool
    {
        return (bool)($this->config['registration_enabled'] ?? false);
    }

    private function handleSubmission(bool $validSession, string $app_root): void
    {
        checkRateLimit($this->db, 'register');

        $security = \SecurityHelper::getInstance();
        $formData = $security->sanitizeArray(
            $_POST,
            ['username', 'password', 'confirm_password', 'csrf_token', 'terms']
        );

        if (!$security->verifyCsrfToken($formData['csrf_token'] ?? '')) {
            \Feedback::flash('ERROR', 'DEFAULT', 'Invalid security token. Please try again.');
            $this->renderForm($validSession, $app_root, [
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
            $this->renderForm($validSession, $app_root, [
                'values' => ['username' => $formData['username'] ?? ''],
            ]);
            return;
        }

        $username = trim($formData['username']);
        $password = $formData['password'];

        try {
            $register = new \Register($this->db);
            $result = $register->register($username, $password);

            if ($result === true) {
                $this->logSuccessfulRegistration($username);
                \Feedback::flash('NOTICE', 'DEFAULT', 'Registration successful. You can log in now.');
                header('Location: ' . $app_root . '?page=login');
                exit;
            }

            \Feedback::flash('ERROR', 'DEFAULT', 'Registration failed: ' . $result);
            $this->renderForm($validSession, $app_root, [
                'values' => ['username' => $username],
            ]);
        } catch (Exception $e) {
            \Feedback::flash('ERROR', 'DEFAULT', 'Registration failed: ' . $e->getMessage());
            $this->renderForm($validSession, $app_root, [
                'values' => ['username' => $username],
            ]);
        }
    }

    private function logSuccessfulRegistration(string $username): void
    {
        if (!$this->logger) {
            return;
        }

        try {
            $userModel = new \User($this->db);
            $userRecord = $userModel->getUserId($username);
            $userId = $userRecord[0]['id'] ?? null;
            $userIP = $_SERVER['REMOTE_ADDR'] ?? '';

            $this->logger->log(
                'info',
                sprintf('Registration: New user "%s" registered successfully. IP: %s', $username, $userIP),
                ['user_id' => $userId, 'scope' => 'user']
            );
        } catch (Exception $e) {
            app_log('warning', 'RegisterController logging failed: ' . $e->getMessage(), ['scope' => 'plugin']);
        }
    }

    private function renderForm(bool $validSession, string $app_root, array $data = []): void
    {
        $formValues = $data['values'] ?? ['username' => ''];
        $registrationEnabled = $data['registrationEnabled'] ?? true;

        Theme::include('page-header');
        Theme::include('page-menu');
        if ($validSession) {
            Theme::include('page-sidebar');
        }

        include APP_PATH . 'helpers/feedback.php';

        $app_root_value = $app_root; // align variable name for template include
        $app_root = $app_root_value;
        $values = $formValues;

        include PLUGIN_REGISTER_PATH . 'views/form-register.php';

        Theme::include('page-footer');
    }
}
