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

        // Get session name from config or generate a random one
        self::$sessionName = $config['session']['name'] ?? self::generateRandomSessionName();

        // Set session name before starting the session
        session_name(self::$sessionName);

        // Set session cookie parameters
        $thisPath = $config['folder'] ?? '/';
        $thisDomain = $config['domain'] ?? '';
        $isSecure = isset($_SERVER['HTTPS']);

        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (browser session)
            'path' => $thisPath,
            'domain' => $thisDomain,
            'secure' => $isSecure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        self::$initialized = true;
    }

    /**
     * Get session name from config or generate a random one
     */
    private static function getSessionNameFromConfig($config) {
        if (isset($config['session']['name']) && !empty($config['session']['name'])) {
            return $config['session']['name'];
        }
        return self::generateRandomSessionName();
    }

    /**
     * Start or resume a session with secure options
     */
    public static function startSession() {
        self::initialize();

        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                session_start(self::$sessionOptions);
            }
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
     *
     * @param bool $strict If true, will return false for new/unauthenticated sessions
     * @return bool True if session is valid, false otherwise
     */
    public static function isValidSession($strict = true) {
        // If session is not started or empty, it's not valid
        if (session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION)) {
            return false;
        }

        // In non-strict mode, consider empty session as valid (for login/logout)
        if (!$strict && !isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
            return true;
        }

        // In strict mode, require user_id and username
        if ($strict && (!isset($_SESSION['user_id']) || !isset($_SESSION['username']))) {
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
        // Ensure session is started
        self::startSession();

        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['LAST_ACTIVITY'] = time();
        $_SESSION['REMEMBER_ME'] = $rememberMe;

        // Set cookie lifetime based on remember me
        $cookieLifetime = $rememberMe ? time() + (30 * 24 * 60 * 60) : 0;

        // Update session cookie with remember me setting
        if (!headers_sent()) {
            setcookie(
                session_name(),
                session_id(),
                [
                    'expires' => $cookieLifetime,
                    'path' => $config['folder'] ?? '/',
                    'domain' => $config['domain'] ?? '',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );

            // Set username cookie
            setcookie('username', $username, [
                'expires' => $cookieLifetime,
                'path' => $config['folder'] ?? '/',
                'domain' => $config['domain'] ?? '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }

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
