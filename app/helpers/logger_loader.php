<?php

/**
 * Returns a logger instance: plugin Log if available, otherwise NullLogger.
 *
 * @param mixed $database Database or DatabaseConnector instance.
 * @return mixed Logger instance with insertLog() method.
 */
function getLoggerInstance($database) {
    if (class_exists('Log')) {
        return new Log($database);
    }
    require_once __DIR__ . '/../core/NullLogger.php';
    return new \App\Core\NullLogger();
}
