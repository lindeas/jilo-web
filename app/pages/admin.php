<?php

/*
 * Admin control center
 *
 * Provides maintenance/migration tooling and exposes hook placeholders
 * so plugins can contribute additional sections, actions, and metrics.
 */

require_once APP_PATH . 'core/Maintenance.php';
require_once APP_PATH . 'core/MigrationRunner.php';
require_once APP_PATH . 'core/PluginManager.php';
require_once APP_PATH . 'helpers/feedback.php';
require_once APP_PATH . 'helpers/security.php';
require_once APP_PATH . 'helpers/datetime.php';

$security = SecurityHelper::getInstance();

if (!Session::isValidSession()) {
    header('Location: ' . $app_root . '?page=login');
    exit;
}

// Check if the user has admin permissions
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
    'plugins' => ['label' => 'Plugins', 'position' => 400, 'hook' => null, 'type' => 'core'],
];

// Register sections for plugins
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

// Get plugin catalog and list of loaded plugins
// with their dependencies
$pluginCatalog = \App\Core\PluginManager::getCatalog();
$pluginLoadedMap = \App\Core\PluginManager::getLoaded();
$pluginDependencyErrors = \App\Core\PluginManager::getDependencyErrors();

$normalizeDependencies = static function ($meta): array {
    $deps = $meta['dependencies'] ?? [];
    if (!is_array($deps)) {
        $deps = $deps === null || $deps === '' ? [] : [$deps];
    }
    $deps = array_map('trim', $deps);
    $deps = array_filter($deps, static function($dep) {
        return $dep !== '';
    });
    return array_values(array_unique($deps));
};

$pluginDependentsIndex = [];
foreach ($pluginCatalog as $slug => $info) {
    $deps = $normalizeDependencies($info['meta'] ?? []);
    foreach ($deps as $dep) {
        $pluginDependentsIndex[$dep][] = $slug;
    }
}

// Build plugin admin map with details, state and dependencies
$pluginAdminMap = [];
foreach ($pluginCatalog as $slug => $info) {
    $meta = $info['meta'] ?? [];
    $name = trim((string)($meta['name'] ?? $slug));
    $enabled = \App\Core\PluginManager::isEnabled($slug); // Use database setting
    $dependencies = $normalizeDependencies($meta);
    $dependents = array_values($pluginDependentsIndex[$slug] ?? []);
    $enabledDependents = array_values(array_filter($dependents, static function($depSlug) {
        return \App\Core\PluginManager::isEnabled($depSlug); // Use database setting
    }));
    $missingDependencies = array_values(array_filter($dependencies, static function($depSlug) use ($pluginCatalog) {
        return !isset($pluginCatalog[$depSlug]) || !\App\Core\PluginManager::isEnabled($depSlug); // Use database setting
    }));

    // Check for migration files and existing tables
    $migrationFiles = glob($info['path'] . '/migrations/*.sql');
    $hasMigration = !empty($migrationFiles);
    $existingTables = [];

    if ($hasMigration) {
        $db = \App\App::db();
        if ($db instanceof PDO) {
            $stmt = $db->query("SHOW TABLES");
            $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            foreach ($migrationFiles as $migrationFile) {
                $migrationContent = file_get_contents($migrationFile);
                foreach ($allTables as $table) {
                    if (strpos($migrationContent, $table) !== false) {
                        $existingTables[] = $table;
                    }
                }
            }
            $existingTables = array_unique($existingTables);
        }
    }

    $pluginAdminMap[$slug] = [
        'slug' => $slug,
        'name' => $name,
        'version' => (string)($meta['version'] ?? ''),
        'description' => (string)($meta['description'] ?? ''),
        'path' => $info['path'],
        'enabled' => $enabled,
        'loaded' => isset($pluginLoadedMap[$slug]),
        'dependencies' => $dependencies,
        'dependents' => $dependents,
        'enabled_dependents' => $enabledDependents,
        'missing_dependencies' => $missingDependencies,
        'dependency_errors' => $pluginDependencyErrors[$slug] ?? [],
        'can_enable' => !$enabled && empty($missingDependencies),
        'can_disable' => $enabled && empty($enabledDependents),
        'has_migration' => $hasMigration,
        'existing_tables' => $existingTables,
        'can_install' => $hasMigration && empty($existingTables),
    ];
}

$pluginAdminList = array_values($pluginAdminMap);
usort($pluginAdminList, static function(array $a, array $b): int {
    return strcmp(strtolower($a['name']), strtolower($b['name']));
});

$sectionState['plugins'] = [
    'plugins' => $pluginAdminList,
    'dependency_errors' => $pluginDependencyErrors,
    'plugin_index' => $pluginAdminMap,
];

// Prepare the DB migrations details
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
        // Maintenance actions
        if ($postAction === 'maintenance_on') {
            $msg = trim($_POST['maintenance_message'] ?? '');
            \App\Core\Maintenance::enable($msg);
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode enabled.', true);
        } elseif ($postAction === 'maintenance_off') {
            \App\Core\Maintenance::disable();
            Feedback::flash('NOTICE', 'DEFAULT', 'Maintenance mode disabled.', true);
        // DB migrations actions
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
        // Plugin actions
        } elseif ($postAction === 'plugin_enable' || $postAction === 'plugin_disable') {
            $slug = strtolower(trim($_POST['plugin'] ?? ''));
            if ($slug === '' || !isset($pluginAdminMap[$slug])) {
                Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
            } else {
                $pluginMeta = $pluginAdminMap[$slug];
                if ($postAction === 'plugin_enable') {
                    if (!$pluginMeta['can_enable']) {
                        $reason = 'Resolve missing dependencies before enabling this plugin.';
                        if (!empty($pluginMeta['missing_dependencies'])) {
                            $reason = 'Enable required plugins first: ' . implode(', ', $pluginMeta['missing_dependencies']);
                        }
                        Feedback::flash('ERROR', 'DEFAULT', $reason, false);
                    } elseif (!\App\Core\PluginManager::setEnabled($slug, true)) {
                        Feedback::flash('ERROR', 'DEFAULT', 'Failed to enable plugin. Check database connection and error logs.', false);
                    } else {
                        // Automatically install plugin tables when enabling
                        $installResult = \App\Core\PluginManager::install($slug);
                        if ($installResult) {
                            Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" enabled and installed successfully.', $pluginMeta['name']), true);
                        } else {
                            Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" enabled but installation failed. Check migration files.', $pluginMeta['name']), true);
                        }
                    }
                } else {
                    if (!$pluginMeta['can_disable']) {
                        $reason = 'Disable dependent plugins first: ' . implode(', ', $pluginMeta['enabled_dependents']);
                        Feedback::flash('ERROR', 'DEFAULT', $reason, false);
                    } elseif (!\App\Core\PluginManager::setEnabled($slug, false)) {
                        Feedback::flash('ERROR', 'DEFAULT', 'Failed to disable plugin. Check database connection and error logs.', false);
                    } else {
                        Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" disabled.', $pluginMeta['name']), true);
                    }
                }
            }
        // Plugin install action
        } elseif ($postAction === 'plugin_install') {
            $slug = strtolower(trim($_POST['plugin'] ?? ''));
            if ($slug === '' || !isset($pluginAdminMap[$slug])) {
                Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
            } else {
                if (\App\Core\PluginManager::install($slug)) {
                    Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" installed successfully.', $pluginAdminMap[$slug]['name']), true);
                } else {
                    Feedback::flash('ERROR', 'DEFAULT', 'Plugin installation failed. Check migration files.', false);
                }
            }
        // Plugin purge action
        } elseif ($postAction === 'plugin_purge') {
            $slug = strtolower(trim($_POST['plugin'] ?? ''));
            if ($slug === '' || !isset($pluginAdminMap[$slug])) {
                Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
            } else {
                if (\App\Core\PluginManager::purge($slug)) {
                    Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" purged successfully. All data and tables removed.', $pluginAdminMap[$slug]['name']), true);
                } else {
                    Feedback::flash('ERROR', 'DEFAULT', 'Plugin purge failed. Check database permissions.', false);
                }
            }
        // Plugin check action
        } elseif ($postAction === 'plugin_check') {
            $slug = strtolower(trim($_POST['plugin'] ?? ''));
            if ($slug === '' || !isset($pluginAdminMap[$slug])) {
                Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
            } else {
                // Redirect to plugin check page
                header('Location: ' . $app_root . '?page=admin&section=plugins&action=plugin_check_page&plugin=' . urlencode($slug));
                exit;
            }
        // Plugin migration test actions
        } elseif ($postAction === 'test_plugin_migrations') {
            $slug = strtolower(trim($_POST['plugin'] ?? ''));
            if ($slug === '' || !isset($pluginAdminMap[$slug])) {
                Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
            } else {
                try {
                    $pluginPath = $pluginAdminMap[$slug]['path'];
                    $bootstrapPath = $pluginPath . '/bootstrap.php';

                    if (!file_exists($bootstrapPath)) {
                        Feedback::flash('ERROR', 'DEFAULT', 'Plugin has no bootstrap file.', false);
                    } else {
                        // Load plugin bootstrap in isolation to test migrations
                        $migrationFunctions = [];
                        $bootstrapContent = file_get_contents($bootstrapPath);

                        // Check for migration functions
                        if (strpos($bootstrapContent, '_ensure_tables') !== false) {
                            // Temporarily include bootstrap to test migrations
                            include_once $bootstrapPath;

                            $migrationFunctionName = str_replace('-', '_', $slug) . '_ensure_tables';
                            if (function_exists($migrationFunctionName)) {
                                $migrationFunctionName();
                                Feedback::flash('NOTICE', 'DEFAULT', sprintf('Plugin "%s" migrations executed successfully.', $pluginAdminMap[$slug]['name']), true);
                            } else {
                                Feedback::flash('ERROR', 'DEFAULT', 'Plugin migration function not found.', false);
                            }
                        } else {
                            Feedback::flash('ERROR', 'DEFAULT', 'Plugin has no migration function.', false);
                        }
                    }
                } catch (Throwable $e) {
                    Feedback::flash('ERROR', 'DEFAULT', 'Migration test failed: ' . $e->getMessage(), false);
                }
            }
        // Test migrations actions
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

// Generate CSRF token early for all templates
$csrf_token = $security->generateCsrfToken();

// Handle plugin check page
if ($queryAction === 'plugin_check_page' && isset($_GET['plugin'])) {
    // Simple test for JSON response
    if (isset($_GET['test'])) {
        header('Content-Type: application/json');
        echo json_encode(['test' => 'working', 'timestamp' => time()]);
        exit;
    }

    // Debug: Log request details
    error_log('Plugin check request: ' . print_r([
        'action' => $queryAction,
        'plugin' => $_GET['plugin'],
        'ajax' => isset($_SERVER['HTTP_X_REQUESTED_WITH']),
        'ajax_header' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set',
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'not set'
    ], true));

    // Start output buffering to catch any unwanted output
    ob_start();

    // Disable error display for JSON responses
    $originalErrorReporting = error_reporting();
    $originalDisplayErrors = ini_get('display_errors');

    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               isset($_GET['ajax']);

    if ($isAjax) {
        error_reporting(0);
        ini_set('display_errors', 0);
    }

    $pluginSlug = strtolower(trim($_GET['plugin']));
    if (!isset($pluginAdminMap[$pluginSlug])) {
        if ($isAjax) {
            ob_end_clean(); // Clear and end output buffer
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unknown plugin specified.']);
            exit;
        }
        Feedback::flash('ERROR', 'DEFAULT', 'Unknown plugin specified.', false);
        header('Location: ' . $app_root . '?page=admin&section=plugins');
        exit;
    }

    $pluginInfo = $pluginAdminMap[$pluginSlug];
    $checkResults = [];

    try {
        // Check plugin files exist
        $migrationFiles = glob($pluginInfo['path'] . '/migrations/*.sql');
        $hasMigration = !empty($migrationFiles);

        $checkResults['files'] = [
            'manifest' => file_exists($pluginInfo['path'] . '/plugin.json'),
            'bootstrap' => file_exists($pluginInfo['path'] . '/bootstrap.php'),
            'migration' => $hasMigration,
        ];

        // Check database tables
        $db = \App\App::db();
        $pluginOwnedTables = [];
        $pluginReferencedTables = [];
        if ($db && method_exists($db, 'getConnection')) {
            $pdo = $db->getConnection();
            $stmt = $pdo->query("SHOW TABLES");
            $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if ($hasMigration) {
                foreach ($migrationFiles as $migrationFile) {
                    $migrationContent = file_get_contents($migrationFile);
                    
                    // Extract tables created by this migration (plugin-owned)
                    if (preg_match_all('/CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+`?([a-zA-Z0-9_]+)`?/i', $migrationContent, $matches)) {
                        foreach ($matches[1] as $tableName) {
                            if (in_array($tableName, $allTables)) {
                                $pluginOwnedTables[] = $tableName;
                            }
                        }
                    }
                    
                    // Find all referenced tables (dependencies)
                    foreach ($allTables as $table) {
                        if (strpos($migrationContent, $table) !== false && !in_array($table, $pluginOwnedTables)) {
                            $pluginReferencedTables[] = $table;
                        }
                    }
                }
                $pluginOwnedTables = array_unique($pluginOwnedTables);
                $pluginReferencedTables = array_unique($pluginReferencedTables);
            }
        }
        $checkResults['tables'] = [
            'owned' => $pluginOwnedTables,
            'referenced' => $pluginReferencedTables,
        ];

        // Check plugin functions
        $bootstrapPath = $pluginInfo['path'] . '/bootstrap.php';
        if (file_exists($bootstrapPath)) {
            include_once $bootstrapPath;
            $migrationFunction = str_replace('-', '_', $pluginSlug) . '_ensure_tables';
            $checkResults['functions'] = [
                'migration' => function_exists($migrationFunction),
            ];
        }

    } catch (Throwable $e) {
        $checkResults['error'] = $e->getMessage();
    }

    // Handle AJAX request
    if ($isAjax) {
        // Restore error reporting
        error_reporting($originalErrorReporting);
        ini_set('display_errors', $originalDisplayErrors);

        ob_end_clean(); // Clear and end output buffer
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');

        $jsonData = json_encode([
            'success' => true,
            'pluginInfo' => $pluginInfo,
            'checkResults' => $checkResults,
            'csrf_token' => $csrf_token,
            'app_root' => $app_root
        ]);

        error_log('JSON response: ' . $jsonData);
        echo $jsonData;
        exit;
    }

    // Restore error reporting for non-AJAX requests
    error_reporting($originalErrorReporting);
    ini_set('display_errors', $originalDisplayErrors);

    // Include check page template for non-AJAX requests
    include '../app/templates/admin_plugin_check.php';
    exit;
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

// Get any new feedback messages
include_once '../app/helpers/feedback.php';

// Load the view
include '../app/templates/admin.php';
