<?php

namespace App\Core;

require_once __DIR__ . '/NullLogger.php';
require_once __DIR__ . '/MigrationException.php';

use PDO;
use Exception;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsDir;
    private string $driver;
    private bool $isSqlite = false;
    private $logger;
    private array $lastResults = [];

    /**
     * @param mixed $db Either a PDO instance or the application's Database wrapper
     * @param string $migrationsDir Directory containing .sql migrations
     */
    public function __construct($db, string $migrationsDir)
    {
        // Normalize to PDO
        if ($db instanceof PDO) {
            $this->pdo = $db;
        } elseif (is_object($db) && method_exists($db, 'getConnection')) {
            $pdo = $db->getConnection();
            if (!$pdo instanceof PDO) {
                throw new Exception('Database wrapper did not return a PDO instance');
            }
            $this->pdo = $pdo;
        } else {
            $type = is_object($db) ? get_class($db) : gettype($db);
            throw new Exception("Unsupported database type: {$type}");
        }
        $this->migrationsDir = rtrim($migrationsDir, '/');
        if (!is_dir($this->migrationsDir)) {
            throw new Exception("Migrations directory not found: {$this->migrationsDir}");
        }
        $this->driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->isSqlite = ($this->driver === 'sqlite');
        $this->ensureMigrationsTable();
        $this->ensureMigrationColumns();
        $this->initializeLogger();
    }

    private function ensureMigrationsTable(): void
    {
        if ($this->isSqlite) {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                applied_at TEXT NOT NULL,
                content_hash TEXT NULL,
                content TEXT NULL
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME NOT NULL,
                content_hash CHAR(64) NULL,
                content LONGTEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }
        $this->pdo->exec($sql);
    }

    private function ensureMigrationColumns(): void
    {
        $this->ensureColumnExists(
            'content_hash',
            $this->isSqlite ? "ALTER TABLE migrations ADD COLUMN content_hash TEXT NULL" : "ALTER TABLE migrations ADD COLUMN content_hash CHAR(64) NULL DEFAULT NULL AFTER applied_at"
        );
        $this->ensureColumnExists(
            'content',
            $this->isSqlite ? "ALTER TABLE migrations ADD COLUMN content TEXT NULL" : "ALTER TABLE migrations ADD COLUMN content LONGTEXT NULL DEFAULT NULL AFTER content_hash"
        );
        $this->ensureColumnExists(
            'result',
            $this->isSqlite ? "ALTER TABLE migrations ADD COLUMN result TEXT NULL" : "ALTER TABLE migrations ADD COLUMN result LONGTEXT NULL DEFAULT NULL AFTER content"
        );
    }

    private function ensureColumnExists(string $column, string $alterSql): void
    {
        if ($this->columnExists('migrations', $column)) {
            return;
        }
        $this->pdo->exec($alterSql);
    }

    private function columnExists(string $table, string $column): bool
    {
        if ($this->isSqlite) {
            $stmt = $this->pdo->query("PRAGMA table_info({$table})");
            $columns = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            foreach ($columns as $col) {
                if (($col['name'] ?? '') === $column) {
                    return true;
                }
            }
            return false;
        }

        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM {$table} LIKE :column");
        $stmt->execute([':column' => $column]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listAllMigrations(): array
    {
        $files = glob($this->migrationsDir . '/*.sql');
        sort($files, SORT_NATURAL);
        return array_map('basename', $files);
    }

    public function listAppliedMigrations(): array
    {
        $stmt = $this->pdo->query('SELECT migration FROM migrations ORDER BY migration ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function listPendingMigrations(): array
    {
        $all = $this->listAllMigrations();
        $applied = $this->listAppliedMigrations();
        $pending = array_values(array_diff($all, $applied));
        return $this->sortMigrations($pending);
    }

    public function hasPendingMigrations(): bool
    {
        return count($this->listPendingMigrations()) > 0;
    }

    public function applyPendingMigrations(): array
    {
        return $this->runMigrations($this->listPendingMigrations());
    }

    public function applyNextMigration(): array
    {
        $pending = $this->listPendingMigrations();
        if (empty($pending)) {
            return [];
        }
        return $this->runMigrations([reset($pending)]);
    }

    public function applyMigrationByName(string $migration): array
    {
        $pending = $this->listPendingMigrations();
        if (!in_array($migration, $pending, true)) {
            return [];
        }
        return $this->runMigrations([$migration]);
    }

    private function runMigrations(array $migrations): array
    {
        $appliedNow = [];
        if (empty($migrations)) {
            return $appliedNow;
        }
        $this->lastResults = [];

        try {
            $this->pdo->beginTransaction();
            foreach ($migrations as $migration) {
                try {
                    $path = $this->migrationsDir . '/' . $migration;
                    $sql = file_get_contents($path);
                    if ($sql === false) {
                        throw new Exception("Unable to read migration file: {$migration}");
                    }
                    $trimmedSql = trim($sql);
                    $hash = hash('sha256', $trimmedSql);

                    if ($this->contentHashExists($hash)) {
                        $this->recordMigration($migration, $trimmedSql, $hash);
                        $appliedNow[] = $migration;
                        continue;
                    }

                    $statements = $this->splitStatements($trimmedSql);
                    foreach ($statements as $stmtSql) {
                        if ($stmtSql === '') {
                            continue;
                        }
                        $this->pdo->exec($stmtSql);
                    }
                    $statementCount = count($statements);
                    $resultMessage = sprintf('Migration "%s" applied successfully (%d statement%s).', $migration, $statementCount, $statementCount === 1 ? '' : 's');
                    $this->lastResults[$migration] = [
                        'content' => $trimmedSql,
                        'message' => $resultMessage,
                        'is_test' => $this->isTestMigration($migration)
                    ];
                    if ($this->isTestMigration($migration)) {
                        $appliedNow[] = $migration;
                        $this->logger->log('info', $resultMessage . ' (test migration)', ['scope' => 'system', 'migration' => $migration]);
                        $this->cleanupTestMigrationFile($migration);
                    } else {
                        $this->recordMigration($migration, $trimmedSql, $hash, $resultMessage);
                        $appliedNow[] = $migration;
                        $this->logger->log('info', $resultMessage, ['scope' => 'system', 'migration' => $migration]);
                    }
                } catch (Exception $migrationException) {
                    throw new MigrationException($migration, $migrationException->getMessage(), $migrationException);
                }
            }
            $this->pdo->commit();
        } catch (MigrationException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->logger->log('error', sprintf('Migration "%s" failed: %s', $e->getMigration(), $e->getMessage()), ['scope' => 'system', 'migration' => $e->getMigration()]);
            throw $e;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->logger->log('error', 'Migration run failed: ' . $e->getMessage(), ['scope' => 'system']);
            throw $e;
        }

        return $appliedNow;
    }

    private function splitStatements(string $sql): array
    {
        if ($sql === '') {
            return [];
        }
        return array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
    }

    private function contentHashExists(string $hash): bool
    {
        if ($hash === '') {
            return false;
        }
        $stmt = $this->pdo->prepare('SELECT 1 FROM migrations WHERE content_hash = :hash LIMIT 1');
        $stmt->execute([':hash' => $hash]);
        return (bool)$stmt->fetchColumn();
    }

    private function recordMigration(string $name, string $content, string $hash, ?string $result = null): void
    {
        $timestampExpr = $this->isSqlite ? "datetime('now')" : 'NOW()';
        $sql = "INSERT INTO migrations (migration, applied_at, content_hash, content, result) VALUES (:migration, {$timestampExpr}, :hash, :content, :result)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':migration' => $name,
            ':hash' => $hash,
            ':content' => $content === '' ? null : $content,
            ':result' => $result,
        ]);
    }

    private function sortMigrations(array $items): array
    {
        usort($items, static function ($a, $b) {
            $aTest = strpos($a, '_test_migration') !== false;
            $bTest = strpos($b, '_test_migration') !== false;
            if ($aTest === $bTest) {
                return strcmp($a, $b);
            }
            return $aTest ? -1 : 1;
        });
        return $items;
    }

    private function isTestMigration(string $migration): bool
    {
        return strpos($migration, '_test_migration') !== false;
    }

    private function cleanupTestMigrationFile(string $migration): void
    {
        $path = $this->migrationsDir . '/' . $migration;
        if (is_file($path)) {
            @unlink($path);
        }
        $stmt = $this->pdo->prepare('DELETE FROM migrations WHERE migration = :migration');
        $stmt->execute([':migration' => $migration]);
    }

    public function markMigrationApplied(string $migration, ?string $note = null): bool
    {
        $path = $this->migrationsDir . '/' . $migration;
        $content = '';
        if (is_file($path)) {
            $fileContent = file_get_contents($path);
            if ($fileContent !== false) {
                $content = trim($fileContent);
            }
        }
        $hash = $content === '' ? '' : hash('sha256', $content);
        if ($hash !== '' && $this->contentHashExists($hash)) {
            return true;
        }

        $result = $note ?? 'Marked as applied manually.';
        $this->recordMigration($migration, $content, $hash, $result);
        return true;
    }

    public function skipMigration(string $migration): bool
    {
        $source = $this->migrationsDir . '/' . $migration;
        if (!is_file($source)) {
            return false;
        }
        $skippedDir = $this->migrationsDir . '/skipped';
        if (!is_dir($skippedDir)) {
            if (!mkdir($skippedDir, 0775, true) && !is_dir($skippedDir)) {
                throw new Exception('Unable to create skipped migrations directory.');
            }
        }
        $destination = $skippedDir . '/' . $migration;
        if (rename($source, $destination)) {
            return true;
        }
        return false;
    }

    private function initializeLogger(): void
    {
        $logger = $GLOBALS['logObject'] ?? null;
        if (is_object($logger) && method_exists($logger, 'log')) {
            $this->logger = $logger;
        } else {
            $this->logger = new NullLogger();
        }
    }

    public function getMigrationRecord(string $migration): ?array
    {
        $stmt = $this->pdo->prepare('SELECT migration, applied_at, content, result FROM migrations WHERE migration = :migration LIMIT 1');
        $stmt->execute([':migration' => $migration]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLastResults(): array
    {
        return $this->lastResults;
    }
}
