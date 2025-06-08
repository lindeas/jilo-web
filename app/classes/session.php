<?php

/**
 * Session Class
 *
 * Core session management functionality for the application
 */
class Session {
    private static $initialized = false;
    private static $sessionName = ''; // Will be set from config, if not we'll have a random session name

    /**
     * Generate a random session name
     */
    private static function generateRandomSessionName(): string {
        return 'sess_' . bin2hex(random_bytes(8)); // 16-character random string
    }
    private static $sessionOptions = [
        'cookie_httponly' => 1,
        'cookie_secure' => 1,
        'cookie_samesite' => 'Strict',
        'gc_maxlifetime' => 7200 // 2 hours
    ];

    /**
     * Initialize session configuration
     */
    private static function initialize() {
        if (self::$initialized) {
            return;
        }

        global $config;

        // Load session settings from config if available
        self::$sessionName = self::generateRandomSessionName();

        if (isset($config['session']) && is_array($config['session'])) {
            if (!empty($config['session']['name'])) {
                self::$sessionName = $config['session']['name'];
            }

            if (isset($config['session']['lifetime'])) {
                self::$sessionOptions['gc_maxlifetime'] = (int)$config['session']['lifetime'];
            }
        }

        self::$initialized = true;
    }

    /**
     * Start or resume a session with secure options
     */
    public static function startSession() {
        self::initialize();

        if (session_status() === PHP_SESSION_NONE) {
            session_name(self::$sessionName);
            session_start(self::$sessionOptions);
        } elseif (session_status() === PHP_SESSION_ACTIVE && session_name() !== self::$sessionName) {
            // If session is active but with wrong name, destroy and restart it
            session_destroy();
            session_name(self::$sessionName);
            session_start(self::$sessionOptions);
        }
    }

    /**
     * Destroy current session and clean up
     */
    public static function destroySession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Get current username if set
     */
    public static function getUsername() {
        return isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : null;
    }

    /**
     * Get current user ID if set
     */
    public static function getUserId() {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Check if current session is valid
     */
    public static function isValidSession() {
        // Check required session variables
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
            return false;
        }

        // Check session timeout
        $session_timeout = isset($_SESSION['REMEMBER_ME']) ? (30 * 24 * 60 * 60) : 7200; // 30 days or 2 hours
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_timeout)) {
            return false;
        }

        // Update last activity time
        $_SESSION['LAST_ACTIVITY'] = time();

        // Regenerate session ID periodically (every 30 minutes)
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 1800) {
            // Regenerate session ID and update creation time
            if (!headers_sent() && session_status() === PHP_SESSION_ACTIVE) {
                $oldData = $_SESSION;
                session_regenerate_id(true);
                $_SESSION = $oldData;
                $_SESSION['CREATED'] = time();
            }
        }

        return true;
    }

    /**
     * Set remember me option for extended session
     */
    public static function setRememberMe($value = true) {
        $_SESSION['REMEMBER_ME'] = $value;
    }

    /**
     * Clear session data and cookies
     */
    public static function cleanup($config) {
        self::destroySession();

        // Clear cookies if headers not sent
        if (!headers_sent()) {
            setcookie('username', '', [
                'expires' => time() - 3600,
                'path' => $config['folder'],
                'domain' => $config['domain'],
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

        // Start fresh session
        self::startSession();

        // Reset session timeout flag
        unset($_SESSION['session_timeout_shown']);
    }

    /**
     * Create a new authenticated session for a user
     */
    public static function createAuthSession($userId, $username, $rememberMe, $config) {
        // Set cookie lifetime based on remember me
        $cookieLifetime = $rememberMe ? time() + (30 * 24 * 60 * 60) : 0;

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Set cookie with secure options
        setcookie('username', $username, [
            'expires' => $cookieLifetime,
            'path' => $config['folder'],
            'domain' => $config['domain'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['LAST_ACTIVITY'] = time();
        if ($rememberMe) {
            self::setRememberMe(true);
        }
    }

    /**
     * Store 2FA pending information in session
     */
    public static function store2FAPending($userId, $username, $rememberMe = false) {
        $_SESSION['2fa_pending_user_id'] = $userId;
        $_SESSION['2fa_pending_username'] = $username;
        if ($rememberMe) {
            $_SESSION['2fa_pending_remember'] = true;
        }
    }

    /**
     * Clear 2FA pending information from session
     */
    public static function clear2FAPending() {
        unset($_SESSION['2fa_pending_user_id']);
        unset($_SESSION['2fa_pending_username']);
        unset($_SESSION['2fa_pending_remember']);
    }

    /**
     * Get 2FA pending information
     */
    public static function get2FAPending() {
        if (!isset($_SESSION['2fa_pending_user_id']) || !isset($_SESSION['2fa_pending_username'])) {
            return null;
        }

        return [
            'user_id' => $_SESSION['2fa_pending_user_id'],
            'username' => $_SESSION['2fa_pending_username'],
            'remember_me' => isset($_SESSION['2fa_pending_remember'])
        ];
    }
}
