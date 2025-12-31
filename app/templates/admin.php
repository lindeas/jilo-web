<?php
/** @var bool $maintenance_enabled */
/** @var string $maintenance_message */
/** @var array $pending */
/** @var array $applied */
/** @var string $csrf_token */
/** @var string|null $next_pending */
/** @var array $migration_contents */
/** @var array $migration_records */
/** @var bool $test_migrations_exist */
/** @var array|null $migration_modal_result */
/** @var string|null $modal_to_open */
/** @var string|null $migration_error */
/** @var array $adminOverviewPills */
/** @var array $adminOverviewStatuses */
/** @var array $sectionState */
?>

<?php
$preselectModalId = null;
if (!empty($modal_to_open)) {
    $preselectModalId = 'migrationModal' . md5($modal_to_open);
}

$tabs = $adminTabs ?? [];
if (empty($tabs)) {
    $tabs = [
        'overview' => [
            'label' => 'Overview',
            'url' => $sectionUrls['overview'] ?? ($app_root . '?page=admin'),
            'type' => 'core',
            'hook' => null,
            'position' => 100,
        ],
    ];
}

$heroPills = [
    [
        'label' => 'Maintenance',
        'value' => $maintenance_enabled ? 'enabled' : 'off',
        'icon' => 'fas fa-power-off',
        'tone' => $maintenance_enabled ? 'danger' : 'success',
    ],
    [
        'label' => 'Migrations',
        'value' => count($pending) . ' pending',
        'icon' => 'fas fa-database',
        'tone' => empty($pending) ? 'neutral' : 'warning',
    ],
];

if (!empty($adminOverviewPills) && is_array($adminOverviewPills)) {
    foreach ($adminOverviewPills as $pill) {
        if (!is_array($pill)) {
            continue;
        }
        $heroPills[] = [
            'label' => (string)($pill['label'] ?? 'Status'),
            'value' => (string)($pill['value'] ?? ''),
            'icon' => (string)($pill['icon'] ?? 'fas fa-info-circle'),
            'tone' => (string)($pill['tone'] ?? 'info'),
        ];
    }
}

$statusItems = [
    [
        'label' => 'Maintenance mode',
        'description' => $maintenance_enabled ? 'Live site shows downtime banner.' : 'Visitors see the normal experience.',
        'value' => $maintenance_enabled ? 'ON' : 'OFF',
        'tone' => $maintenance_enabled ? 'warning' : 'success',
    ],
    [
        'label' => 'Schema migrations',
        'description' => empty($pending) ? 'Database matches code.' : 'Pending updates detected.',
        'value' => count($pending) . ' pending',
        'tone' => empty($pending) ? 'success' : 'warning',
    ],
];

if (!empty($adminOverviewStatuses) && is_array($adminOverviewStatuses)) {
    foreach ($adminOverviewStatuses as $status) {
        if (!is_array($status)) {
            continue;
        }
        $statusItems[] = [
            'label' => (string)($status['label'] ?? 'Status'),
            'description' => (string)($status['description'] ?? ''),
            'value' => (string)($status['value'] ?? ''),
            'tone' => (string)($status['tone'] ?? 'info'),
        ];
    }
}
?>

<section class="tm-hero">
    <div class="tm-hero-card tm-hero-card--admin">
        <div class="tm-hero-body">
            <div class="tm-hero-heading">
                <h1 class="tm-hero-title">Admin control center</h1>
                <p class="tm-hero-subtitle">
                    Centralized administration dashboard for system-wide management.
                </p>
            </div>
            <div class="tm-hero-meta tm-hero-meta--stacked">
<?php foreach ($heroPills as $pill):
    $toneClass = 'pill-' . preg_replace('/[^a-z0-9_-]/i', '', $pill['tone'] ?? 'info');
?>
                <div class="tm-hero-pill <?= htmlspecialchars($toneClass) ?>">
                    <i class="<?= htmlspecialchars($pill['icon']) ?>"></i>
                    <?= htmlspecialchars($pill['label']) ?> <?= htmlspecialchars($pill['value']) ?>
                </div>
<?php endforeach; ?>
            </div>
        </div>
        <div class="tm-hero-actions">
            <a class="btn btn-primary tm-directory-cta" href="<?= htmlspecialchars($app_root) ?>?page=dashboard">
                <i class="fas fa-arrow-left"></i> Back to dashboard
            </a>
        </div>
    </div>
</section>

<section class="tm-admin tm-admin--dashboard">
    <div class="tm-admin-tabs" role="tablist">
<?php foreach ($tabs as $sectionKey => $tabMeta):
    $isActive = $activeSection === $sectionKey;
    $tabUrl = $tabMeta['url'] ?? ($sectionUrls[$sectionKey] ?? ($app_root . '?page=admin&section=' . urlencode($sectionKey)));
?>
        <a class="tm-admin-tab-button <?= $isActive ? 'active' : '' ?>"
           href="<?= htmlspecialchars($tabUrl) ?>"
           role="tab"
           aria-selected="<?= $isActive ? 'true' : 'false' ?>"
           aria-controls="tm-admin-tab-<?= htmlspecialchars($sectionKey) ?>">
            <?= htmlspecialchars($tabMeta['label'] ?? ucfirst($sectionKey)) ?>
        </a>
<?php endforeach; ?>
    </div>

<?php foreach ($tabs as $sectionKey => $tabMeta):
    $panelUrl = $tabMeta['url'] ?? ($sectionUrls[$sectionKey] ?? ($app_root . '?page=admin&section=' . urlencode($sectionKey)));
    $isActive = $activeSection === $sectionKey;
?>
    <div class="tm-admin-tab-panel <?= $isActive ? 'active' : '' ?>" id="tm-admin-tab-<?= htmlspecialchars($sectionKey) ?>" role="tabpanel">
<?php if (($tabMeta['type'] ?? 'core') === 'core' && $sectionKey === 'overview'): ?>
        <div class="tm-admin-grid tm-admin-grid--three">
            <article class="tm-admin-card">
                <header>
                    <h2 class="tm-admin-card-title">Current status</h2>
                    <p class="tm-admin-card-subtitle">High-level signals that require your attention.</p>
                </header>
                <ul class="tm-admin-status-list">
<?php foreach ($statusItems as $status):
    $statusTone = 'status-' . preg_replace('/[^a-z0-9_-]/i', '', $status['tone'] ?? 'info');
?>
                    <li class="<?= htmlspecialchars($statusTone) ?>">
                        <div>
                            <strong><?= htmlspecialchars($status['label']) ?></strong>
<?php if (!empty($status['description'])): ?>
                            <p><?= htmlspecialchars($status['description']) ?></p>
<?php endif; ?>
                        </div>
                        <span class="tm-admin-status-value <?= htmlspecialchars($statusTone) ?>">
                            <?= htmlspecialchars($status['value']) ?>
                        </span>
                    </li>
<?php endforeach; ?>
                </ul>
            </article>

            <article class="tm-admin-card">
                <header>
                    <h2 class="tm-admin-card-title">Maintenance</h2>
                    <p class="tm-admin-card-subtitle">Toggle maintenance or update visitor message.</p>
                </header>
                <form method="post" class="tm-admin-controls">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="maintenance_on">
                    <input type="hidden" name="section" value="overview">
                    <label for="maintenance_message_overview" class="form-label">Maintenance message</label>
                    <input type="text"
                           id="maintenance_message_overview"
                           name="maintenance_message"
                           class="form-control tm-admin-message-input"
                           value="<?= htmlspecialchars($maintenance_message) ?>"
                           placeholder="Custom message. Default is 'Please try again later.'">
                    <div class="tm-admin-inline-actions">
                        <button type="submit" class="btn btn-warning" <?= $maintenance_enabled ? 'disabled' : '' ?>>Enable</button>
                        <button type="button" class="btn btn-outline-secondary" <?= $maintenance_enabled ? '' : 'disabled' ?>
                                onclick="document.getElementById('maintenance-disable-form-overview').submit();">Disable</button>
                    </div>
                </form>
                <form method="post" id="maintenance-disable-form-overview" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="maintenance_off">
                    <input type="hidden" name="section" value="overview">
                </form>
            </article>

            <article class="tm-admin-card">
                <header>
                    <h2 class="tm-admin-card-title">Next migration</h2>
                    <p class="tm-admin-card-subtitle">Peek at what will run when you apply updates.</p>
                </header>
                <?php if ($next_pending): ?>
                    <p class="text-muted mb-2">Next: <strong><?= htmlspecialchars($next_pending) ?></strong></p>
                    <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#migrationModal<?= md5($next_pending) ?>">
                        View SQL
                    </button>
                <?php else: ?>
                    <p class="tm-admin-empty">No migrations queued.</p>
                <?php endif; ?>
                <hr>
                <form method="post" class="tm-confirm" data-confirm="Apply all pending migrations?">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="migrate_up">
                    <input type="hidden" name="section" value="overview">
                    <button type="submit" class="btn btn-danger w-100" <?= empty($pending) ? 'disabled' : '' ?>>Apply all pending</button>
                </form>
            </article>
        </div>
<?php elseif (($tabMeta['type'] ?? 'core') === 'core' && $sectionKey === 'maintenance'): ?>
        <div class="tm-admin-grid">
            <article class="tm-admin-card">
                <header>
                    <div>
                        <h2 class="tm-admin-card-title">Maintenance mode</h2>
                        <p class="tm-admin-card-subtitle">Let your users know when maintenance is in progress.</p>
                    </div>
                    <span class="tm-hero-pill <?= $maintenance_enabled ? 'pill-danger' : 'pill-neutral' ?>">
                        <?= $maintenance_enabled ? 'ENABLED' : 'DISABLED' ?>
                    </span>
                </header>
                <div class="tm-admin-section">
                    <p class="tm-admin-section-title">Message</p>
                    <form method="post" class="tm-admin-controls">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="maintenance_on">
                        <textarea id="maintenance_message"
                                  name="maintenance_message"
                                  class="form-control tm-admin-message-input"
                                  rows="3"
                                  placeholder="Custom message. Default is 'Please try again later.'"><?= htmlspecialchars($maintenance_message) ?></textarea>
                        <div class="tm-admin-inline-actions">
                            <button type="submit" class="btn btn-warning" <?= $maintenance_enabled ? 'disabled' : '' ?>>Enable maintenance</button>
                            <button type="button" class="btn btn-outline-secondary" <?= $maintenance_enabled ? '' : 'disabled' ?> onclick="document.getElementById('maintenance-disable-form').submit();">Disable maintenance</button>
                        </div>
                    </form>
                </div>
                <form method="post" id="maintenance-disable-form" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="maintenance_off">
                    <input type="hidden" name="section" value="maintenance">
                </form>
            </article>
        </div>
<?php elseif (($tabMeta['type'] ?? 'core') === 'core' && $sectionKey === 'migrations'): ?>
        <div class="tm-admin-grid">
            <article class="tm-admin-card tm-admin-card--migrations">
                <header>
                    <div>
                        <h2 class="tm-admin-card-title">Database migrations</h2>
                        <p class="tm-admin-card-subtitle">Review pending SQL and apply with confidence.</p>
                    </div>
                </header>

                <?php if (!empty($migration_error)): ?>
                    <div class="alert alert-danger">Error: <?= htmlspecialchars($migration_error) ?></div>
                <?php endif; ?>

                <div class="tm-admin-test-tools">
                    <p><strong>Test migration tools</strong></p>
                    <div class="tm-admin-inline-actions">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="action" value="create_test_migration">
                            <input type="hidden" name="section" value="migrations">
                            <button type="submit" class="btn btn-outline-primary btn-sm" <?= !empty($test_migrations_exist) ? 'disabled' : '' ?>>Create test migration</button>
                        </form>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="action" value="clear_test_migrations">
                            <input type="hidden" name="section" value="migrations">
                            <button type="submit" class="btn btn-outline-secondary btn-sm" <?= empty($test_migrations_exist) ? 'disabled' : '' ?>>Clear test migrations</button>
                        </form>
                    </div>
                </div>

                <div class="tm-admin-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="tm-admin-section-title mb-0">Pending migrations</p>
                        <?php if (!empty($next_pending)): ?>
                            <span class="badge bg-info text-dark">Next: <?= htmlspecialchars($next_pending) ?></span>
                        <?php endif; ?>
                    </div>
                    <ul class="tm-admin-list">
                        <?php if (empty($pending)): ?>
                            <li class="tm-admin-empty">No pending migrations</li>
                        <?php else: ?>
                            <?php foreach ($pending as $fname): ?>
                                <li>
                                    <div class="tm-admin-list-actions">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#migrationModal<?= md5($fname) ?>">View</button>
                                    </div>
                                    <span><?= htmlspecialchars($fname) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="tm-admin-section">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="tm-admin-section-title mb-0">Applied migrations</p>
                        <span class="badge bg-secondary"><?= count($applied) ?></span>
                    </div>
                    <ul class="tm-admin-list">
                        <?php if (empty($applied)): ?>
                            <li class="tm-admin-empty">No applied migrations yet</li>
                        <?php else: ?>
                            <?php foreach ($applied as $fname):
                                if (strpos($fname, '_test_migration') !== false) {
                                    continue;
                                }
                            ?>
                                <li>
                                    <div class="tm-admin-list-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#migrationModal<?= md5($fname) ?>">View</button>
                                    </div>
                                    <span><?= htmlspecialchars($fname) ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <form method="post" class="tm-confirm" data-confirm="Apply all pending migrations?">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="migrate_up">
                    <input type="hidden" name="section" value="migrations">
                    <button type="submit" class="btn btn-danger w-100" <?= empty($pending) ? 'disabled' : '' ?>>Apply all pending</button>
                </form>
            </article>
        </div>
<?php elseif (($tabMeta['type'] ?? 'core') === 'core' && $sectionKey === 'plugins'): ?>
        <?php
            $pluginsState = $sectionState['plugins'] ?? [];
            $pluginsList = $pluginsState['plugins'] ?? [];
            $dependencyErrors = $pluginsState['dependency_errors'] ?? [];
            $totalPlugins = count($pluginsList);
            $enabledPlugins = count(array_filter($pluginsList, static function($plugin) {
                return !empty($plugin['enabled']);
            }));
            $issuesPlugins = count(array_filter($pluginsList, static function($plugin) {
                return !empty($plugin['dependency_errors']) || !$plugin['loaded'];
            }));
        ?>
        <div class="tm-admin-grid">
            <article class="tm-admin-card">
                <header>
                    <div>
                        <h2 class="tm-admin-card-title">Plugin overview</h2>
                        <p class="tm-admin-card-subtitle">Enable or disable functionality and review dependency health.</p>
                    </div>
                    <div class="tm-hero-pill pill-primary">
                        <?= htmlspecialchars($enabledPlugins) ?> / <?= htmlspecialchars($totalPlugins) ?> enabled
                    </div>
                </header>
                <?php if (!empty($dependencyErrors)): ?>
                    <div class="alert alert-warning">
                        <strong>Dependency issues detected.</strong> Resolve the following before enabling affected plugins:
                        <ul class="mb-0 mt-2">
                        <?php foreach ($dependencyErrors as $slug => $errors):
                            if (empty($errors)) {
                                continue;
                            }
                        ?>
                            <li><strong><?= htmlspecialchars($slug) ?>:</strong> <?= htmlspecialchars(implode('; ', $errors)) ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if (empty($pluginsList)): ?>
                    <p class="tm-admin-empty mb-0">No plugins detected in the plugins directory.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover tm-admin-table">
                            <thead>
                                <tr>
                                    <th>Plugin</th>
                                    <th>Status</th>
                                    <th>Depends on</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $pluginIndex = $pluginsState['plugin_index'] ?? [];
                            foreach ($pluginsList as $plugin):
                                $missingDeps = $plugin['missing_dependencies'] ?? [];
                                $depErrors = $plugin['dependency_errors'] ?? [];
                                $dependents = $plugin['dependents'] ?? [];
                                $enabledDependents = $plugin['enabled_dependents'] ?? [];
                                $statusBadges = [];
                                $statusBadges[] = $plugin['enabled']
                                    ? '<span class="badge text-uppercase" style="background-color:#198754;color:#fff;">Enabled</span>'
                                    : '<span class="badge text-uppercase" style="background-color:#6c757d;color:#fff;">Disabled</span>';
                                if ($plugin['enabled'] && empty($depErrors) && $plugin['loaded']) {
                                    $statusBadges[] = '<span class="badge text-uppercase" style="background-color:#0dcaf0;color:#052c65;">Loaded</span>';
                                }
                                if (!empty($missingDeps) || !empty($depErrors)) {
                                    $statusBadges[] = '<span class="badge text-uppercase" style="background-color:#ffc107;color:#212529;">Issues</span>';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($plugin['name']) ?></strong>
                                        <?php if (!empty($plugin['version'])): ?>
                                            <span class="text-muted">v<?= htmlspecialchars($plugin['version']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($plugin['description'])): ?>
                                            <p class="tm-admin-muted mb-0"><?= htmlspecialchars($plugin['description']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= implode(' ', $statusBadges) ?>
                                        <?php if (!empty($depErrors)): ?>
                                            <p class="tm-admin-muted text-warning mb-0"><?= htmlspecialchars(implode(' ', $depErrors)) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($plugin['dependencies'])): ?>
                                            <ul class="tm-admin-inline-list">
                                            <?php foreach ($plugin['dependencies'] as $dep):
                                                $depMeta = $pluginIndex[$dep] ?? null;
                                                $depStatusBadge = '';
                                                if ($depMeta) {
                                                    $depStatusBadge = $depMeta['enabled']
                                                        ? '<span class="badge" style="background-color:#198754;color:#fff;">OK</span>'
                                                        : '<span class="badge" style="background-color:#ffc107;color:#212529;">Off</span>';
                                                    if (!empty($depMeta['dependency_errors'])) {
                                                        $depStatusBadge = '<span class="badge" style="background-color:#dc3545;color:#fff;">Error</span>';
                                                    }
                                                } elseif (in_array($dep, $missingDeps, true)) {
                                                    $depStatusBadge = '<span class="badge" style="background-color:#dc3545;color:#fff;">Missing</span>';
                                                }
                                            ?>
                                                <li>
                                                    <?= htmlspecialchars($dep) ?>
                                                    <?php if ($depStatusBadge !== ''): ?>
                                                        <span class="tm-admin-dep-status"><?= $depStatusBadge ?></span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-right">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="section" value="plugins">
                                            <input type="hidden" name="plugin" value="<?= htmlspecialchars($plugin['slug']) ?>">
                                            <?php if ($plugin['enabled']): ?>
                                            <input type="hidden" name="action" value="plugin_disable">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" <?= $plugin['can_disable'] ? '' : 'disabled' ?>>Disable</button>
                                            <?php else: ?>
                                            <input type="hidden" name="action" value="plugin_enable">
                                            <button type="submit" class="btn btn-sm btn-outline-success" <?= $plugin['can_enable'] ? '' : 'disabled' ?>>Enable</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </article>
        </div>
<?php elseif (!empty($tabMeta['hook'])): ?>
        <?php
            do_hook($tabMeta['hook'], [
                'section' => $sectionKey,
                'active_section' => $activeSection,
                'app_root' => $app_root,
                'section_url' => $panelUrl,
                'section_urls' => $sectionUrls ?? [],
                'csrf_token' => $csrf_token,
                'state' => $sectionState[$sectionKey] ?? [],
                'section_state' => $sectionState,
                'db' => $db ?? null,
            ]);
        ?>
<?php else: ?>
        <article class="tm-admin-card">
            <p class="tm-admin-empty mb-0">No renderer available for this section.</p>
        </article>
<?php endif; ?>
    </div>
<?php endforeach; ?>
</section>

<?php if (!empty($migration_contents)):
    foreach ($migration_contents as $name => $content):
        $modalId = 'migrationModal' . md5($name);
        $record = $migration_records[$name] ?? null;
        $appliedAtRaw = $record['applied_at'] ?? null;
        $appliedAtFormatted = null;
        if (!empty($appliedAtRaw)) {
            $timestamp = strtotime($appliedAtRaw);
            $appliedAtFormatted = $timestamp ? date('M d, Y H:i', $timestamp) : $appliedAtRaw;
        }
        $isModalNext = (!empty($next_pending) && $next_pending === $name);
        $modalResult = (!empty($migration_modal_result) && ($migration_modal_result['name'] ?? '') === $name) ? $migration_modal_result : null;
?>
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $modalId ?>Label"><?= htmlspecialchars($name) ?></h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <pre class="tm-admin-modal-code"><code style="border-radius: 0.5rem;"><?= htmlspecialchars($content) ?></code></pre>
            </div>
            <div class="modal-footer">
                <?php if ($isModalNext): ?>
                    <form method="post" class="me-auto tm-confirm" data-confirm="Apply migration <?= htmlspecialchars($name) ?>?">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="migrate_apply_one">
                        <input type="hidden" name="migration_name" value="<?= htmlspecialchars($name) ?>">
                        <input type="hidden" name="section" value="migrations">
                        <button type="submit" class="btn btn-danger">Apply migration</button>
                    </form>
                <?php endif; ?>
                <?php if ($modalResult): ?>
                    <div class="alert alert-<?= $modalResult['status'] === 'success' ? 'success' : 'info' ?> mb-0 small">
                        <?= htmlspecialchars($modalResult['message']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($appliedAtFormatted): ?>
                    <div class="tm-admin-modal-meta">
                        <span class="tm-admin-pill pill-success">
                            <i class="far fa-clock"></i>
                            Applied <?= htmlspecialchars($appliedAtFormatted) ?>
                        </span>
                    </div>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php
    endforeach;
endif; ?>

<form method="post" id="tm-admin-hidden-read-migration" class="d-none">
    <input type="hidden" name="action" value="read_migration">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <input type="hidden" name="filename" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.tm-confirm').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const preselectModal = <?= $preselectModalId ? '"#' . htmlspecialchars($preselectModalId) . '"' : 'null' ?>;
    if (preselectModal) {
        const el = document.querySelector(preselectModal);
        if (el && window.$) {
            window.$(el).modal('show');
        }
    }
});
</script>
