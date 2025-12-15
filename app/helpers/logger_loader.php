<?php

/**
 * Returns a logger instance: plugin Log if available, otherwise NullLogger.
 *
 * @param mixed $database Database or DatabaseConnector instance.
 * @return mixed Logger instance with PSR-3 log() compatible method.
 */
function getLoggerInstance($database) {
    if (class_exists('Log')) {
        return new Log($database);
    }
    require_once __DIR__ . '/../core/NullLogger.php';
    return new \App\Core\NullLogger();
}

if (!function_exists('app_log')) {
    /**
     * Lightweight logging helper that prefers the plugin logger but falls back to NullLogger.
     */
    function app_log(string $level, string $message, array $context = []): void {
        global $logObject;

        if (isset($logObject) && is_object($logObject) && method_exists($logObject, 'log')) {
            $logObject->log($level, $message, $context);
            return;
        }

        static $fallbackLogger = null;
        if ($fallbackLogger === null) {
            require_once __DIR__ . '/../core/NullLogger.php';
            $fallbackLogger = new \App\Core\NullLogger();
        }

        $fallbackLogger->log($level, $message, $context);
    }
}
