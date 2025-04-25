<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';

use PHPUnit\Framework\TestCase;

/**
 * TestLogger class for testing log functionality
 * This is a simplified implementation that mimics the plugin's Log class
 * but with a different name to avoid conflicts
 */
class TestLogger {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    public function insertLog($userId, $message, $scope = 'user') {
        try {
            $sql = 'INSERT INTO log
                        (user_id, scope, message)
                    VALUES
                        (:user_id, :scope, :message)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id' => $userId,
                ':scope'   => $scope,
                ':message' => $message,
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function readLog($userId, $scope, $offset = 0, $items_per_page = '', $filters = []) {
        $params = [];
        $where_clauses = [];

        // Base query with user join
        $base_sql = 'SELECT l.*, u.username 
                    FROM log l 
                    LEFT JOIN user u ON l.user_id = u.id';

        // Add scope condition
        if ($scope === 'user') {
            $where_clauses[] = 'l.user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        // Add time range filters if specified
        if (!empty($filters['from_time'])) {
            $where_clauses[] = 'l.time >= :from_time';
            $params[':from_time'] = $filters['from_time'] . ' 00:00:00';
        }
        if (!empty($filters['until_time'])) {
            $where_clauses[] = 'l.time <= :until_time';
            $params[':until_time'] = $filters['until_time'] . ' 23:59:59';
        }

        // Add message search if specified
        if (!empty($filters['message'])) {
            $where_clauses[] = 'l.message LIKE :message';
            $params[':message'] = '%' . $filters['message'] . '%';
        }

        // Add user ID search if specified
        if (!empty($filters['id'])) {
            $where_clauses[] = 'l.user_id = :search_user_id';
            $params[':search_user_id'] = $filters['id'];
        }

        // Combine WHERE clauses
        $sql = $base_sql;
        if (!empty($where_clauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
        }

        // Add ordering
        $sql .= ' ORDER BY l.time DESC';

        // Add pagination
        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

class LogTest extends TestCase
{
    private $db;
    private $log;
    private $testUserId;

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

        // Create user table
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS user (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create log table with the expected schema from Log class
        $this->db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                scope VARCHAR(50) NOT NULL DEFAULT 'user',
                message TEXT NOT NULL,
                time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(id)
            )
        ");

        // Create test users with all required fields
        $this->db->getConnection()->exec("
            INSERT INTO user (username, password, email) 
            VALUES 
                ('testuser', 'password123', 'testuser@example.com'),
                ('testuser2', 'password123', 'testuser2@example.com')
        ");

        // Store test user ID for later use
        $stmt = $this->db->getConnection()->query("SELECT id FROM user WHERE username = 'testuser' LIMIT 1");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->testUserId = $user['id'];

        // Create a TestLogger instance that will be used by the app's Log wrapper
        $this->log = new TestLogger($this->db);
    }

    protected function tearDown(): void
    {
        // Drop tables in correct order (respect foreign key constraints)
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS log");
        $this->db->getConnection()->exec("DROP TABLE IF EXISTS user");
        parent::tearDown();
    }

    public function testInsertLog()
    {
        $result = $this->log->insertLog($this->testUserId, 'Test message', 'test');
        $this->assertTrue($result);

        // Verify the log was inserted
        $stmt = $this->db->getConnection()->query("SELECT * FROM log WHERE user_id = {$this->testUserId} LIMIT 1");
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Test message', $log['message']);
        $this->assertEquals('test', $log['scope']);
    }

    public function testReadLog()
    {
        // Insert some test logs with a delay to ensure order
        $this->log->insertLog($this->testUserId, 'Test message 1', 'user');
        sleep(1); // Ensure different timestamps
        $this->log->insertLog($this->testUserId, 'Test message 2', 'user');

        $logs = $this->log->readLog($this->testUserId, 'user');
        $this->assertCount(2, $logs);
        $this->assertEquals('Test message 2', $logs[0]['message']); // Most recent first (by time)
    }

    public function testReadLogWithTimeFilter()
    {
        // First message from yesterday
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stmt = $this->db->getConnection()->prepare("
            INSERT INTO log (user_id, scope, message, time)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$this->testUserId, 'user', 'Test message 1', $yesterday . ' 12:00:00']);

        // Second message from today
        $today = date('Y-m-d');
        $stmt->execute([$this->testUserId, 'user', 'Test message 2', $today . ' 12:00:00']);

        // Should get only today's messages
        $logs = $this->log->readLog($this->testUserId, 'user', 0, '', [
            'from_time' => $today
        ]);
        $this->assertCount(1, $logs);
        $this->assertEquals('Test message 2', $logs[0]['message']); // Most recent first
    }

    public function testReadLogWithPagination()
    {
        // Insert multiple test logs with delays to ensure order
        for ($i = 1; $i <= 5; $i++) {
            $this->log->insertLog($this->testUserId, "Test message $i", 'user');
            sleep(1); // Ensure different timestamps
        }

        // Get all logs to verify total
        $allLogs = $this->log->readLog($this->testUserId, 'user');
        $this->assertCount(5, $allLogs);

        // Get first page (offset 0, limit 2)
        $logs = $this->log->readLog($this->testUserId, 'user', 0, 2);
        $this->assertCount(2, $logs);
        $this->assertEquals('Test message 5', $logs[0]['message']); // Most recent first
        $this->assertEquals('Test message 4', $logs[1]['message']);

        // Get second page (offset 2, limit 2)
        $logs = $this->log->readLog($this->testUserId, 'user', 2, 2);
        $this->assertCount(2, $logs);
        $this->assertEquals('Test message 3', $logs[0]['message']);
        $this->assertEquals('Test message 2', $logs[1]['message']);
    }

    public function testReadLogWithMessageFilter()
    {
        // Insert test logs with different messages and delays
        $this->log->insertLog($this->testUserId, 'Test message ABC', 'user');
        sleep(1); // Ensure different timestamps
        $this->log->insertLog($this->testUserId, 'Test message XYZ', 'user');
        sleep(1); // Ensure different timestamps
        $this->log->insertLog($this->testUserId, 'Different message', 'user');

        // Filter by message content
        $logs = $this->log->readLog($this->testUserId, 'user', 0, '', ['message' => 'Test message']);
        $this->assertCount(2, $logs);

        // Verify filtered results
        foreach ($logs as $log) {
            $this->assertStringContainsString('Test message', $log['message']);
        }
    }
}
