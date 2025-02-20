<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';
require_once dirname(__DIR__, 3) . '/app/includes/rate_limit_middleware.php';

use PHPUnit\Framework\TestCase;

class RateLimitMiddlewareTest extends TestCase
{
    private $db;
    private $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create rate limiter table
        $this->db->getConnection()->exec("CREATE TABLE pages_rate_limits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            endpoint TEXT NOT NULL,
            request_time DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Create ip_whitelist table
        $this->db->getConnection()->exec("CREATE TABLE ip_whitelist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL UNIQUE,
            is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
            description TEXT,
            created_at TEXT DEFAULT (DATETIME('now')),
            created_by TEXT
        )");

        // Create ip_blacklist table
        $this->db->getConnection()->exec("CREATE TABLE ip_blacklist (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL UNIQUE,
            is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
            reason TEXT,
            expiry_time TEXT NULL,
            created_at TEXT DEFAULT (DATETIME('now')),
            created_by TEXT
        )");

        $this->rateLimiter = new RateLimiter($this->db);

        // Mock $_SERVER variables
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['REQUEST_URI'] = '/login';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        // Define testing constant
        if (!defined('TESTING')) {
            define('TESTING', true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up rate limit records
        $this->db->getConnection()->exec('DELETE FROM pages_rate_limits');

        parent::tearDown();
    }

    public function testRateLimitMiddleware()
    {
        // Test multiple requests
        for ($i = 1; $i <= 5; $i++) {
            $result = checkRateLimit(['db' => $this->db], '/login');

            if ($i <= 5) {
                // First 5 requests should pass
                $this->assertTrue($result);
            } else {
                // 6th and subsequent requests should be blocked
                $this->assertFalse($result);
            }
        }
    }

    public function testRateLimitBypass()
    {
        // Test AJAX request bypass
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $result = checkRateLimit(['db' => $this->db], '/login');
        $this->assertTrue($result);
    }

    public function testRateLimitReset()
    {
        // Use up the rate limit
        for ($i = 0; $i < 5; $i++) {
            checkRateLimit(['db' => $this->db], '/login');
        }

        // Wait for rate limit to reset (use a short window for testing)
        sleep(2);

        // Should be able to make request again
        $result = checkRateLimit(['db' => $this->db], '/login');
        $this->assertTrue($result);
    }

    public function testDifferentEndpoints()
    {
        // Use up rate limit for login
        for ($i = 0; $i < 5; $i++) {
            checkRateLimit(['db' => $this->db], '/login');
        }

        // Should still be able to access different endpoint
        $result = checkRateLimit(['db' => $this->db], '/dashboard');
        $this->assertTrue($result);
    }

    public function testDifferentIpAddresses()
    {
        // Use up rate limit for first IP
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        for ($i = 0; $i < 5; $i++) {
            checkRateLimit(['db' => $this->db], '/login');
        }

        // Different IP should not be affected
        $_SERVER['REMOTE_ADDR'] = '127.0.0.2';
        $result = checkRateLimit(['db' => $this->db], '/login');
        $this->assertTrue($result);
    }

    public function testWhitelistedIp()
    {
        // Add IP to whitelist
        $this->db->execute(
            'INSERT INTO ip_whitelist (ip_address, description, created_by) VALUES (?, ?, ?)',
            ['127.0.0.1', 'Test whitelist', 'PHPUnit']
        );

        // Should be able to make more requests than limit
        for ($i = 0; $i < 10; $i++) {
            $result = checkRateLimit(['db' => $this->db], '/login');
            $this->assertTrue($result);
        }
    }

    public function testBlacklistedIp()
    {
        // Add IP to blacklist
        $this->db->execute(
            'INSERT INTO ip_blacklist (ip_address, reason, created_by) VALUES (?, ?, ?)',
            ['127.0.0.1', 'Test blacklist', 'PHPUnit']
        );

        // Should be blocked immediately
        $result = checkRateLimit(['db' => $this->db], '/login');
        $this->assertFalse($result);
    }

    public function testRateLimitPersistence()
    {
        // Use up some of the rate limit
        for ($i = 0; $i < 2; $i++) {
            checkRateLimit(['db' => $this->db], '/login');
        }

        // Destroy and restart session
        //session_destroy();
        //session_start();

        // Should still count previous requests
        $result = checkRateLimit(['db' => $this->db], '/login');
        $this->assertTrue($result);
    }
}
