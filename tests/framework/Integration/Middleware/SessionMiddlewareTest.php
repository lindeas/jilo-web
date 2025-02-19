<?php

require_once dirname(__DIR__, 4) . '/app/includes/session_middleware.php';

use PHPUnit\Framework\TestCase;

class SessionMiddlewareTest extends TestCase
{
    protected $config;
    protected $app_root;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock server variables
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTPS'] = 'on';

        // Set up test config
        $this->config = [
            'folder' => '/app',
            'domain' => 'localhost'
        ];
        $this->app_root = 'https://localhost/app';
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSessionStart()
    {
        $_SESSION = ['USER_ID' => 1];
        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertTrue($result);
        $this->assertArrayHasKey('LAST_ACTIVITY', $_SESSION);
        $this->assertArrayHasKey('CREATED', $_SESSION);
        $this->assertArrayHasKey('USER_ID', $_SESSION);
        $this->assertEquals(1, $_SESSION['USER_ID']);
    }

    public function testSessionTimeout()
    {
        $_SESSION = [
            'USER_ID' => 1,
            'LAST_ACTIVITY' => time() - 1500 // 25 minutes ago
        ];

        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertFalse($result);
        $this->assertArrayNotHasKey('USER_ID', $_SESSION, 'Session should be cleared after timeout');
    }

    public function testSessionRegeneration()
    {
        $now = time();
        $_SESSION = [
            'USER_ID' => 1,
            'CREATED' => $now - 1900 // 31+ minutes ago
        ];

        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertTrue($result);
        $this->assertEquals(1, $_SESSION['USER_ID']);
        $this->assertGreaterThanOrEqual($now - 1900, $_SESSION['CREATED']);
        $this->assertLessThanOrEqual($now + 10, $_SESSION['CREATED']);
    }

    public function testRememberMe()
    {
        $_SESSION = [
            'USER_ID' => 1,
            'REMEMBER_ME' => true,
            'LAST_ACTIVITY' => time() - 86500 // More than 24 hours ago
        ];

        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertTrue($result);
        $this->assertArrayHasKey('USER_ID', $_SESSION);
    }

    public function testNoUserSession()
    {
        $_SESSION = [];
        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertFalse($result);
        $this->assertArrayNotHasKey('USER_ID', $_SESSION);
    }

    public function testSessionHeaders()
    {
        $_SESSION = [
            'USER_ID' => 1,
            'LAST_ACTIVITY' => time() - 1500 // 25 minutes ago
        ];

        $result = applySessionMiddleware($this->config, $this->app_root);

        $this->assertFalse($result);
        $this->assertArrayNotHasKey('USER_ID', $_SESSION, 'Session should be cleared after timeout');
    }
}
