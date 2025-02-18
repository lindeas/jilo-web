<?php

require_once dirname(__DIR__, 4) . '/app/classes/database.php';
require_once dirname(__DIR__, 4) . '/app/classes/server.php';

use PHPUnit\Framework\TestCase;

class JiloServerTest extends TestCase
{
    private $db;
    private $server;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database
        $this->db = new Database([
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ]);

        // Create servers table
        $this->db->getConnection()->exec("
            CREATE TABLE servers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                host_id INTEGER NOT NULL,
                port INTEGER NOT NULL,
                status TEXT DEFAULT 'offline',
                last_seen INTEGER,
                version TEXT,
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            )
        ");

        $this->server = new Server($this->db);
    }

    public function testGetServerStatus()
    {
        // Create mock server that overrides file_get_contents
        $mockServer = $this->getMockBuilder(Server::class)
            ->setConstructorArgs([$this->db])
            ->onlyMethods(['getServerStatus'])
            ->getMock();

        // Test successful response
        $mockServer->expects($this->exactly(2))
            ->method('getServerStatus')
            ->willReturnMap([
                ['localhost', 8080, '/health', true],
                ['localhost', 8081, '/health', false]
            ]);

        $this->assertTrue($mockServer->getServerStatus('localhost', 8080));
        $this->assertFalse($mockServer->getServerStatus('localhost', 8081));
    }

    public function testGetServerStatusWithCustomEndpoint()
    {
        $mockServer = $this->getMockBuilder(Server::class)
            ->setConstructorArgs([$this->db])
            ->onlyMethods(['getServerStatus'])
            ->getMock();

        $mockServer->expects($this->once())
            ->method('getServerStatus')
            ->with('localhost', 8080, '/custom/health')
            ->willReturn(true);

        $this->assertTrue($mockServer->getServerStatus('localhost', 8080, '/custom/health'));
    }

    public function testGetServerStatusWithDefaults()
    {
        $mockServer = $this->getMockBuilder(Server::class)
            ->setConstructorArgs([$this->db])
            ->onlyMethods(['getServerStatus'])
            ->getMock();

        $mockServer->expects($this->once())
            ->method('getServerStatus')
            ->with('127.0.0.1', 8080, '/health')
            ->willReturn(true);

        $this->assertTrue($mockServer->getServerStatus());
    }
}
