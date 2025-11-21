<?php
/**
 * Admin tools controller
 *
 * Allows superusers to:
 * - Enable/disable maintenance mode
 * - Run database migrations
 */

// Security and CSRF
require_once __DIR__ . '/../helpers/security.php';
$security = SecurityHelper::getInstance();

// Must be logged in
if (!Session::isValidSession()) {
    header('Location: ' . $app_root . '?page=login');
    exit;
}

// Must be superuser
$canAdmin = false;
if (isset($userId) && isset($userObject) && method_exists($userObject, 'hasRight')) {
    $canAdmin = ($userId === 1) || (bool)$userObject->hasRight($userId, 'superuser');
}
if (!$canAdmin) {
    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
    header('Location: ' . $app_root);
    exit;
}

// Get any old feedback messages
include_once '../app/helpers/feedback.php';

// Handle actions
$action = $_POST['action'] ?? '';

// AJAX: view migration file contents
if ($action === 'read_migration') {
    header('Content-Type: application/json');

    // CSRF check
    $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? $csrfHeader;
    if (!$security->verifyCsrfToken($csrfToken)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    // Permission check
    if (!$canAdmin) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    // Validate filename to avoid traversal
    $filename = basename($_POST['filename'] ?? '');
    if ($filename === '' || !preg_match('/^[A-Za-z0-9_\-]+\.sql$/', $filename)) {
        echo json_encode(['success' => false, 'error' => 'Invalid filename']);
        exit;
    }

    $migrationsDir = __DIR__ . '/../../doc/database/migrations';
    $path = realpath($migrationsDir . '/' . $filename);
    if ($path === false || strpos($path, realpath($migrationsDir)) !== 0) {
        echo json_encode(['success' => false, 'error' => 'File not found']);
        exit;
    }

    $content = @file_get_contents($path);
    if ($content === false) {
        echo json_encode(['success' => false, 'error' => 'Could not read file']);
        exit;
    }

    echo json_encode(['success' => true, 'name' => $filename, 'content' => $content]);
    exit;
}
if ($action !== '') {
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        Feedback::flash('SECURITY', 'CSRF_INVALID');
        header('Location: ' . $app_root . '?page=admin-tools');
        exit;
    }

    try {
        if ($action === 'maintenance_on') {
            require_once __DIR__ . '/../core/Maintenance.php';
            $msg = trim($_POST['maintenance_message'] ?? '');
            \App\Core\Maintenance::enable($msg);
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode enabled.', true);
        } elseif ($action === 'maintenance_off') {
            require_once __DIR__ . '/../core/Maintenance.php';
            \App\Core\Maintenance::disable();
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode disabled.', true);
        } elseif ($action === 'migrate_up') {
            require_once __DIR__ . '/../core/MigrationRunner.php';
            $migrationsDir = __DIR__ . '/../../doc/database/migrations';
            $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
            $applied = $runner->applyPendingMigrations();

            // Clean up any test migration files after applying
            if (!empty($applied)) {
                foreach ($applied as $migration) {
                    if (strpos($migration, '_test_migration.sql') !== false) {
                        $filepath = $migrationsDir . '/' . $migration;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                        // Remove from database migrations table to leave no trace
                        $stmt = $db->getConnection()->prepare("DELETE FROM migrations WHERE migration = :migration");
                        $stmt->execute([':migration' => $migration]);
                    }
                }
            }

            if (empty($applied)) {
                Feedback::flash('NOTICE', 'DEFAULT', 'No pending migrations.', true);
            } else {
                Feedback::flash('NOTICE', 'DEFAULT', 'Applied migrations: ' . implode(', ', $applied), true);
            }
        } elseif ($action === 'migrate_apply_one') {
            require_once __DIR__ . '/../core/MigrationRunner.php';
            $migrationsDir = __DIR__ . '/../../doc/database/migrations';
            $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
            $migrationName = trim($_POST['migration_name'] ?? '');
            $applied = $migrationName !== '' ? $runner->applyMigrationByName($migrationName) : [];

            if (empty($applied)) {
                Feedback::flash('NOTICE', 'DEFAULT', 'No pending migrations.', true);
                $_SESSION['migration_modal_result'] = [
                    'name' => $migrationName ?: null,
                    'status' => 'info',
                    'message' => 'No pending migrations to apply.'
                ];
                if (!empty($migrationName)) {
                    $_SESSION['migration_modal_open'] = $migrationName;
                }
            } else {
                Feedback::flash('NOTICE', 'DEFAULT', 'Applied migration: ' . implode(', ', $applied), true);
                $_SESSION['migration_modal_result'] = [
                    'name' => $applied[0],
                    'status' => 'success',
                    'message' => 'Migration ' . $applied[0] . ' applied successfully.'
                ];
                $_SESSION['migration_modal_open'] = $applied[0];
            }
        } elseif ($action === 'create_test_migration') {
            $migrationsDir = __DIR__ . '/../../doc/database/migrations';
            $timestamp = date('Ymd_His');
            $filename = $timestamp . '_test_migration.sql';
            $filepath = $migrationsDir . '/' . $filename;

            // Create a simple test migration that adds a test setting (MariaDB compatible)
            $testMigration = "-- Test migration for testing purposes\n";
            $testMigration .= "-- This migration adds a test setting to settings table\n";
            $testMigration .= "INSERT INTO settings (`key`, `value`, updated_at) VALUES ('test_migration_flag', '1', NOW())\n";
            $testMigration .= "ON DUPLICATE KEY UPDATE `value` = '1', updated_at = NOW();\n";

            if (file_put_contents($filepath, $testMigration)) {
                Feedback::flash('NOTICE', 'DEFAULT', 'Test migration created: ' . $filename, true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', 'Failed to create test migration file', false);
            }
        } elseif ($action === 'clear_test_migrations') {
            $migrationsDir = __DIR__ . '/../../doc/database/migrations';

            // Find and remove test migration files
            $testFiles = glob($migrationsDir . '/*_test_migration.sql');
            $removedCount = 0;

            foreach ($testFiles as $file) {
                $filename = basename($file);
                if (file_exists($file)) {
                    unlink($file);
                    $removedCount++;
                }
                // Remove from database migrations table to leave no trace
                $stmt = $db->getConnection()->prepare("DELETE FROM migrations WHERE migration = :migration");
                $stmt->execute([':migration' => $filename]);
            }

            if ($removedCount > 0) {
                Feedback::flash('NOTICE', 'DEFAULT', 'Cleared ' . $removedCount . ' test migration(s)', true);
            } else {
                Feedback::flash('NOTICE', 'DEFAULT', 'No test migrations to clear', true);
            }
        }
    } catch (Throwable $e) {
        Feedback::flash('ERROR', 'DEFAULT', 'Action failed: ' . $e->getMessage(), false);
    }

    header('Location: ' . $app_root . '?page=admin-tools');
    exit;
}

// Prepare data for view
require_once __DIR__ . '/../core/Maintenance.php';
$maintenance_enabled = \App\Core\Maintenance::isEnabled();
$maintenance_message = \App\Core\Maintenance::getMessage();

require_once __DIR__ . '/../core/MigrationRunner.php';
$migrationsDir = __DIR__ . '/../../doc/database/migrations';
$pending = [];
$applied = [];
$next_pending = null;
$migration_contents = [];
$test_migrations_exist = false;
$migration_modal_result = $_SESSION['migration_modal_result'] ?? null;
if (isset($_SESSION['migration_modal_result'])) {
    unset($_SESSION['migration_modal_result']);
}
$modal_to_open = $_SESSION['migration_modal_open'] ?? null;
if (isset($_SESSION['migration_modal_open'])) {
    unset($_SESSION['migration_modal_open']);
}
$migration_records = [];
try {
    $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
    $pending = $runner->listPendingMigrations();
    $applied = $runner->listAppliedMigrations();

    $sortTestFirst = static function (array $items): array {
        usort($items, static function ($a, $b) {
            $aTest = strpos($a, '_test_migration') !== false;
            $bTest = strpos($b, '_test_migration') !== false;
            if ($aTest === $bTest) {
                return strcmp($a, $b);
            }
            return $aTest ? -1 : 1;
        });
        return $items;
    };

    $pending = $sortTestFirst($pending);
    $applied = $sortTestFirst($applied);
    $next_pending = $pending[0] ?? null;

    // Check if any test migrations exist
    $test_migrations_exist = !empty(glob($migrationsDir . '/*_test_migration.sql'));

    // Preload contents for billing-admin style modals
    $all = array_unique(array_merge($pending, $applied));
    foreach ($all as $fname) {
        $path = realpath($migrationsDir . '/' . $fname);
        $content = false;
        if ($path && strpos($path, realpath($migrationsDir)) === 0) {
            $content = @file_get_contents($path);
        }

        $record = $runner->getMigrationRecord($fname);
        if ($record) {
            $migration_records[$fname] = $record;
        }

        if ($content !== false && $content !== null) {
            $migration_contents[$fname] = $content;
        } elseif (!empty($record['content'])) {
            $migration_contents[$fname] = $record['content'];
        }
    }
} catch (Throwable $e) {
    // show error in the page
    $migration_error = $e->getMessage();
}

// CSRF token
$csrf_token = $security->generateCsrfToken();

// Load the template
include __DIR__ . '/../templates/admin-tools.php';
