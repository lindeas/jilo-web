<?php

require_once dirname(__DIR__, 4) . '/app/classes/database.php';
require_once dirname(__DIR__, 4) . '/app/classes/platform.php';

use PHPUnit\Framework\TestCase;

class PlatformTest extends TestCase
{
    private $db;
    private $platform;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create hosts table
        $this->db->getConnection()->exec("
            CREATE TABLE hosts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                platform_id INTEGER NOT NULL,
                name TEXT NOT NULL
            )
        ");

        // Create jilo_agents table
        $this->db->getConnection()->exec("
            CREATE TABLE jilo_agents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                host_id INTEGER NOT NULL
            )
        ");

        // Create platforms table
        $this->db->getConnection()->exec("
            CREATE TABLE platforms (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                jitsi_url TEXT NOT NULL,
                jilo_database TEXT NOT NULL,
                created_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now'))
            )
        ");

        $this->platform = new Platform($this->db);
    }

    public function testAddPlatform()
    {
        $data = [
            'name' => 'Test platform',
            'jitsi_url' => 'https://jitsi.example.com',
            'jilo_database' => '/path/to/jilo.db'
        ];

        $result = $this->platform->addPlatform($data);
        $this->assertTrue($result);

        // Verify platform was created
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM platforms WHERE name = ?');
        $stmt->execute([$data['name']]);
        $platform = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotNull($platform);
        $this->assertEquals($data['name'], $platform['name']);
        $this->assertEquals($data['jitsi_url'], $platform['jitsi_url']);
        $this->assertEquals($data['jilo_database'], $platform['jilo_database']);
    }

    public function testGetPlatformDetails()
    {
        // Create test platform
        $stmt = $this->db->getConnection()->prepare('INSERT INTO platforms (name, jitsi_url, jilo_database) VALUES (?, ?, ?)');
        $stmt->execute(['Test platform', 'https://jitsi.example.com', '/path/to/jilo.db']);
        $platformId = $this->db->getConnection()->lastInsertId();

        // Test getting specific platform
        $platform = $this->platform->getPlatformDetails($platformId);
        $this->assertIsArray($platform);
        $this->assertEquals('Test platform', $platform[0]['name']);

        // Test getting all platforms
        $platforms = $this->platform->getPlatformDetails();
        $this->assertIsArray($platforms);
        $this->assertCount(1, $platforms);
    }

    public function testEditPlatform()
    {
        // Create test platform
        $stmt = $this->db->getConnection()->prepare('INSERT INTO platforms (name, jitsi_url, jilo_database) VALUES (?, ?, ?)');
        $stmt->execute(['Test platform', 'https://jitsi.example.com', '/path/to/jilo.db']);
        $platformId = $this->db->getConnection()->lastInsertId();

        $updateData = [
            'name' => 'Updated platform',
            'jitsi_url' => 'https://new.example.com',
            'jilo_database' => '/path/to/jilo.db'
        ];

        $result = $this->platform->editPlatform($platformId, $updateData);
        $this->assertTrue($result);

        // Verify update
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM platforms WHERE id = ?');
        $stmt->execute([$platformId]);
        $platform = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($updateData['name'], $platform['name']);
        $this->assertEquals($updateData['jitsi_url'], $platform['jitsi_url']);
    }

    public function testDeletePlatform()
    {
        // Create test platform
        $stmt = $this->db->getConnection()->prepare('INSERT INTO platforms (name, jitsi_url, jilo_database) VALUES (?, ?, ?)');
        $stmt->execute(['Test platform', 'https://jitsi.example.com', '/path/to/jilo.db']);
        $platformId = $this->db->getConnection()->lastInsertId();

        // Create test host
        $stmt = $this->db->getConnection()->prepare('INSERT INTO hosts (platform_id, name) VALUES (?, ?)');
        $stmt->execute([$platformId, 'Test host']);
        $hostId = $this->db->getConnection()->lastInsertId();

        // Create test agent
        $stmt = $this->db->getConnection()->prepare('INSERT INTO jilo_agents (host_id) VALUES (?)');
        $stmt->execute([$hostId]);

        $result = $this->platform->deletePlatform($platformId);
        $this->assertTrue($result);

        // Verify platform deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM platforms WHERE id = ?');
        $stmt->execute([$platformId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, $result['count']);

        // Verify host deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM hosts WHERE platform_id = ?');
        $stmt->execute([$platformId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, $result['count']);

        // Verify agent deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM jilo_agents WHERE host_id = ?');
        $stmt->execute([$hostId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, $result['count']);
    }

    public function testValidatePlatformData()
    {
        $validData = [
            'name' => 'Test platform',
            'jitsi_url' => 'https://jitsi.example.com',
            'jilo_database' => '/path/to/jilo.db'
        ];

        $result = $this->platform->addPlatform($validData);
        $this->assertTrue($result);

        // Verify platform was created
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM platforms WHERE name = ?');
        $stmt->execute([$validData['name']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $result['count']);

        // Test invalid data (missing required fields)
        $invalidData = [
            'name' => 'Test platform 2'
            // Missing jitsi_url and jilo_database
        ];

        $result = $this->platform->addPlatform($invalidData);
        $this->assertIsString($result); // Should return error message

        // Verify platform was not created
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM platforms WHERE name = ?');
        $stmt->execute([$invalidData['name']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(0, $result['count']);
    }

    public function testCheckJiloDatabaseAccess()
    {
        // Create a temporary SQLite database for testing
        $tempDb = tempnam(sys_get_temp_dir(), 'jilo_test_');
        $testDb = new \SQLite3($tempDb);
        $testDb->close();

        $data = [
            'name' => 'Test platform',
            'jitsi_url' => 'https://jitsi.example.com',
            'jilo_database' => $tempDb
        ];

        $result = $this->platform->addPlatform($data);
        $this->assertTrue($result);

        // Verify platform was created
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) as count FROM platforms WHERE jilo_database = ?');
        $stmt->execute([$tempDb]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals(1, $result['count']);

        // Test with non-existent database
        $data['name'] = 'Another platform';
        $data['jilo_database'] = '/nonexistent/path/db.sqlite';
        $result = $this->platform->addPlatform($data);
        $this->assertTrue($result); // No database validation in Platform class

        // Clean up
        unlink($tempDb);
    }
}
