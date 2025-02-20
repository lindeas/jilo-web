<?php

/**
 * Test Session Handler
 * 
 * Provides session handling functionality for PHPUnit tests.
 * This class ensures proper session management during testing.
 */
class TestSessionHandler implements SessionHandlerInterface
{
    private static $initialized = false;
    private $data = [];

    /**
     * Initialize session settings
     */
    public static function init()
    {
        if (!self::$initialized && !headers_sent()) {
            // Clean up any existing session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-3600, '/');
            }

            $_SESSION = array();

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }

            // Set session configuration
            session_name('jilo');

            // Start a new session
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start([
                    'cookie_httponly' => 1,
                    'cookie_secure' => 1,
                    'cookie_samesite' => 'Strict',
                    'gc_maxlifetime' => 1440 // 24 minutes
                ]);
            }

            self::$initialized = true;
        }
    }

    /**
     * Start a fresh session
     */
    public static function startSession()
    {
        // Clean up any existing session first
        self::cleanupSession();

        // Initialize new session
        if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
            session_name('jilo');
            session_start([
                'cookie_httponly' => 1,
                'cookie_secure' => 1,
                'cookie_samesite' => 'Strict',
                'gc_maxlifetime' => 1440
            ]);
            self::$initialized = true;
        }
    }

    /**
     * Clean up the current session
     */
    public static function cleanupSession()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }

        $_SESSION = array();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        self::$initialized = false;
    }

    public function open($path, $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        return $this->data[$id] ?? '';
    }

    public function write($id, $data): bool
    {
        $this->data[$id] = $data;
        return true;
    }

    public function destroy($id): bool
    {
        unset($this->data[$id]);
        return true;
    }

    public function gc($max_lifetime): int|false
    {
        return 0;
    }
}
