<?php

require_once dirname(__DIR__, 4) . '/app/classes/host.php';

use PHPUnit\Framework\TestCase;

class HostTest extends TestCase
{
    private $db;
    private $host;

    protected function setUp(): void
    {
        parent::setUp();

        // Set development environment for detailed errors
        global $config;
        $config['environment'] = 'development';

        // Set up test database
        $this->db = new \Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create hosts table
        $this->db->getConnection()->exec("
            CREATE TABLE hosts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                platform_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                address TEXT NOT NULL
            )
        ");

        // Create jilo_agents table for relationship testing
        $this->db->getConnection()->exec("
            CREATE TABLE jilo_agents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                host_id INTEGER NOT NULL,
                agent_type_id INTEGER NOT NULL,
                url TEXT NOT NULL,
                secret_key TEXT,
                check_period INTEGER DEFAULT 60
            )
        ");

        $this->host = new \Host($this->db);
    }

    public function testAddHost()
    {
        $data = [
            'platform_id' => 1,
            'name' => 'Test host',
            'address' => '192.168.1.100'
        ];

        $result = $this->host->addHost($data);
        $this->assertTrue($result);

        // Verify host was created
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM hosts WHERE platform_id = ? AND name = ?');
        $stmt->execute([$data['platform_id'], $data['name']]);
        $host = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($data['name'], $host['name']);
        $this->assertEquals($data['address'], $host['address']);
    }

    public function testGetHostDetails()
    {
        // Add test host
        $data = [
            'platform_id' => 1,
            'name' => 'Test host',
            'address' => '192.168.1.100'
        ];
        $this->host->addHost($data);

        // Test getting all hosts
        $hosts = $this->host->getHostDetails();
        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);

        // Test getting hosts by platform
        $hosts = $this->host->getHostDetails(1);
        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals($data['name'], $hosts[0]['name']);

        // Test getting specific host
        $hosts = $this->host->getHostDetails(1, $hosts[0]['id']);
        $this->assertIsArray($hosts);
        $this->assertCount(1, $hosts);
        $this->assertEquals($data['name'], $hosts[0]['name']);
    }

    public function testEditHost()
    {
        // Add test host
        $data = [
            'platform_id' => 1,
            'name' => 'Test host',
            'address' => '192.168.1.100'
        ];
        $this->host->addHost($data);

        // Get host ID
        $stmt = $this->db->getConnection()->prepare('SELECT id FROM hosts WHERE platform_id = ? AND name = ?');
        $stmt->execute([$data['platform_id'], $data['name']]);
        $hostId = $stmt->fetch(\PDO::FETCH_COLUMN);

        // Update host
        $updateData = [
            'id' => $hostId,
            'name' => 'Updated host',
            'address' => '192.168.1.200'
        ];

        $result = $this->host->editHost($data['platform_id'], $updateData);
        $this->assertTrue($result);

        // Verify update
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM hosts WHERE id = ?');
        $stmt->execute([$hostId]);
        $host = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals($updateData['name'], $host['name']);
        $this->assertEquals($updateData['address'], $host['address']);
    }

    public function testDeleteHost()
    {
        // Add test host
        $data = [
            'platform_id' => 1,
            'name' => 'Test host',
            'address' => '192.168.1.100'
        ];
        $this->host->addHost($data);

        // Get host ID
        $stmt = $this->db->getConnection()->prepare('SELECT id FROM hosts WHERE platform_id = ? AND name = ?');
        $stmt->execute([$data['platform_id'], $data['name']]);
        $hostId = $stmt->fetch(\PDO::FETCH_COLUMN);

        // Add test agent to the host
        $this->db->getConnection()->exec("
            INSERT INTO jilo_agents (host_id, agent_type_id, url, secret_key)
            VALUES ($hostId, 1, 'http://test:8080', 'secret')
        ");

        // Delete host
        $result = $this->host->deleteHost($hostId);
        $this->assertTrue($result);

        // Verify host deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) FROM hosts WHERE id = ?');
        $stmt->execute([$hostId]);
        $hostCount = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->assertEquals(0, $hostCount);

        // Verify agent deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) FROM jilo_agents WHERE host_id = ?');
        $stmt->execute([$hostId]);
        $agentCount = $stmt->fetch(\PDO::FETCH_COLUMN);
        $this->assertEquals(0, $agentCount);
    }

    public function testEditNonexistentHost()
    {
        $updateData = [
            'id' => 999,
            'name' => 'Nonexistent host',
            'address' => '192.168.1.200'
        ];

        $result = $this->host->editHost(1, $updateData);
        $this->assertIsString($result);
        $this->assertStringContainsString('No host found', $result);
    }
}
