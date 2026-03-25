<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = test_db_config();
    }

    public function testDatabaseConnection()
    {
        $db = new Database($this->config);
        $this->assertNotNull($db->getConnection());
    }

    public function testMysqlAndMariadbEquivalence()
    {
        $baseConfig = test_db_config();

        $mysqlConfig = array_merge($baseConfig, ['type' => 'mysql']);
        $mariadbConfig = array_merge($baseConfig, ['type' => 'mariadb']);

        // Both should connect successfully
        $mysqlDb = new Database($mysqlConfig);
        $mariadbDb = new Database($mariadbConfig);

        $this->assertNotNull($mysqlDb->getConnection());
        $this->assertNotNull($mariadbDb->getConnection());
    }

    public function testInvalidDatabaseType()
    {
        require_once dirname(__DIR__, 3) . '/app/includes/errors.php';
        global $config;
        $config = ['environment' => 'development'];

        $invalidConfig = array_merge(test_db_config(), ['type' => 'invalid']);

        try {
            $db = new Database($invalidConfig);
            $connection = $db->getConnection();
            $this->assertNull($connection, 'Connection should be null for invalid database type');
        } catch (Exception $e) {
            // Either an exception or null connection is acceptable
            $this->assertTrue(true);
        }
    }

    public function testMysqlConnectionMissingData()
    {
        $config = test_db_config();
        $config['type'] = 'mysql';
        $config['user'] = '';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('MySQL connection data is missing');

        new Database($config);
    }

    public function testPrepareAndExecute()
    {
        $db = new Database($this->config);
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare("SELECT 1");
        $this->assertTrue($stmt->execute());
    }

    public function testTransaction()
    {
        $db = new Database($this->config);
        $pdo = $db->getConnection();
        $this->assertTrue($pdo->beginTransaction());
        $this->assertTrue($pdo->commit());
    }
}
