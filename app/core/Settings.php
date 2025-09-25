<?php

namespace App\Core;

use PDO;
use Exception;

class Settings
{
    private PDO $pdo;

    public function __construct($db)
    {
        if ($db instanceof PDO) {
            $this->pdo = $db;
        } elseif (is_object($db) && method_exists($db, 'getConnection')) {
            $pdo = $db->getConnection();
            if (!$pdo instanceof PDO) {
                throw new Exception('Settings: database wrapper did not return PDO');
            }
            $this->pdo = $pdo;
        } else {
            $type = is_object($db) ? get_class($db) : gettype($db);
            throw new Exception("Settings: unsupported database type: {$type}");
        }
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS settings (\n                `key` TEXT PRIMARY KEY,\n                `value` TEXT,\n                `updated_at` TEXT NOT NULL\n            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS settings (\n                `key` VARCHAR(191) NOT NULL PRIMARY KEY,\n                `value` TEXT NULL,\n                `updated_at` DATETIME NOT NULL\n            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }
        $this->pdo->exec($sql);
    }

    public function get(string $key, $default = null)
    {
        $stmt = $this->pdo->prepare('SELECT `value` FROM settings WHERE `key` = :k');
        $stmt->execute([':k' => $key]);
        $val = $stmt->fetchColumn();
        if ($val === false) return $default;
        return $val;
    }

    public function set(string $key, $value): bool
    {
        $stmt = $this->pdo->prepare('REPLACE INTO settings (`key`, `value`, `updated_at`) VALUES (:k, :v, NOW())');
        return (bool)$stmt->execute([':k' => $key, ':v' => $value]);
    }
}
