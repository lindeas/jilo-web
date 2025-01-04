<?php

class Messages {
    // Message types
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'danger';
    const TYPE_INFO = 'info';
    const TYPE_WARNING = 'warning';

    // Message categories
    const SECURITY = [
        'WHITELIST_ADD_SUCCESS' => [
            'message' => 'IP address successfully added to whitelist.',
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'WHITELIST_ADD_ERROR' => [
            'message' => 'Failed to add IP to whitelist. Please check the IP format.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'WHITELIST_REMOVE_SUCCESS' => [
            'message' => 'IP address successfully removed from whitelist.',
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'WHITELIST_REMOVE_ERROR' => [
            'message' => 'Failed to remove IP from whitelist.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'BLACKLIST_ADD_SUCCESS' => [
            'message' => 'IP address successfully added to blacklist.',
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'BLACKLIST_ADD_ERROR' => [
            'message' => 'Failed to add IP to blacklist. Please check the IP format.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'BLACKLIST_REMOVE_SUCCESS' => [
            'message' => 'IP address successfully removed from blacklist.',
            'type' => self::TYPE_SUCCESS,
            'dismissible' => true
        ],
        'BLACKLIST_REMOVE_ERROR' => [
            'message' => 'Failed to remove IP from blacklist.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'IP_REQUIRED' => [
            'message' => 'IP address is required.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'PERMISSION_DENIED' => [
            'message' => 'You do not have permission to perform this action.',
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'RATE_LIMIT_INFO' => [
            'message' => 'Rate limiting is active. This helps protect against brute force attacks.',
            'type' => self::TYPE_INFO,
            'dismissible' => false
        ]
    ];

    const LOGIN = [
        'LOGIN_FAILED' => [
            'message' => 'Invalid username or password.',
            'type' => self::TYPE_ERROR,
            'dismissible' => true
        ],
        'LOGIN_BLOCKED' => [
            'message' => 'Too many failed attempts. Please try again later.',
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ],
        'IP_BLACKLISTED' => [
            'message' => 'Access denied. Your IP address is blacklisted.',
            'type' => self::TYPE_ERROR,
            'dismissible' => false
        ]
    ];

    /**
     * Get message configuration by key
     */
    public static function get($category, $key) {
        $messages = constant("self::$category");
        return $messages[$key] ?? null;
    }

    /**
     * Render message HTML
     */
    public static function render($category, $key, $customMessage = null) {
        $config = self::get($category, $key);
        if (!$config) return '';

        $message = $customMessage ?? $config['message'];
        $dismissible = $config['dismissible'] ? ' alert-dismissible fade show' : '';
        $dismissButton = $config['dismissible'] ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

        return sprintf(
            '<div class="alert alert-%s%s" role="alert">%s%s</div>',
            $config['type'],
            $dismissible,
            htmlspecialchars($message),
            $dismissButton
        );
    }

    /**
     * Store message in session for display after redirect
     */
    public static function flash($category, $key, $customMessage = null) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'category' => $category,
            'key' => $key,
            'custom_message' => $customMessage
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
