<?php

require_once dirname(__DIR__, 3) . '/app/classes/database.php';

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        // Set development environment for detailed errors
        global $config;
        $config['environment'] = 'development';

        $this->config = [
            'type' => 'sqlite',
            'dbFile' => ':memory:'
        ];
    }

    public function testDatabaseConnection()
    {
        $db = new Database($this->config);
        $this->assertNotNull($db->getConnection());
    }

    public function testMysqlAndMariadbEquivalence()
    {
        // Test that mysql and mariadb are treated the same
        $mysqlConfig = [
            'type' => 'mysql',
            'host' => 'invalid-host',
            'port' => 3306,
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'test'
        ];

        $mariadbConfig = [
            'type' => 'mariadb',
            'host' => 'invalid-host',
            'port' => 3306,
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'test'
        ];

        // Both should fail to connect and return null
        $mysqlDb = new Database($mysqlConfig);
        $this->assertNull($mysqlDb->getConnection());

        $mariaDb = new Database($mariadbConfig);
        $this->assertNull($mariaDb->getConnection());
    }

    public function testInvalidDatabaseType()
    {
        $invalidConfig = [
            'type' => 'invalid',
            'host' => 'localhost',
            'port' => 3306,
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'test'
        ];

        $invalidDb = new Database($invalidConfig);
        $this->assertNull($invalidDb->getConnection());
    }

    public function testMySQLConnectionMissingData()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('MySQL connection data is missing');

        $config = [
            'type' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'dbname' => 'test',
            // Missing user parameter
            'password' => 'test'
        ];
        new Database($config);
    }

    public function testPrepareAndExecute()
    {
        $db = new Database($this->config);

        // Create test table
        $db->execute('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');

        // Test prepare and execute
        $result = $db->execute('INSERT INTO test (name) VALUES (?)', ['test_name']);
        $this->assertEquals(1, $result->rowCount());

        // Verify insertion
        $result = $db->execute('SELECT name FROM test WHERE id = ?', [1]);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals('test_name', $row['name']);
    }

    public function testTransaction()
    {
        $db = new Database($this->config);

        // Create test table
        $db->execute('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');

        // Test successful transaction
        $db->beginTransaction();
        $db->execute('INSERT INTO test (name) VALUES (?)', ['transaction_test']);
        $db->commit();

        $result = $db->execute('SELECT COUNT(*) as count FROM test');
        $this->assertEquals(1, $result->fetch(PDO::FETCH_ASSOC)['count']);

        // Test rollback
        $db->beginTransaction();
        $db->execute('INSERT INTO test (name) VALUES (?)', ['rollback_test']);
        $db->rollBack();

        $result = $db->execute('SELECT COUNT(*) as count FROM test');
        $this->assertEquals(1, $result->fetch(PDO::FETCH_ASSOC)['count']);
    }
}
