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

        // Create user table with MariaDB syntax
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS user (
                id INT PRIMARY KEY AUTO_INCREMENT,
                username VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create user_meta table with MariaDB syntax
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS user_meta (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                name VARCHAR(255),
                email VARCHAR(255),
                timezone VARCHAR(100),
                bio TEXT,
                avatar VARCHAR(255),
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
            )
        ");

        // Create security_rate_auth table for rate limiting
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS security_rate_auth (
                id INT PRIMARY KEY AUTO_INCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                username VARCHAR(255) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ip_username (ip_address, username)
            )
        ");

        // Create user_2fa table for two-factor authentication
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS user_2fa (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                secret_key VARCHAR(255) NOT NULL,
                backup_codes TEXT,
                enabled TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
            )
        ");

        $this->user = new User($this->db);
        $this->register = new Register();
    }

    protected function tearDown(): void
    {
        // Clean up App state
        App::reset('db');
        
        // Drop tables in correct order
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS user_2fa");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS security_rate_auth");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS user_meta");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS user");
        parent::tearDown();
    }

    public function testLogin()
    {
        // First register a user
        $username = 'testuser';
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
        // Register a test user first
        $username = 'testuser';
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
