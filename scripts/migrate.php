#!/usr/bin/env php
<?php

// Simple CLI to run DB migrations

require_once __DIR__ . '/../app/core/ConfigLoader.php';
require_once __DIR__ . '/../app/core/DatabaseConnector.php';
require_once __DIR__ . '/../app/core/MigrationRunner.php';

use App\Core\ConfigLoader;
use App\Core\DatabaseConnector;
use App\Core\MigrationRunner;

function printUsage()
{
    echo "\nJilo Web - Database Migrations\n";
    echo "Usage:\n";
    echo "  php scripts/migrate.php status     # Show pending and applied migrations\n";
    echo "  php scripts/migrate.php up         # Apply all pending migrations\n";
    echo "\n";
}

$action = $argv[1] ?? 'status';

try {
    // Load configuration to connect to DB
    $config = ConfigLoader::loadConfig([
        __DIR__ . '/../app/config/jilo-web.conf.php',
        __DIR__ . '/../jilo-web.conf.php',
        '/srv/jilo-web/jilo-web.conf.php',
        '/opt/jilo-web/jilo-web.conf.php',
    ]);

    $db = DatabaseConnector::connect($config);
    $migrationsDir = realpath(__DIR__ . '/../doc/database/migrations');
    if ($migrationsDir === false) {
        fwrite(STDERR, "Migrations directory not found: doc/database/migrations\n");
        exit(1);
    }

    $runner = new MigrationRunner($db, $migrationsDir);

    if ($action === 'status') {
        $all = $runner->listAllMigrations();
        $applied = $runner->listAppliedMigrations();
        $pending = $runner->listPendingMigrations();

        echo "All migrations (" . count($all) . "):\n";
        foreach ($all as $m) echo "  - $m\n";
        echo "\nApplied (" . count($applied) . "):\n";
        foreach ($applied as $m) echo "  - $m\n";
        echo "\nPending (" . count($pending) . "):\n";
        foreach ($pending as $m) echo "  - $m\n";
        echo "\n";
        exit(0);
    } elseif ($action === 'up') {
        $pending = $runner->listPendingMigrations();
        if (empty($pending)) {
            echo "No pending migrations.\n";
            exit(0);
        }
        echo "Applying " . count($pending) . " migration(s):\n";
        foreach ($pending as $m) echo "  - $m\n";
        $applied = $runner->applyPendingMigrations();
        echo "\nApplied successfully: " . count($applied) . "\n";
        exit(0);
    } else {
        printUsage();
        exit(1);
    }
} catch (Throwable $e) {
    fwrite(STDERR, "Migration error: " . $e->getMessage() . "\n");
    exit(1);
}
