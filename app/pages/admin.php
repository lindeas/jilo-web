<?php

/*
 * Admin control center
 *
 * Provides maintenance/migration tooling and exposes hook placeholders
 * so plugins can contribute additional sections, actions, and metrics.
 */

require_once __DIR__ . '/../core/Maintenance.php';
require_once __DIR__ . '/../core/MigrationRunner.php';
require_once '../app/helpers/security.php';
include_once '../app/helpers/feedback.php';

$security = SecurityHelper::getInstance();

if (!Session::isValidSession()) {
    header('Location: ' . $app_root . '?page=login');
    exit;
}

$canAdmin = false;
if (isset($userId) && isset($userObject) && method_exists($userObject, 'hasRight')) {
    $canAdmin = ($userId === 1) || (bool)$userObject->hasRight($userId, 'superuser');
}

if (!$canAdmin) {
    Feedback::flash('SECURITY', 'PERMISSION_DENIED');
    header('Location: ' . $app_root);
    exit;
}

$postAction = $_POST['action'] ?? '';
$queryAction = $_GET['action'] ?? '';
$action = $postAction ?: $queryAction;
$targetId = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
$sectionRegistry = [
    'overview' => ['label' => 'Overview', 'position' => 100, 'hook' => null, 'type' => 'core'],
    'maintenance' => ['label' => 'Maintenance', 'position' => 200, 'hook' => null, 'type' => 'core'],
    'migrations' => ['label' => 'Migrations', 'position' => 300, 'hook' => null, 'type' => 'core'],
];

$registerSection = static function(array $section) use (&$sectionRegistry): void {
    $key = strtolower(trim($section['key'] ?? ''));
    $label = trim((string)($section['label'] ?? ''));
    if ($key === '' || $label === '') {
        return;
    }

    $position = (int)($section['position'] ?? 900);
    $sectionRegistry[$key] = [
        'label' => $label,
        'position' => $position,
        'hook' => $section['hook'] ?? ('admin.' . $key . '.render'),
        'type' => $section['type'] ?? 'plugin',
    ];
};

// Hooks sections for plugins
do_hook('admin.sections.register', [
    'register' => $registerSection,
    'app_root' => $app_root,
    'sections' => &$sectionRegistry,
]);

uasort($sectionRegistry, static function(array $a, array $b): int {
    if ($a['position'] === $b['position']) {
        return strcmp($a['label'], $b['label']);
    }
    return $a['position'] <=> $b['position'];
});

if (empty($sectionRegistry)) {
    $sectionRegistry = [
        'overview' => ['label' => 'Overview', 'position' => 100, 'hook' => null, 'type' => 'core'],
    ];
}

$validSections = array_keys($sectionRegistry);

$buildAdminUrl = static function(string $section = 'overview') use ($app_root, &$sectionRegistry): string {
    if (!isset($sectionRegistry[$section])) {
        $section = array_key_first($sectionRegistry) ?? 'overview';
    }
    $suffix = $section !== 'overview' ? ('&section=' . urlencode($section)) : '';
    return $app_root . '?page=admin' . $suffix;
};

$sectionUrls = [];
foreach (array_keys($sectionRegistry) as $sectionKey) {
    $sectionUrls[$sectionKey] = $buildAdminUrl($sectionKey);
}

$requestedSection = strtolower(trim($_GET['section'] ?? 'overview'));
if (!isset($sectionRegistry[$requestedSection])) {
    $requestedSection = array_key_first($sectionRegistry) ?? 'overview';
}
$activeSection = $requestedSection;

$adminTabs = [];
foreach ($sectionRegistry as $key => $meta) {
    $adminTabs[$key] = [
        'label' => $meta['label'],
        'url' => $sectionUrls[$key],
        'hook' => $meta['hook'],
        'type' => $meta['type'],
        'position' => $meta['position'],
    ];
}

// Hooks section for plugins
$sectionStatePayload = \App\Core\HookDispatcher::applyFilters('admin.sections.state', [
    'sections' => $sectionRegistry,
    'state' => [],
    'db' => $db ?? null,
    'user_id' => $userId,
    'app_root' => $app_root,
]);
$sectionState = [];
if (is_array($sectionStatePayload)) {
    $sectionState = $sectionStatePayload['state'] ?? (is_array($sectionStatePayload) ? $sectionStatePayload : []);
}

$migrationsDir = __DIR__ . '/../../doc/database/migrations';

if ($postAction === 'read_migration') {
    header('Content-Type: application/json');
    $csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? $csrfHeader;
    if (!$security->verifyCsrfToken($csrfToken)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    if (!$canAdmin) {
        echo json_encode(['success' => false, 'error' => 'Permission denied']);
        exit;
    }

    $filename = basename($_POST['filename'] ?? '');
    if ($filename === '' || !preg_match('/^[A-Za-z0-9_\-]+\.sql$/', $filename)) {
        echo json_encode(['success' => false, 'error' => 'Invalid filename']);
        exit;
    }

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

// Hooks actions for plugins
if ($action !== '' && $action !== 'read_migration') {
    $customActionPayload = \App\Core\HookDispatcher::applyFilters('admin.actions.handle', [
        'handled' => false,
        'action' => $action,
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'request' => $_REQUEST,
        'security' => $security,
        'app_root' => $app_root,
        'build_admin_url' => $buildAdminUrl,
        'user_id' => $userId,
        'db' => $db ?? null,
        'target_id' => $targetId,
        'section_state' => $sectionState,
    ]);

    if (!empty($customActionPayload['handled'])) {
        return;
    }
}

if ($postAction !== '' && $postAction !== 'read_migration') {
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        Feedback::flash('SECURITY', 'CSRF_INVALID');
        header('Location: ' . $buildAdminUrl($activeSection));
        exit;
    }

    $postSection = strtolower(trim($_POST['section'] ?? $activeSection));
    if (!in_array($postSection, $validSections, true)) {
        $postSection = 'overview';
    }

    try {
        if ($postAction === 'maintenance_on') {
            $msg = trim($_POST['maintenance_message'] ?? '');
            \App\Core\Maintenance::enable($msg);
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode enabled.', true);
        } elseif ($postAction === 'maintenance_off') {
            \App\Core\Maintenance::disable();
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode disabled.', true);
        } elseif ($postAction === 'migrate_up') {
            $runner = new \App\Core\MigrationRunner($db, $migrationsDir);
            $applied = $runner->applyPendingMigrations();
            Feedback::flash('NOTICE', 'DEFAULT', empty($applied) ? 'No pending migrations.' : 'Applied migrations: ' . implode(', ', $applied), true);
        } elseif ($postAction === 'migrate_apply_one') {
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
        } elseif ($postAction === 'create_test_migration') {
            $timestamp = date('Ymd_His');
            $filename = $timestamp . '_test_migration.sql';
            $filepath = $migrationsDir . '/' . $filename;
            $testMigration = "-- Test migration for testing purposes\n";
            $testMigration .= "-- This migration adds a test setting to settings table\n";
            $testMigration .= "INSERT INTO settings (`key`, `value`, updated_at) VALUES ('test_migration_flag', '1', NOW())\n";
            $testMigration .= "ON DUPLICATE KEY UPDATE `value` = '1', updated_at = NOW();\n";
            if (file_put_contents($filepath, $testMigration)) {
                Feedback::flash('NOTICE', 'DEFAULT', 'Test migration created: ' . $filename, true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', 'Failed to create test migration file', false);
            }
        } elseif ($postAction === 'clear_test_migrations') {
            $testFiles = glob($migrationsDir . '/*_test_migration.sql') ?: [];
            $removedCount = 0;
            foreach ($testFiles as $file) {
                $filename = basename($file);
                if (file_exists($file)) {
                    unlink($file);
                    $removedCount++;
                }
                $stmt = $db->getConnection()->prepare('DELETE FROM migrations WHERE migration = :migration');
                $stmt->execute([':migration' => $filename]);
            }
            Feedback::flash('NOTICE', 'DEFAULT', $removedCount > 0 ? ('Cleared ' . $removedCount . ' test migration(s)') : 'No test migrations to clear', true);
        }
    } catch (Throwable $e) {
        Feedback::flash('ERROR', 'DEFAULT', 'Action failed: ' . $e->getMessage(), false);
    }

    header('Location: ' . $buildAdminUrl($postSection));
    exit;
}

$maintenance_enabled = \App\Core\Maintenance::isEnabled();
$maintenance_message = \App\Core\Maintenance::getMessage();

$pending = [];
$applied = [];
$next_pending = null;
$migration_contents = [];
$test_migrations_exist = false;
$migration_records = [];
$migration_error = null;

$migration_modal_result = $_SESSION['migration_modal_result'] ?? null;
if (isset($_SESSION['migration_modal_result'])) {
    unset($_SESSION['migration_modal_result']);
}
$modal_to_open = $_SESSION['migration_modal_open'] ?? null;
if (isset($_SESSION['migration_modal_open'])) {
    unset($_SESSION['migration_modal_open']);
}

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
    $test_migrations_exist = !empty(glob($migrationsDir . '/*_test_migration.sql'));

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
    $migration_error = $e->getMessage();
}

$overviewPillsPayload = \App\Core\HookDispatcher::applyFilters('admin.overview.pills', [
    'pills' => [],
    'sections' => $sectionRegistry,
    'section_state' => $sectionState,
    'app_root' => $app_root,
    'user_id' => $userId,
]);
$adminOverviewPills = [];
if (is_array($overviewPillsPayload)) {
    $adminOverviewPills = $overviewPillsPayload['pills'] ?? (is_array($overviewPillsPayload) ? $overviewPillsPayload : []);
}

$overviewStatusesPayload = \App\Core\HookDispatcher::applyFilters('admin.overview.statuses', [
    'statuses' => [],
    'sections' => $sectionRegistry,
    'section_state' => $sectionState,
    'app_root' => $app_root,
    'user_id' => $userId,
]);
$adminOverviewStatuses = [];
if (is_array($overviewStatusesPayload)) {
    $adminOverviewStatuses = $overviewStatusesPayload['statuses'] ?? (is_array($overviewStatusesPayload) ? $overviewStatusesPayload : []);
}

$csrf_token = $security->generateCsrfToken();

include '../app/templates/admin.php';
