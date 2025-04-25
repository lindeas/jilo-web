<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';
require_once dirname(__DIR__, 3) . '/app/classes/log.php';
require_once dirname(__DIR__, 3) . '/app/includes/rate_limit_middleware.php';

use PHPUnit\Framework\TestCase;

class RateLimitMiddlewareTest extends TestCase
{
    private $db;
    private $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Set global IP for rate limiting
        global $user_IP;
        $user_IP = '8.8.8.8';

        // Prepare DB for Github CI
        $host = defined('CI_DB_HOST') ? CI_DB_HOST : '127.0.0.1';
        $password = defined('CI_DB_PASSWORD') ? CI_DB_PASSWORD : '';

        // Set up test database
        $this->db = new Database([
            'type' => 'mariadb',
            'host' => $host,
            'port' => '3306',
            'dbname' => 'totalmeet_test',
            'user' => 'test_totalmeet',
            'password' => $password
        ]);

        // Create rate limiter instance
        $this->rateLimiter = new RateLimiter($this->db);

        // Drop tables if they exist
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS security_rate_auth");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS security_rate_page");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS security_ip_blacklist");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS security_ip_whitelist");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS log");

        // Create required tables with correct names from RateLimiter class
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS security_rate_auth (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                username VARCHAR(255) NOT NULL,
                attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_username (ip_address, username)
            )
        ");

        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS security_rate_page (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                endpoint VARCHAR(255) NOT NULL,
                request_time DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_endpoint (ip_address, endpoint),
                INDEX idx_request_time (request_time)
            )
        ");

        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS security_ip_blacklist (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                is_network BOOLEAN DEFAULT FALSE,
                reason VARCHAR(255),
                expiry_time TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_by VARCHAR(255),
                UNIQUE KEY unique_ip (ip_address)
            )
        ");

        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS security_ip_whitelist (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                is_network BOOLEAN DEFAULT FALSE,
                description VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_by VARCHAR(255),
                UNIQUE KEY unique_ip (ip_address)
            )
        ");

        // Create log table
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS log (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                scope VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Mock $_SERVER['REMOTE_ADDR'] with a non-whitelisted IP
        $_SERVER['REMOTE_ADDR'] = '8.8.8.8';

        // Define PHPUNIT_RUNNING constant
        if (!defined('PHPUNIT_RUNNING')) {
            define('PHPUNIT_RUNNING', true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up all rate limit records
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");
        $this->db->getConnection()->exec("TRUNCATE TABLE security_ip_blacklist");
        $this->db->getConnection()->exec("TRUNCATE TABLE security_ip_whitelist");
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_auth");
        $this->db->getConnection()->exec("TRUNCATE TABLE log");
        parent::tearDown();
    }

    public function testRateLimitMiddleware()
    {
        // Clean any existing rate limit records
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");

        // Make 60 requests to reach the limit
        for ($i = 0; $i < 60; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result, "Request $i should be allowed");

            // Verify request was recorded
            $stmt = $this->db->getConnection()->prepare("
                SELECT COUNT(*) as count
                FROM security_rate_page
                WHERE ip_address = ?
                AND endpoint = ?
                AND request_time >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ");
            $stmt->execute(['8.8.8.8', '/login']);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $this->assertEquals($i + 1, $count, "Expected " . ($i + 1) . " requests to be recorded, got {$count}");
        }

        // The 61st request should be blocked
        $result = checkRateLimit($this->db, '/login');
        $this->assertFalse($result, "Request should be blocked after 60 requests");
    }

    public function testRateLimitBypass()
    {
        // Clean any existing rate limit records and lists
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");
        $this->db->getConnection()->exec("TRUNCATE TABLE security_ip_whitelist");
        $this->db->getConnection()->exec("TRUNCATE TABLE security_ip_blacklist");

        // Add IP to whitelist and verify it was added
        $stmt = $this->db->getConnection()->prepare("INSERT INTO security_ip_whitelist (ip_address, is_network, description, created_by) VALUES (?, 0, ?, 'PHPUnit')");
        $stmt->execute(['8.8.8.8', 'Test whitelist']);

        // Verify IP is in whitelist
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM security_ip_whitelist WHERE ip_address = ?");
        $stmt->execute(['8.8.8.8']);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        $this->assertEquals(1, $count, "IP should be in whitelist");

        // Should be able to make more requests than limit
        for ($i = 0; $i < 100; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result, "Request $i should be allowed for whitelisted IP");
        }
    }

    public function testRateLimitReset()
    {
        // Clean any existing rate limit records
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");

        // Make some requests
        for ($i = 0; $i < 15; $i++) {
            $result = checkRateLimit($this->db, '/login');
        }

        // Manually expire old requests
        $this->db->getConnection()->exec("
            DELETE FROM security_rate_page
            WHERE request_time < DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ");

        // Should be able to make requests again
        $result = checkRateLimit($this->db, '/login');
        $this->assertTrue($result);
    }

    public function testDifferentEndpoints()
    {
        // Clean any existing rate limit records
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");

        // Make requests to login endpoint (default limit: 60)
        for ($i = 0; $i < 30; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result, "Request $i to /login should be allowed");
        }

        // Clean up between endpoint tests
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");

        // Make requests to register endpoint (limit: 5)
        for ($i = 0; $i < 5; $i++) {
            $result = checkRateLimit($this->db, '/register');
            $this->assertTrue($result, "Request $i to /register should be allowed");
        }

        // The 6th request to register should be blocked
        $result = checkRateLimit($this->db, '/register');
        $this->assertFalse($result, "Request should be blocked after 5 requests to /register");
    }

    public function testDifferentIpAddresses()
    {
        // Make requests from first IP
        for ($i = 0; $i < 30; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result);
        }

        // Change IP
        $_SERVER['REMOTE_ADDR'] = '8.8.4.4';

        // Should be able to make requests from different IP
        for ($i = 0; $i < 30; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result);
        }
    }

    public function testWhitelistedIp()
    {
        // Add IP to whitelist
        $this->rateLimiter->addToWhitelist('8.8.8.8', false, 'Test whitelist', 'PHPUnit');

        // Should be able to make more requests than limit
        for ($i = 0; $i < 50; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result);
        }
    }

    public function testBlacklistedIp()
    {
        // Add IP to blacklist and verify it was added
        $this->db->getConnection()->exec("INSERT INTO security_ip_blacklist (ip_address, is_network, reason, created_by) VALUES ('8.8.8.8', 0, 'Test blacklist', 'system')");

        // Request should be blocked
        $result = checkRateLimit($this->db, '/login');
        $this->assertFalse($result, "Blacklisted IP should be blocked");
    }

    public function testRateLimitPersistence()
    {
        // Clean any existing rate limit records
        $this->db->getConnection()->exec("TRUNCATE TABLE security_rate_page");

        // Make 60 requests to reach the limit
        for ($i = 0; $i < 60; $i++) {
            $result = checkRateLimit($this->db, '/login');
            $this->assertTrue($result, "Request $i should be allowed");

            // Verify request was recorded
            $stmt = $this->db->getConnection()->prepare("
                SELECT COUNT(*) as count
                FROM security_rate_page
                WHERE ip_address = ?
                AND endpoint = ?
                AND request_time >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ");
            $stmt->execute(['8.8.8.8', '/login']);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $this->assertEquals($i + 1, $count, "Expected " . ($i + 1) . " requests to be recorded, got {$count}");
        }

        // The 61st request should be blocked
        $result = checkRateLimit($this->db, '/login');
        $this->assertFalse($result, "Request should be blocked after 60 requests");
    }
}
