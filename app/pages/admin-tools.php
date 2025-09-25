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
            if (empty($applied)) {
                Feedback::flash('NOTICE', 'DEFAULT', 'No pending migrations.', true);
            } else {
                Feedback::flash('NOTICE', 'DEFAULT', 'Applied migrations: ' . implode(', ', $applied), true);
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
$migration_contents = [];
try {
    $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
    $pending = $runner->listPendingMigrations();
    $applied = $runner->listAppliedMigrations();
    // Preload contents for billing-admin style modals
    $all = array_unique(array_merge($pending, $applied));
    foreach ($all as $fname) {
        $path = realpath($migrationsDir . '/' . $fname);
        if ($path && strpos($path, realpath($migrationsDir)) === 0) {
            $content = @file_get_contents($path);
            if ($content !== false) {
                $migration_contents[$fname] = $content;
            }
        }
    }
} catch (Throwable $e) {
    // show error in the page
    $migration_error = $e->getMessage();
}

// CSRF token
$csrf_token = $security->generateCsrfToken();

// Get any new feedback messages
include __DIR__ . '/../helpers/feedback.php';

// Load the template
include __DIR__ . '/../templates/admin-tools.php';
