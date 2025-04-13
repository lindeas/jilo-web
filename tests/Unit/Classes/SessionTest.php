<?php

namespace Tests\Unit\Classes;

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../../app/classes/session.php';
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    public function testGetUsername()
    {
        $_SESSION['username'] = 'testuser';
        $this->assertEquals('testuser', \Session::getUsername());
        unset($_SESSION['username']);
        $this->assertNull(\Session::getUsername());
    }

    public function testGetUserId()
    {
        $_SESSION['user_id'] = 123;
        $this->assertEquals(123, \Session::getUserId());
        unset($_SESSION['user_id']);
        $this->assertNull(\Session::getUserId());
    }

    public function testIsValidSession()
    {
        // Invalid without required variables
        $this->assertFalse(\Session::isValidSession());

        // Valid with required variables
        $_SESSION['user_id'] = 123;
        $_SESSION['username'] = 'testuser';
        $_SESSION['LAST_ACTIVITY'] = time();
        $this->assertTrue(\Session::isValidSession());

        // Invalid after timeout
        $_SESSION['LAST_ACTIVITY'] = time() - 8000; // More than 2 hours
        $this->assertFalse(\Session::isValidSession());

        // Valid with remember me
        $_SESSION = [
            'user_id' => 123,
            'username' => 'testuser',
            'REMEMBER_ME' => true,
            'LAST_ACTIVITY' => time() - 8000
        ];
        $this->assertTrue(\Session::isValidSession());
    }

    public function testSetRememberMe()
    {
        \Session::setRememberMe(true);
        $this->assertTrue($_SESSION['REMEMBER_ME']);
        \Session::setRememberMe(false);
        $this->assertFalse($_SESSION['REMEMBER_ME']);
    }

    public function test2FASession()
    {
        // Test storing 2FA pending info
        \Session::store2FAPending(123, 'testuser', true);
        $this->assertEquals(123, $_SESSION['2fa_pending_user_id']);
        $this->assertEquals('testuser', $_SESSION['2fa_pending_username']);
        $this->assertTrue(isset($_SESSION['2fa_pending_remember']));

        // Test getting 2FA pending info
        $pendingInfo = \Session::get2FAPending();
        $this->assertEquals([
            'user_id' => 123,
            'username' => 'testuser',
            'remember_me' => true
        ], $pendingInfo);

        // Test clearing 2FA pending info
        \Session::clear2FAPending();
        $this->assertNull(\Session::get2FAPending());
    }
}
