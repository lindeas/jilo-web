<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';
require_once dirname(__DIR__, 3) . '/app/classes/log.php';

use PHPUnit\Framework\TestCase;

class RateLimiterTest extends TestCase
{
    private $rateLimiter;
    private $db;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up in-memory SQLite database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        $this->rateLimiter = new RateLimiter($this->db);
    }

    public function testGetRecentAttempts()
    {
        $ip = '127.0.0.1';
        $username = 'testuser';

        // Clean up any existing attempts first
        $stmt = $this->db->getConnection()->prepare("DELETE FROM {$this->rateLimiter->authRatelimitTable} WHERE ip_address = ?");
        $stmt->execute([$ip]);

        // Initially should have no attempts
        $attempts = $this->rateLimiter->getRecentAttempts($ip);
        $this->assertEquals(0, $attempts);

        // Add a login attempt
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->authRatelimitTable} (ip_address, username) VALUES (?, ?)");
        $stmt->execute([$ip, $username]);

        // Should now have 1 attempt
        $attempts = $this->rateLimiter->getRecentAttempts($ip);
        $this->assertEquals(1, $attempts);
    }

    public function testIpBlacklisting()
    {
        $ip = '192.0.2.1'; // Using TEST-NET-1 range

        // Should be blacklisted by default (TEST-NET-1 range)
        $this->assertTrue($this->rateLimiter->isIpBlacklisted($ip));

        // Test with non-blacklisted IP
        $nonBlacklistedIp = '8.8.8.8'; // Google DNS
        $this->assertFalse($this->rateLimiter->isIpBlacklisted($nonBlacklistedIp));

        // Add IP to blacklist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->blacklistTable} (ip_address, reason) VALUES (?, ?)");
        $stmt->execute([$nonBlacklistedIp, 'Test blacklist']);

        // IP should now be blacklisted
        $this->assertTrue($this->rateLimiter->isIpBlacklisted($nonBlacklistedIp));
    }

    public function testIpWhitelisting()
    {
        $ip = '127.0.0.1'; // Localhost

        // Clean up any existing whitelist entries
        $stmt = $this->db->getConnection()->prepare("DELETE FROM {$this->rateLimiter->whitelistTable} WHERE ip_address = ?");
        $stmt->execute([$ip]);

        // Add to whitelist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->whitelistTable} (ip_address, description) VALUES (?, ?)");
        $stmt->execute([$ip, 'Test whitelist']);

        // Should be whitelisted
        $this->assertTrue($this->rateLimiter->isIpWhitelisted($ip));

        // Test with non-whitelisted IP
        $nonWhitelistedIp = '8.8.8.8'; // Google DNS
        $this->assertFalse($this->rateLimiter->isIpWhitelisted($nonWhitelistedIp));

        // Add to whitelist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->whitelistTable} (ip_address, description) VALUES (?, ?)");
        $stmt->execute([$nonWhitelistedIp, 'Test whitelist']);

        // Should now be whitelisted
        $this->assertTrue($this->rateLimiter->isIpWhitelisted($nonWhitelistedIp));
    }

    public function testIpRangeBlacklisting()
    {
        $ip = '8.8.8.8'; // Google DNS
        $networkIp = '8.8.8.0/24'; // Network containing Google DNS

        // Initially IP should not be blacklisted
        $this->assertFalse($this->rateLimiter->isIpBlacklisted($ip));

        // Add network to blacklist
        $stmt = $this->db->getConnection()->prepare("INSERT INTO {$this->rateLimiter->blacklistTable} (ip_address, is_network, reason) VALUES (?, 1, ?)");
        $stmt->execute([$networkIp, 'Test network blacklist']);

        // IP in range should now be blacklisted
        $this->assertTrue($this->rateLimiter->isIpBlacklisted($ip));
    }
}
