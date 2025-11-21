<?php

namespace App\Core;

use PDO;
use Exception;

class MigrationRunner
{
    private PDO $pdo;
    private string $migrationsDir;
    private string $driver;
    private bool $isSqlite = false;

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
                $this->recordMigration($migration, $trimmedSql, $hash);
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

    private function recordMigration(string $name, string $content, string $hash): void
    {
        $timestampExpr = $this->isSqlite ? "datetime('now')" : 'NOW()';
        $sql = "INSERT INTO migrations (migration, applied_at, content_hash, content) VALUES (:migration, {$timestampExpr}, :hash, :content)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':migration' => $name,
            ':hash' => $hash,
                ':content' => $content === '' ? null : $content,
        ]);
    }
}
