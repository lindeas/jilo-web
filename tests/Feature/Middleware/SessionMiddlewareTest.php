<?php

use PHPUnit\Framework\TestCase;
use Tests\Feature\Middleware\Mock\Session;
use Tests\Feature\Middleware\Mock\Feedback;

require_once __DIR__ . '/MockSession.php';
require_once __DIR__ . '/MockFeedback.php';

class SessionMiddlewareTest extends TestCase
{
    protected $config;
    protected $app_root;
    protected const SESSION_TIMEOUT = 7200; // 2 hours in seconds

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

        // Initialize session variables
        $_SESSION = [
            'user_id' => 1,
            'username' => 'testuser',
            'CREATED' => time(),
            'LAST_ACTIVITY' => time()
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    protected function applyMiddleware()
    {
        // Check session validity
        if (!Session::isValidSession()) {
            // Session invalid, clean up
            Session::cleanup($this->config);
            Feedback::flash("LOGIN", "SESSION_TIMEOUT");
            return false;
        }
        return true;
    }

    public function testValidSession()
    {
        $result = $this->applyMiddleware();

        $this->assertTrue($result);
        $this->assertArrayHasKey('LAST_ACTIVITY', $_SESSION);
        $this->assertArrayHasKey('CREATED', $_SESSION);
        $this->assertArrayHasKey('user_id', $_SESSION);
        $this->assertEquals(1, $_SESSION['user_id']);
    }

    public function testSessionTimeout()
    {
        $_SESSION['LAST_ACTIVITY'] = time() - (self::SESSION_TIMEOUT + 60); // 2 hours + 1 minute ago
        $result = $this->applyMiddleware();

        $this->assertFalse($result);
        $this->assertEmpty($_SESSION);
    }

    public function testRememberMe()
    {
        $_SESSION['REMEMBER_ME'] = true;
        $_SESSION['LAST_ACTIVITY'] = time() - (self::SESSION_TIMEOUT + 60); // More than 2 hours ago

        $result = $this->applyMiddleware();

        $this->assertTrue($result);
        $this->assertArrayHasKey('user_id', $_SESSION);
    }

    public function testNoUserSession()
    {
        unset($_SESSION['user_id']);
        $result = $this->applyMiddleware();

        $this->assertFalse($result);
        $this->assertEmpty($_SESSION);
    }

    public function testInvalidSession()
    {
        $_SESSION['LAST_ACTIVITY'] = time() - (self::SESSION_TIMEOUT + 60); // 2 hours + 1 minute ago
        unset($_SESSION['REMEMBER_ME']);
        $result = $this->applyMiddleware();

        $this->assertFalse($result);
        $this->assertEmpty($_SESSION);
    }
}
