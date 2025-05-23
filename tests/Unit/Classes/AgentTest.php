<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';
require_once dirname(__DIR__, 3) . '/app/classes/agent.php';

use PHPUnit\Framework\TestCase;

class AgentTest extends TestCase
{
    private $db;
    private $agent;

    protected function setUp(): void
    {
        parent::setUp();

        // Set development environment for detailed errors
        global $config;
        $config['environment'] = 'development';

        // Set up test database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create jilo_agent table
        $this->db->getConnection()->exec("
            CREATE TABLE jilo_agent (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                host_id INTEGER NOT NULL,
                agent_type_id INTEGER NOT NULL,
                url TEXT NOT NULL,
                secret_key TEXT,
                check_period INTEGER DEFAULT 60,
                created_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now')),
                updated_at INTEGER NOT NULL DEFAULT (strftime('%s', 'now'))
            )
        ");

        // Create jilo_agent_type table
        $this->db->getConnection()->exec("
            CREATE TABLE jilo_agent_type (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                description TEXT NOT NULL,
                endpoint TEXT NOT NULL
            )
        ");

        // Create host table
        $this->db->getConnection()->exec("
            CREATE TABLE host (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                platform_id INTEGER NOT NULL,
                name TEXT NOT NULL
            )
        ");

        // Insert test host
        $this->db->getConnection()->exec("
            INSERT INTO host (id, platform_id, name) VALUES (1, 1, 'Test Host')
        ");

        // Insert test agent type
        $this->db->getConnection()->exec("
            INSERT INTO jilo_agent_type (id, description, endpoint) 
            VALUES (1, 'Test Agent Type', '/api/test')
        ");

        $this->agent = new Agent($this->db);
    }

    public function testAddAgent()
    {
        $hostId = 1;
        $data = [
            'type_id' => 1,
            'url' => 'http://test.agent:8080',
            'secret_key' => 'test_secret',
            'check_period' => 60
        ];

        try {
            $result = $this->agent->addAgent($hostId, $data);
            $this->assertTrue($result);

            // Verify agent was created
            $stmt = $this->db->getConnection()->prepare('SELECT * FROM jilo_agent WHERE host_id = ?');
            $stmt->execute([$hostId]);
            $agent = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->assertEquals($data['url'], $agent['url']);
            $this->assertEquals($data['secret_key'], $agent['secret_key']);
            $this->assertEquals($data['check_period'], $agent['check_period']);
        } catch (Exception $e) {
            $this->fail('An error occurred while adding agent: ' . $e->getMessage());
        }
    }

    public function testGetAgentDetails()
    {
        // Add test agent
        $hostId = 1;
        $data = [
            'type_id' => 1,
            'url' => 'http://test.agent:8080',
            'secret_key' => 'test_secret',
            'check_period' => 60
        ];

        $this->agent->addAgent($hostId, $data);

        // Test getting agent details
        $agents = $this->agent->getAgentDetails($hostId);
        $this->assertIsArray($agents);
        $this->assertCount(1, $agents);
        $this->assertEquals($data['url'], $agents[0]['url']);
    }

    public function testEditAgent()
    {
        // Add test agent
        $hostId = 1;
        $data = [
            'type_id' => 1,
            'url' => 'http://test.agent:8080',
            'secret_key' => 'test_secret',
            'check_period' => 60
        ];

        $this->agent->addAgent($hostId, $data);

        // Get agent ID
        $stmt = $this->db->getConnection()->prepare('SELECT id FROM jilo_agent WHERE host_id = ? LIMIT 1');
        $stmt->execute([$hostId]);
        $agentId = $stmt->fetch(PDO::FETCH_COLUMN);

        // Update agent
        $updateData = [
            'type_id' => 1,
            'url' => 'http://updated.agent:8080',
            'secret_key' => 'updated_secret',
            'check_period' => 120,
            'agent_type_id' => 1  // Add this field for the update
        ];

        $result = $this->agent->editAgent($agentId, $updateData);
        $this->assertTrue($result);

        // Verify update
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM jilo_agent WHERE id = ?');
        $stmt->execute([$agentId]);
        $agent = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals($updateData['url'], $agent['url']);
        $this->assertEquals($updateData['secret_key'], $agent['secret_key']);
        $this->assertEquals($updateData['check_period'], $agent['check_period']);
    }

    public function testDeleteAgent()
    {
        // Add test agent
        $hostId = 1;
        $data = [
            'type_id' => 1,
            'url' => 'http://test.agent:8080',
            'secret_key' => 'test_secret',
            'check_period' => 60
        ];

        $this->agent->addAgent($hostId, $data);

        // Get agent ID
        $stmt = $this->db->getConnection()->prepare('SELECT id FROM jilo_agent WHERE host_id = ? LIMIT 1');
        $stmt->execute([$hostId]);
        $agentId = $stmt->fetch(PDO::FETCH_COLUMN);

        // Delete agent
        $result = $this->agent->deleteAgent($agentId);
        $this->assertTrue($result);

        // Verify deletion
        $stmt = $this->db->getConnection()->prepare('SELECT COUNT(*) FROM jilo_agent WHERE id = ?');
        $stmt->execute([$agentId]);
        $count = $stmt->fetch(PDO::FETCH_COLUMN);

        $this->assertEquals(0, $count);
    }

    public function testFetchAgent()
    {
        // Add test agent
        $hostId = 1;
        $data = [
            'type_id' => 1,
            'url' => 'http://test.agent:8080',
            'secret_key' => 'test_secret',
            'check_period' => 60
        ];

        $this->agent->addAgent($hostId, $data);

        // Get agent ID
        $stmt = $this->db->getConnection()->prepare('SELECT id FROM jilo_agent WHERE host_id = ? LIMIT 1');
        $stmt->execute([$hostId]);
        $agentId = $stmt->fetch(PDO::FETCH_COLUMN);

        // Mock fetch response
        $mockAgent = $this->getMockBuilder(Agent::class)
            ->setConstructorArgs([$this->db])
            ->onlyMethods(['fetchAgent'])
            ->getMock();

        $mockResponse = json_encode([
            'status' => 'ok',
            'metrics' => [
                'cpu_usage' => 25.5,
                'memory_usage' => 1024,
                'uptime' => 3600
            ]
        ]);

        $mockAgent->expects($this->once())
            ->method('fetchAgent')
            ->willReturn($mockResponse);

        $response = $mockAgent->fetchAgent($agentId);
        $this->assertJson($response);

        $data = json_decode($response, true);
        $this->assertEquals('ok', $data['status']);
    }
}
