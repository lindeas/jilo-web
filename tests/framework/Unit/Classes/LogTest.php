<?php

require_once dirname(__DIR__, 4) . '/app/classes/database.php';
require_once dirname(__DIR__, 4) . '/app/classes/log.php';

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    private $db;
    private $log;

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
                id INTEGER PRIMARY KEY,
                username TEXT NOT NULL
            )
        ");

        // Create test user
        $this->db->getConnection()->exec("
            INSERT INTO users (id, username) VALUES (1, 'testuser'), (2, 'testuser2')
        ");

        // Create logs table
        $this->db->getConnection()->exec("
            CREATE TABLE logs (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                scope TEXT NOT NULL,
                message TEXT NOT NULL,
                time DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        $this->log = new Log($this->db);
    }

    public function testInsertLog()
    {
        $result = $this->log->insertLog(1, 'Test message', 'test');
        $this->assertTrue($result);

        $stmt = $this->db->getConnection()->prepare("SELECT * FROM logs WHERE scope = ?");
        $stmt->execute(['test']);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $log['user_id']);
        $this->assertEquals('Test message', $log['message']);
        $this->assertEquals('test', $log['scope']);
    }

    public function testReadLog()
    {
        // Insert test logs
        $this->log->insertLog(1, 'Test message 1', 'user');
        $this->log->insertLog(1, 'Test message 2', 'user');

        $logs = $this->log->readLog(1, 'user');
        $this->assertCount(2, $logs);
        $this->assertEquals('Test message 1', $logs[0]['message']);
        $this->assertEquals('Test message 2', $logs[1]['message']);
    }

    public function testReadLogWithTimeFilter()
    {
        // Insert test logs with different times
        $this->log->insertLog(1, 'Old message', 'user');
        sleep(1); // Ensure different timestamps
        $this->log->insertLog(1, 'New message', 'user');

        $now = date('Y-m-d H:i:s');
        $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $logs = $this->log->readLog(1, 'user', 0, '', [
            'from_time' => $oneHourAgo,
            'until_time' => $now
        ]);

        $this->assertCount(2, $logs);
    }

    public function testReadLogWithPagination()
    {
        // Insert test logs
        $this->log->insertLog(1, 'Message 1', 'user');
        $this->log->insertLog(1, 'Message 2', 'user');
        $this->log->insertLog(1, 'Message 3', 'user');

        // Test with limit
        $logs = $this->log->readLog(1, 'user', 0, 2);
        $this->assertCount(2, $logs);

        // Test with offset
        $logs = $this->log->readLog(1, 'user', 2, 2);
        $this->assertCount(1, $logs);
    }

    public function testReadLogWithMessageFilter()
    {
        // Insert test logs
        $this->log->insertLog(1, 'Test message', 'user');
        $this->log->insertLog(1, 'Another message', 'user');

        $logs = $this->log->readLog(1, 'user', 0, '', [
            'message' => 'Test'
        ]);

        $this->assertCount(1, $logs);
        $this->assertEquals('Test message', $logs[0]['message']);
    }

    public function testReadLogWithUserFilter()
    {
        // Insert test logs for different users
        $this->log->insertLog(1, 'User 1 message', 'user');
        $this->log->insertLog(2, 'User 2 message', 'user');

        $logs = $this->log->readLog(1, 'user', 0, '', [
            'id' => 1
        ]);

        $this->assertCount(1, $logs);
        $this->assertEquals('User 1 message', $logs[0]['message']);
    }
}
