<?php

require_once dirname(__DIR__, 3) . '/app/core/App.php';
require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/user.php';
require_once dirname(__DIR__, 3) . '/plugins/register/models/register.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';

use PHPUnit\Framework\TestCase;
use App\App;

class UserTest extends TestCase
{
    private $db;
    private $register;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare DB for Github CI
        $host = defined('CI_DB_HOST') ? CI_DB_HOST : '127.0.0.1';
        $password = defined('CI_DB_PASSWORD') ? CI_DB_PASSWORD : '';

        $this->db = new Database([
            'type' => 'mariadb',
            'host' => $host,
            'port' => '3306',
            'dbname' => 'jilo_test',
            'user' => 'test_jilo',
            'password' => $password
        ]);

        // Set up App::db() for Register class to use
        App::set('db', $this->db->getConnection());

        // Use centralized schema setup
        setupTestDatabaseSchema($this->db->getConnection());

        // Clean up any test users from previous runs
        $this->db->getConnection()->exec("DELETE FROM user_2fa WHERE user_id >= 1000");
        $this->db->getConnection()->exec("DELETE FROM security_rate_auth WHERE username LIKE 'testuser%'");
        $this->db->getConnection()->exec("DELETE FROM user_meta WHERE user_id >= 1000");
        $this->db->getConnection()->exec("DELETE FROM user WHERE id >= 1000");

        $this->user = new User($this->db);
        $this->register = new Register();
    }

    protected function tearDown(): void
    {
        // Clean up App state
        App::reset('db');

        // Clean up test data
        $this->db->getConnection()->exec("DELETE FROM user_2fa WHERE user_id >= 1000");
        $this->db->getConnection()->exec("DELETE FROM security_rate_auth WHERE username LIKE 'testuser%'");
        $this->db->getConnection()->exec("DELETE FROM user_meta WHERE user_id >= 1000");
        $this->db->getConnection()->exec("DELETE FROM user WHERE id >= 1000");

        parent::tearDown();
    }

    public function testLogin()
    {
        // First register a user with unique username
        $username = 'testuser_login_' . time() . '_' . rand(1000, 9999);
        $password = 'password123';

        $this->register->register($username, $password);

        // Mock $_SERVER['REMOTE_ADDR'] for rate limiter
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Test successful login
        try {
            $result = $this->user->login($username, $password);
            $this->assertIsArray($result);
            $this->assertEquals('success', $result['status']);
            $this->assertArrayHasKey('user_id', $result);
            $this->assertArrayHasKey('username', $result);
            $this->assertArrayHasKey('user_id', $_SESSION);
            $this->assertArrayHasKey('username', $_SESSION);
            $this->assertArrayHasKey('CREATED', $_SESSION);
            $this->assertArrayHasKey('LAST_ACTIVITY', $_SESSION);
        } catch (Exception $e) {
            $this->fail('Login should not throw for valid credentials: ' . $e->getMessage());
        }

        // Test failed login
        $result = $this->user->login($username, 'wrongpassword');
        $this->assertIsArray($result);
        $this->assertEquals('failed', $result['status']);
        $this->assertArrayHasKey('message', $result);
        $this->assertStringContainsString('Invalid credentials', $result['message']);
    }

    public function testGetUserDetails()
    {
        // First register a user with unique username
        $username = 'testuser_details_' . time() . '_' . rand(1000, 9999);
        $password = 'password123';
        $result = $this->register->register($username, $password);
        $this->assertTrue($result);

        // Get user ID from database
        $stmt = $this->db->getConnection()->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();
        $this->assertNotFalse($userId);

        // Insert user metadata
        $stmt = $this->db->getConnection()->prepare("
            UPDATE user_meta
            SET name = ?, email = ?
            WHERE user_id = ?
        ");
        $stmt->execute(['Test User', 'test@example.com', $userId]);

        // Get user details
        $userDetails = $this->user->getUserDetails($userId);

        $this->assertIsArray($userDetails);
        $this->assertNotEmpty($userDetails);
        $this->assertArrayHasKey(0, $userDetails, 'User details should be returned as an array');

        // Get first row since we're querying by primary key
        $userDetails = $userDetails[0];

        $this->assertArrayHasKey('username', $userDetails, 'User details should include username');
        $this->assertArrayHasKey('name', $userDetails, 'User details should include name');
        $this->assertArrayHasKey('email', $userDetails, 'User details should include email');

        // Verify values
        $this->assertEquals($username, $userDetails['username'], 'Username should match');
        $this->assertEquals('Test User', $userDetails['name'], 'Name should match');
        $this->assertEquals('test@example.com', $userDetails['email'], 'Email should match');
    }
}
