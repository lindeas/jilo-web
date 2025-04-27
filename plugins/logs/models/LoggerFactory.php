<?php

/**
 * LoggerFactory for Logger Plugin.
 *
 * Responsible for auto-migration and creating the Log instance.
 */
class LoggerFactory
{
    /**
     * @param object $db Database connector instance.
     * @return array [Log $logger, string $userIP]
     */
    public static function create($db): array
    {
        // Auto-migration: ensure log table exists
        $pdo = $db->getConnection();
        $migrationFile = __DIR__ . '/../migrations/create_log_table.sql';
        if (file_exists($migrationFile)) {
            $sql = file_get_contents($migrationFile);
            $pdo->exec($sql);
        }

        // Load models and core IP helper
        require_once __DIR__ . '/Log.php';
        require_once __DIR__ . '/../../../app/helpers/ip_helper.php';

        // Instantiate logger and retrieve user IP
        $logger = new \Log($db);
        $userIP = getUserIP();

        return [$logger, $userIP];
    }
}
