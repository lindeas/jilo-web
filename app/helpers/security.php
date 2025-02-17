<?php

/**
 * Security Helper
 * 
 * Security helper, to be used with all the forms in the app.
 * Implements singleton pattern for consistent state management.
 */
class SecurityHelper {
    private static $instance = null;
    private $session;

    private function __construct() {
        // Don't start a new session, just reference the existing one
        $this->session = &$_SESSION;
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new SecurityHelper();
        }
        return self::$instance;
    }

    // Generate CSRF token
    public function generateCsrfToken() {
        if (empty($this->session['csrf_token'])) {
            $this->session['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $this->session['csrf_token'];
    }

    // Verify CSRF token
    public function verifyCsrfToken($token) {
        if (empty($this->session['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($this->session['csrf_token'], $token);
    }

    // Sanitize string input
    public function sanitizeString($input) {
        if (is_string($input)) {
            return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        }
        return '';
    }

    // Validate email
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validate integer
    public function validateInt($input) {
        return filter_var($input, FILTER_VALIDATE_INT) !== false;
    }

    // Validate URL
    public function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    // Sanitize array of inputs
    public function sanitizeArray($array, $allowedKeys = []) {
        $sanitized = [];
        foreach ($array as $key => $value) {
            if (empty($allowedKeys) || in_array($key, $allowedKeys)) {
                if (is_array($value)) {
                    $sanitized[$key] = $this->sanitizeArray($value);
                } else {
                    $sanitized[$key] = $this->sanitizeString($value);
                }
            }
        }
        return $sanitized;
    }

    // Validate form data based on rules
    public function validateFormData($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) && $rule['required']) {
                $errors[$field] = "Field is required";
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];
                switch ($rule['type']) {
                    case 'email':
                        if (!$this->validateEmail($value)) {
                            $errors[$field] = "Invalid email format";
                        }
                        break;
                    case 'integer':
                        if (!$this->validateInt($value)) {
                            $errors[$field] = "Must be a valid integer";
                        }
                        break;
                    case 'url':
                        if (!$this->validateUrl($value)) {
                            $errors[$field] = "Invalid URL format";
                        }
                        break;
                    case 'string':
                        if (isset($rule['min']) && strlen($value) < $rule['min']) {
                            $errors[$field] = "Minimum length is {$rule['min']} characters";
                        }
                        if (isset($rule['max']) && strlen($value) > $rule['max']) {
                            $errors[$field] = "Maximum length is {$rule['max']} characters";
                        }
                        break;
                }
            }
        }
        return $errors;
    }
}
