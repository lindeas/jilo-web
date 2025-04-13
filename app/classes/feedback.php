<?php

class Feedback {
    // Feedback types
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'danger';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';

    // Default feedback message configurations
    const NOTICE = [
        'DEFAULT' => [
            'type' => self::TYPE_INFO,
            'dismissible' => true
        ]
    ];

    const ERROR = [
        'DEFAULT' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ]
    ];

    const LOGIN = [
        'LOGIN_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'LOGIN_FAILED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'LOGOUT_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'SESSION_TIMEOUT' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'IP_BLACKLISTED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'IP_NOT_WHITELISTED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'TOO_MANY_ATTEMPTS' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ]
    ];

    const SECURITY = [
        'WHITELIST_ADD_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'WHITELIST_ADD_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'WHITELIST_REMOVE_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'WHITELIST_REMOVE_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'BLACKLIST_ADD_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'BLACKLIST_ADD_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'BLACKLIST_REMOVE_SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'BLACKLIST_REMOVE_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'RATE_LIMIT_INFO' => [
            'type' => self::TYPE_INFO,
            'dismissible' => false
        ],
        'PERMISSION_DENIED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'IP_REQUIRED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ]
    ];

    const REGISTER = [
        'SUCCESS' => [
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'FAILED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'DISABLED' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
    ];

    const SYSTEM = [
        'DB_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'DB_CONNECT_ERROR' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'DB_UNKNOWN_TYPE' => [
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
    ];

    private static $strings = null;

    /**
     * Get feedback message strings
     */
    private static function getStrings() {
        if (self::$strings === null) {
            self::$strings = require __DIR__ . '/../includes/strings.php';
        }
        return self::$strings;
    }

    /**
     * Get feedback message configuration by key
     */
    public static function get($category, $key) {
        $config = constant("self::$category")[$key] ?? null;
        if (!$config) return null;

        $strings = self::getStrings();
        $message = $strings[$category][$key] ?? '';

        return array_merge($config, ['message' => $message]);
    }

    /**
     * Render feedback message HTML
     */
    // Usage: echo Feedback::render('LOGIN', 'LOGIN_SUCCESS', 'custom message [or null]', true [for dismissible; or null], true [for small; or omit]);
    public static function render($category, $key, $customMessage = null, $dismissible = null, $small = false, $sanitize = true) {
        $config = self::get($category, $key);
        if (!$config) return '';

        $message = $customMessage ?? $config['message'];
        $isDismissible = $dismissible ?? $config['dismissible'] ?? false;
        $dismissClass = $isDismissible ? ' alert-dismissible fade show' : '';
        $dismissButton = $isDismissible ? '<button type="button" class="btn-close' . ($small ? ' btn-close-sm' : '') . '" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
        $smallClass = $small ? ' alert-sm' : '';

        return sprintf(
            '<div class="alert alert-%s%s%s" role="alert">%s%s</div>',
            $config['type'],
            $dismissClass,
            $smallClass,
            $sanitize ? htmlspecialchars($message) : $message,
            $dismissButton
        );
    }

    /**
     * Get feedback message data for JavaScript
     */
    public static function getMessageData($category, $key, $customMessage = null, $dismissible = null, $small = false) {
        $config = self::get($category, $key);
        if (!$config) return null;

        return [
            'type' => $config['type'],
            'message' => $customMessage ?? $config['message'],
            'dismissible' => $dismissible ?? $config['dismissible'] ?? false,
            'small' => $small
        ];
    }

    /**
     * Store feedback message in session for display after redirect
     */
    // Usage: Feedback::flash('LOGIN', 'LOGIN_SUCCESS', 'custom message [or null]', true [for dismissible; or null], true [for small; or omit]);
    public static function flash($category, $key, $customMessage = null, $dismissible = null, $small = false) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        // Get the feedback message configuration
        $config = self::get($category, $key);
        $isDismissible = $dismissible ?? $config['dismissible'] ?? false;

        $_SESSION['flash_messages'][] = [
            'category' => $category,
            'key' => $key,
            'custom_message' => $customMessage,
            'dismissible' => $isDismissible,
            'small' => $small
        ];
    }

    /**
     * Get and clear all flash feedback messages
     */
    public static function getFlash() {
        $system_messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $system_messages;
    }
}
