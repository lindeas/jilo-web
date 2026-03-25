<?php

namespace Unit\Plugins;

use PDO;
use PHPUnit\Framework\TestCase;
use App\Core\PluginManager;
use App\App;

require_once __DIR__ . '/../../../app/core/App.php';
require_once __DIR__ . '/../../../app/core/PluginManager.php';

class PluginManagerTest extends TestCase
{
    /**
     * Register plugin bootstraps closures into global hook arrays, which PHPUnit
     * cannot serialize when backing up globals. Disable the backup for this test
     * suite so we can exercise plugins that rely on closures.
     */
    protected $backupGlobals = false;
    protected $backupStaticAttributes = false;

    private static $pdo;
    private static $originalTables = [];
    private static $testPlugin = 'register';
    private static $tablePattern = 'register_%';

    public static function setUpBeforeClass(): void
    {
        // Use centralized test configuration
        $dbConfig = test_db_config();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8',
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['dbname']
        );
        self::$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Setup test database schema
        setupTestDatabaseSchema(self::$pdo);

        // Store original plugin-owned tables (if any) to clean up later
        self::$originalTables = self::listPluginTables();

        // Set up App::db() for PluginManager (it now uses App API)
        App::set('db', self::$pdo);
        // Also set $GLOBALS['db'] for legacy fallback tests
        $GLOBALS['db'] = self::$pdo;

        // Initialize PluginManager catalog
        $pluginsDir = __DIR__ . '/../../../plugins';
        PluginManager::load($pluginsDir);
    }

    public function testDatabasePluginStateManagement(): void
    {
        // Test initial state
        $initialState = PluginManager::isEnabled(self::$testPlugin);
        $this->assertIsBool($initialState, 'PluginManager::isEnabled should return boolean');

        // Test enabling plugin via database
        $enableResult = PluginManager::setEnabled(self::$testPlugin, true);
        $this->assertTrue($enableResult, 'Should be able to enable plugin');

        // Verify state is persisted in database
        $stmt = self::$pdo->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
        $key = 'plugin_enabled_' . self::$testPlugin;
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($result, 'Plugin state should be stored in database');
        $this->assertEquals('1', $result['value'], 'Enabled plugin should have value "1"');

        // Test isEnabled reads from database
        $this->assertTrue(PluginManager::isEnabled(self::$testPlugin), 'Plugin should be enabled after database update');

        // Test disabling plugin via database
        $disableResult = PluginManager::setEnabled(self::$testPlugin, false);
        $this->assertTrue($disableResult, 'Should be able to disable plugin');

        // Verify state is updated in database
        $stmt->execute([':key' => $key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($result, 'Plugin state should still be in database');
        $this->assertEquals('0', $result['value'], 'Disabled plugin should have value "0"');

        // Test isEnabled reads disabled state
        $this->assertFalse(PluginManager::isEnabled(self::$testPlugin), 'Plugin should be disabled after database update');
    }

    public function testPluginInstall(): void
    {
        // Ensure plugin is disabled first
        PluginManager::setEnabled(self::$testPlugin, false);

        $beforeTables = self::listPluginTables();

        $installResult = PluginManager::install(self::$testPlugin);
        $this->assertTrue($installResult, 'Plugin installation should succeed');

        $afterTables = self::listPluginTables();
        $this->assertSame($beforeTables, $afterTables, 'Register plugin should not create dedicated tables');
    }

    public function testPluginPurge(): void
    {
        // First install the plugin to have something to purge
        PluginManager::install(self::$testPlugin);

        // Test plugin purge
        $purgeResult = PluginManager::purge(self::$testPlugin);
        $this->assertTrue($purgeResult, 'Plugin purge should succeed');

        // Mark plugin as enabled so purge has work to do
        PluginManager::setEnabled(self::$testPlugin, true);
        $stmt = self::$pdo->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
        $key = 'plugin_enabled_' . self::$testPlugin;
        $stmt->execute([':key' => $key]);
        $this->assertEquals('1', $stmt->fetchColumn(), 'Plugin should be enabled before purge');

        // Verify plugin purge
        $purgeResult = PluginManager::purge(self::$testPlugin);
        $this->assertTrue($purgeResult, 'Plugin purge should succeed');

        $stmt->execute([':key' => $key]);
        $this->assertFalse($stmt->fetch(), 'Plugin settings should be removed after purge');

        $this->assertFalse(PluginManager::isEnabled(self::$testPlugin), 'Plugin should be disabled after purge');

        $this->assertSame(self::$originalTables, self::listPluginTables(), 'Register plugin should not leave residual tables');
    }

    public function testFallbackToManifestWhenDatabaseUnavailable(): void
    {
        // Temporarily unset both App::db() and global database connection
        $originalDb = $GLOBALS['db'] ?? null;
        unset($GLOBALS['db']);
        App::reset('db');

        // Test fallback to manifest
        $result = PluginManager::isEnabled(self::$testPlugin);
        $this->assertIsBool($result, 'Should fallback to manifest when database unavailable');

        // Restore database connection
        $GLOBALS['db'] = $originalDb;
        App::set('db', self::$pdo);
    }

    public static function tearDownAfterClass(): void
    {
        // Clean up test data
        if (self::$pdo) {
            // Remove plugin settings
            $stmt = self::$pdo->prepare('DELETE FROM settings WHERE `key` LIKE :pattern');
            $stmt->execute([':pattern' => 'plugin_enabled_' . self::$testPlugin]);

            $currentTables = self::listPluginTables();
            if (!empty($currentTables)) {
                self::$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($currentTables as $table) {
                    if (!in_array($table, self::$originalTables, true)) {
                        self::$pdo->exec("DROP TABLE IF EXISTS `$table`");
                    }
                }
                self::$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }

            // Clean up PDO connection to prevent serialization errors
            self::$pdo = null;
        }

        // Reset App state
        App::reset('db');
    }

    private static function listPluginTables(): array
    {
        if (!self::$pdo) {
            return [];
        }

        $pattern = str_replace("'", "\\'", self::$tablePattern);
        $stmt = self::$pdo->query("SHOW TABLES LIKE '$pattern'");
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
}
