<?php

namespace App\Core;

use PDO;
use Exception;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsDir;

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
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL UNIQUE,
                applied_at TEXT NOT NULL
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                applied_at DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }
        $this->pdo->exec($sql);
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
        return array_values(array_diff($all, $applied));
    }

    public function hasPendingMigrations(): bool
    {
        return count($this->listPendingMigrations()) > 0;
    }

    public function applyPendingMigrations(): array
    {
        $pending = $this->listPendingMigrations();
        $appliedNow = [];
        if (empty($pending)) {
            return $appliedNow;
        }

        try {
            $this->pdo->beginTransaction();
            foreach ($pending as $migration) {
                $path = $this->migrationsDir . '/' . $migration;
                $sql = file_get_contents($path);
                if ($sql === false) {
                    throw new Exception("Unable to read migration file: {$migration}");
                }
                // Split on ; at line ends, but allow inside procedures? Keep simple for our use-cases
                $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
                foreach ($statements as $stmtSql) {
                    if ($stmtSql === '') continue;
                    $this->pdo->exec($stmtSql);
                }
                $ins = $this->pdo->prepare('INSERT INTO migrations (migration, applied_at) VALUES (:m, NOW())');
                $ins->execute([':m' => $migration]);
                $appliedNow[] = $migration;
            }
            $this->pdo->commit();
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }

        return $appliedNow;
    }
}
