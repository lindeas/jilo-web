<?php

namespace App\Core;

use PDO;
use Exception;

class MigrationRunner
{
    private PDO $db;
    private string $migrationsDir;

    public function __construct(PDO $db, string $migrationsDir)
    {
        $this->db = $db;
        $this->migrationsDir = rtrim($migrationsDir, '/');
        if (!is_dir($this->migrationsDir)) {
            throw new Exception("Migrations directory not found: {$this->migrationsDir}");
        }
        $this->ensureMigrationsTable();
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            applied_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
    }

    public function listAllMigrations(): array
    {
        $files = glob($this->migrationsDir . '/*.sql');
        sort($files, SORT_NATURAL);
        return array_map('basename', $files);
    }

    public function listAppliedMigrations(): array
    {
        $stmt = $this->db->query('SELECT migration FROM migrations ORDER BY migration ASC');
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
            $this->db->beginTransaction();
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
                    $this->db->exec($stmtSql);
                }
                $ins = $this->db->prepare('INSERT INTO migrations (migration, applied_at) VALUES (:m, NOW())');
                $ins->execute([':m' => $migration]);
                $appliedNow[] = $migration;
            }
            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return $appliedNow;
    }
}
