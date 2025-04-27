<?php

/**
 * Log wrapper that delegates to plugin Log or NullLogger fallback.
 * Used when code does require_once '../app/classes/log.php'.
 */

// If there is already a Log plugin loaded
if (class_exists('Log')) {
    return;
}

// Load fallback NullLogger
require_once __DIR__ . '/../core/NullLogger.php';

class Log {
    private $logger;

    /**
     * @param mixed $database Database or DatabaseConnector instance
     */
    public function __construct($database) {
        global $logObject;
        if (isset($logObject) && method_exists($logObject, 'insertLog')) {
            $this->logger = $logObject;
        } else {
            $this->logger = new \App\Core\NullLogger();
        }
    }

    /**
     * PSR-3 compatible log method
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function log(string $level, string $message, array $context = []): void {
        $this->logger->log($level, $message, $context);
    }
}
