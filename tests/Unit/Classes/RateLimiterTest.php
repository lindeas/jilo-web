<?php

require_once dirname(__DIR__, 3) . '/app/core/App.php';
require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';
require_once dirname(__DIR__, 3) . '/app/classes/log.php';

use PHPUnit\Framework\TestCase;
use App\App;

class RateLimiterTest extends TestCase
{
    private $db;
    private $rateLimiter;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare DB for Github CI
        $host = defined('CI_DB_HOST') ? CI_DB_HOST : '127.0.0.1';
        $password = defined('CI_DB_PASSWORD') ? CI_DB_PASSWORD : '';

        // Set up test database
        $this->db = new Database([
            'type' => 'mariadb',
            'host' => $host,
            'port' => '3306',
            'dbname' => 'jilo_test',
            'user' => 'test_jilo',
            'password' => $password
        ]);

        // Set up App::db() for RateLimiter
        App::set('db', $this->db->getConnection());
        
        // The RateLimiter constructor will create all necessary tables
        $this->rateLimiter = new RateLimiter();
    }

    protected function tearDown(): void
    {
        // Drop tables in correct order
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS {$this->rateLimiter->authRatelimitTable}");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS {$this->rateLimiter->pagesRatelimitTable}");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS {$this->rateLimiter->blacklistTable}");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS {$this->rateLimiter->whitelistTable}");
        
        // Clean up App state
        App::reset('db');
        
        parent::tearDown();
    }

    public function testGetRecentAttempts()
    {
        $ip = '8.8.8.8';

        // Record some login attempts
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->authRatelimitTable} 
            (ip_address, username, attempted_at) VALUES (?, ?, NOW())");

        // Add 3 attempts
        for ($i = 0; $i < 3; $i++) {
            $stmt->execute([$ip, 'testuser']);
        }

        $attempts = $this->rateLimiter->getRecentAttempts($ip);
        $this->assertEquals(3, $attempts);
    }

    public function testIsIpBlacklisted()
    {
        $ip = '8.8.8.8';

        // Add IP to blacklist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->blacklistTable} 
            (ip_address, is_network, reason) VALUES (?, ?, ?)");
        $stmt->execute([$ip, 0, 'Test blacklist']); // Explicitly set is_network to 0 (false)

        $this->assertTrue($this->rateLimiter->isIpBlacklisted($ip));
        $this->assertFalse($this->rateLimiter->isIpBlacklisted('8.8.4.4'));
    }

    public function testIsIpWhitelisted()
    {
        // Test with an IP that's not in the default whitelisted ranges
        $ip = '8.8.8.8'; // Google's DNS, definitely not in private ranges

        // Add IP to whitelist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->whitelistTable} 
            (ip_address, is_network, description) VALUES (?, ?, ?)");
        $stmt->execute([$ip, 0, 'Test whitelist']); // Explicitly set is_network to 0 (false)

        $this->assertTrue($this->rateLimiter->isIpWhitelisted($ip));
        $this->assertFalse($this->rateLimiter->isIpWhitelisted('8.8.4.4')); // Another IP not in private ranges
    }

    public function testRateLimitCheck()
    {
        $ip = '8.8.8.8'; // Use non-whitelisted IP
        $endpoint = '/test';

        // First request should be allowed
        $this->assertTrue($this->rateLimiter->isPageRequestAllowed($ip, $endpoint));

        // Add requests up to the limit
        for ($i = 0; $i < 60; $i++) { // Default limit is 60 per minute
            $this->rateLimiter->recordPageRequest($ip, $endpoint);
        }

        // The next request should be rate limited
        $this->assertFalse($this->rateLimiter->isPageRequestAllowed($ip, $endpoint));
    }
}
