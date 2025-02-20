<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/user.php';
require_once dirname(__DIR__, 3) . '/app/classes/ratelimiter.php';

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $db;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create users table
        $this->db->getConnection()->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            )
        ");

        // Create users_meta table
        $this->db->getConnection()->exec("
            CREATE TABLE users_meta (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT,
                email TEXT,
                timezone TEXT,
                bio TEXT,
                avatar TEXT,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Create tables for rate limiter
        $this->db->getConnection()->exec("
            CREATE TABLE login_attempts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address TEXT NOT NULL,
                username TEXT NOT NULL,
                attempted_at TEXT DEFAULT (DATETIME('now'))
            )
        ");

        $this->db->getConnection()->exec("
            CREATE TABLE ip_whitelist (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address TEXT NOT NULL UNIQUE,
                is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
                description TEXT,
                created_at TEXT DEFAULT (DATETIME('now')),
                created_by TEXT
            )
        ");

        $this->db->getConnection()->exec("
            CREATE TABLE ip_blacklist (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address TEXT NOT NULL UNIQUE,
                is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
                reason TEXT,
                expiry_time TEXT NULL,
                created_at TEXT DEFAULT (DATETIME('now')),
                created_by TEXT
            )
        ");

        $this->user = new User($this->db);
    }

    public function testRegister()
    {
        $result = $this->user->register('testuser', 'password123');
        $this->assertTrue($result);

        // Verify user was created
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute(['testuser']);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals('testuser', $user['username']);
        $this->assertTrue(password_verify('password123', $user['password']));

        // Verify user_meta was created
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM users_meta WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $meta = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotNull($meta);
    }

    public function testLogin()
    {
        // Create a test user
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->getConnection()->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute(['testuser', $hashedPassword]);

        // Mock $_SERVER['REMOTE_ADDR'] for rate limiter
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Test successful login
        try {
            $result = $this->user->login('testuser', $password);
            $this->assertTrue($result);
        } catch (Exception $e) {
            $this->fail('Login should not throw an exception for valid credentials: ' . $e->getMessage());
        }

        // Test failed login
        try {
            $this->user->login('testuser', 'wrongpassword');
            $this->fail('Login should throw an exception for invalid credentials');
        } catch (Exception $e) {
            $this->assertStringContainsString('Invalid credentials', $e->getMessage());
        }

        // Test nonexistent user
        try {
            $this->user->login('nonexistent', $password);
            $this->fail('Login should throw an exception for nonexistent user');
        } catch (Exception $e) {
            $this->assertStringContainsString('Invalid credentials', $e->getMessage());
        }
    }

    public function testGetUserDetails()
    {
        // Create a test user
        $stmt = $this->db->getConnection()->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute(['testuser', 'hashedpassword']);
        $userId = $this->db->getConnection()->lastInsertId();

        // Create user meta with some data
        $stmt = $this->db->getConnection()->prepare('INSERT INTO users_meta (user_id, name, email) VALUES (?, ?, ?)');
        $stmt->execute([$userId, 'Test User', 'test@example.com']);

        $userDetails = $this->user->getUserDetails($userId);
        $this->assertIsArray($userDetails);
        $this->assertCount(1, $userDetails); // Should return one row
        $user = $userDetails[0]; // Get the first row
        $this->assertEquals('testuser', $user['username']);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test@example.com', $user['email']);

        // Test nonexistent user
        $userDetails = $this->user->getUserDetails(999);
        $this->assertEmpty($userDetails);
    }
}
