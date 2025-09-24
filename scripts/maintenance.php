#!/usr/bin/env php
<?php

require_once __DIR__ . '/../app/core/Maintenance.php';

use App\Core\Maintenance;

function usage()
{
    echo "\nJilo Web - Maintenance Mode\n";
    echo "Usage:\n";
    echo "  php scripts/maintenance.php on  [message]  # Enable maintenance mode with optional message\n";
    echo "  php scripts/maintenance.php off           # Disable maintenance mode\n";
    echo "  php scripts/maintenance.php status        # Show maintenance status\n\n";
}

$cmd = $argv[1] ?? 'status';

try {
    switch ($cmd) {
        case 'on':
            $message = $argv[2] ?? '';
            if (Maintenance::enable($message)) {
                echo "Maintenance mode ENABLED" . ($message ? ": $message" : '') . "\n";
                exit(0);
            }
            fwrite(STDERR, "Failed to enable maintenance mode\n");
            exit(1);
        case 'off':
            if (Maintenance::disable()) {
                echo "Maintenance mode DISABLED\n";
                exit(0);
            }
            fwrite(STDERR, "Failed to disable maintenance mode\n");
            exit(1);
        case 'status':
        default:
            if (Maintenance::isEnabled()) {
                $msg = Maintenance::getMessage();
                echo "Maintenance: ON" . ($msg ? " - $msg" : '') . "\n";
            } else {
                echo "Maintenance: OFF\n";
            }
            exit(0);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
