<?php

class Messages {
    // Message types
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'danger';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';

    // Default message configurations
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

    private static $strings = null;

    /**
     * Get message strings
     */
    private static function getStrings() {
        if (self::$strings === null) {
            self::$strings = require __DIR__ . '/../includes/messages-strings.php';
        }
        return self::$strings;
    }

    /**
     * Get message configuration by key
     */
    public static function get($category, $key) {
        $config = constant("self::$category")[$key] ?? null;
        if (!$config) return null;

        $strings = self::getStrings();
        $message = $strings[$category][$key] ?? '';

        return array_merge($config, ['message' => $message]);
    }

    /**
     * Render message HTML
     */
    public static function render($category, $key, $customMessage = null, $dismissible = null) {
        $config = self::get($category, $key);
        if (!$config) return '';

        $message = $customMessage ?? $config['message'];
        $isDismissible = $dismissible ?? $config['dismissible'];
        $dismissClass = $isDismissible ? ' alert-dismissible fade show' : '';
        $dismissButton = $isDismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

        return sprintf(
            '<div class="alert alert-%s%s" role="alert">%s%s</div>',
            $config['type'],
            $dismissClass,
            htmlspecialchars($message),
            $dismissButton
        );
    }

    /**
     * Store message in session for display after redirect
     */
    public static function flash($category, $key, $customMessage = null, $dismissible = null) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'category' => $category,
            'key' => $key,
            'custom_message' => $customMessage,
            'dismissible' => $dismissible
        ];
    }

    /**
     * Get and clear all flash messages
     */
    public static function getFlash() {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }
}
