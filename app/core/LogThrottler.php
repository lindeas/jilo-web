<?php

namespace App\Core;

require_once __DIR__ . '/Settings.php';

class LogThrottler
{
    /**
     * Log a message no more than once per interval.
     *
     * @param object $logger Logger implementing log($level, $message, array $context)
     * @param mixed $db PDO or DatabaseConnector for Settings
     * @param string $key Unique key for throttling (e.g. migrations_pending)
     * @param int $intervalSeconds Minimum seconds between logs
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     */
    public static function logThrottled($logger, $db, string $key, int $intervalSeconds, string $level, string $message, array $context = []): void
    {
        if (!is_object($logger) || !method_exists($logger, 'log')) {
            return;
        }

        $settings = null;
        $shouldLog = true;
        $settingsKey = 'log_throttle_' . $key;

        try {
            $settings = new Settings($db);
            $lastLogged = $settings->get($settingsKey);
            if ($lastLogged) {
                $lastTimestamp = strtotime($lastLogged);
                if ($lastTimestamp !== false && (time() - $lastTimestamp) < $intervalSeconds) {
                    $shouldLog = false;
                }
            }
        } catch (\Throwable $e) {
            $settings = null;
        }

        if ($shouldLog) {
            $logger->log($level, $message, $context);
            if ($settings) {
                $settings->set($settingsKey, date('Y-m-d H:i:s'));
            }
        }
    }
}
