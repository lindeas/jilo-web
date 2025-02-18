<?php

require_once __DIR__ . '/vendor/autoload.php';

class TestCase extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test environment
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
        
        // Include common app files
        require_once dirname(__DIR__, 2) . '/app/includes/config.php';
        require_once dirname(__DIR__, 2) . '/app/includes/functions.php';
        
        // Clean up any existing session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Reset session data
        $_SESSION = [];
        
        // Only start session if headers haven't been sent
        if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function tearDown(): void
    {
        // Clean up session after each test
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
        
        parent::tearDown();
    }

    /**
     * Helper method to start a new session if needed
     */
    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    protected function captureHeaders(): array
    {
        $headers = [];
        $callback = function($header) use (&$headers) {
            $headers[] = $header;
        };

        // Mock header function
        if (!function_exists('header')) {
            eval('function header($header) use ($callback) { $callback($header); }');
        }

        return $headers;
    }
}
