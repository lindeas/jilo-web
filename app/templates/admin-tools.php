<?php
/** @var bool $maintenance_enabled */
/** @var string $maintenance_message */
/** @var array $pending */
/** @var array $applied */
/** @var string $csrf_token */
?>
<!-- admin tools page -->
<section class="tm-hero">
    <div class="tm-hero-card">
        <div class="tm-hero-body">
            <div class="tm-hero-heading">
                <h1 class="tm-hero-title">Admin tools</h1>
                <p class="tm-hero-subtitle">Centralized maintenance and database utilities to keep <?= htmlspecialchars($config['site_name']) ?> healthy.</p>
            </div>
            <div class="tm-hero-meta">
                <span class="tm-hero-pill <?= $maintenance_enabled ? 'pill-danger' : 'pill-success' ?>">
                    <i class="fas fa-power-off"></i>
                    Maintenance <?= $maintenance_enabled ? 'enabled' : 'not enabled' ?>
                </span>
                <span class="tm-hero-pill <?= empty($pending) ? 'pill-neutral' : 'pill-danger' ?>">
                    <i class="fas fa-database"></i>
                    <?= count($pending) ?> pending migration<?= count($pending) === 1 ? '' : 's' ?>
                </span>
            </div>
        </div>
        <a class="btn btn-primary tm-directory-cta" href="<?= htmlspecialchars($app_root) ?>?page=dashboard">
            <i class="fas fa-arrow-left"></i>
            Back to dashboard
        </a>
    </div>
</section>

<section class="tm-admin">

    <div class="tm-admin-grid">
        <article class="tm-admin-card">
            <header>
                <div>
                    <h2 class="tm-admin-card-title">Maintenance mode</h2>
                    <p class="tm-admin-card-subtitle">Let your team know when maintenance is in progress.</p>
                </div>
                <span class="tm-hero-pill <?= $maintenance_enabled ? 'pill-danger' : 'pill-neutral' ?>"><?= $maintenance_enabled ? 'enabled' : 'disabled' ?></span>
            </header>

            <div class="tm-admin-section">
                <p class="tm-admin-section-title">Message</p>
                <form method="post" class="tm-admin-controls">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="maintenance_on">
                    <input type="text" id="maintenance_message" name="maintenance_message" class="tm-admin-message-input" value="<?= htmlspecialchars($maintenance_message) ?>" placeholder="Upgrading database">
                    <div class="tm-admin-inline-actions">
                        <button type="submit" class="btn btn-warning" <?= $maintenance_enabled ? 'disabled' : '' ?>>Enable maintenance</button>
                        <button type="button" class="btn btn-outline-secondary" <?= $maintenance_enabled ? '' : 'disabled' ?> onclick="document.getElementById('maintenance-disable-form').submit();">Disable maintenance</button>
                    </div>
                </form>
                <form method="post" id="maintenance-disable-form" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="maintenance_off">
                </form>
            </div>
        </article>

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
                        <button type="submit" class="btn btn-outline-primary btn-sm" <?= !empty($test_migrations_exist) ? 'disabled' : '' ?>>Create test migration</button>
                    </form>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="clear_test_migrations">
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
<?php     foreach ($pending as $fname): ?>
                    <li>
                        <div class="tm-admin-list-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-toggle="modal" data-target="#migrationModal<?= md5($fname) ?>">
                                View
                            </button>
                        </div>
                        <span><?= htmlspecialchars($fname) ?></span>
                    </li>
<?php     endforeach; ?>
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
<?php     foreach ($applied as $fname):
            if (strpos($fname, '_test_migration') !== false) {
                continue;
            }
?>
                    <li>
                        <div class="tm-admin-list-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle="modal" data-target="#migrationModal<?= md5($fname) ?>">
                                View
                            </button>
                        </div>
                        <span><?= htmlspecialchars($fname) ?></span>
                    </li>
<?php     endforeach; ?>
<?php endif; ?>
                </ul>
            </div>

            <form method="post" class="tm-confirm" data-confirm="Apply all pending migrations?">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="migrate_up">
                <button type="submit" class="btn btn-danger w-100" <?= empty($pending) ? 'disabled' : '' ?>>Apply all pending</button>
            </form>
        </article>
    </div>
</section>

<!-- Migration viewer modals (one per file) -->
<?php if (!empty($migration_contents)):
      foreach ($migration_contents as $name => $content):
          $modalId = 'migrationModal' . md5($name);
?>
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="<?= $modalId ?>Label"><?= htmlspecialchars($name) ?></h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
<?php
    $record = $migration_records[$name] ?? null;
    $appliedAtRaw = $record['applied_at'] ?? null;
    $appliedAtFormatted = null;
    if (!empty($appliedAtRaw)) {
        $timestamp = strtotime($appliedAtRaw);
        $appliedAtFormatted = $timestamp ? date('M d, Y H:i', $timestamp) : $appliedAtRaw;
    }
?>
            <div class="modal-body p-0">
                <pre class="tm-admin-modal-code"><code style="border-radius: 0.5rem;"><?= htmlspecialchars($content) ?></code></pre>
            </div>
<?php
    $isModalNext = (!empty($next_pending) && $next_pending === $name);
    $modalResult = (!empty($migration_modal_result) && ($migration_modal_result['name'] ?? '') === $name) ? $migration_modal_result : null;
?>
            <div class="modal-footer">
<?php if ($isModalNext): ?>
                <form method="post" class="me-auto tm-confirm" data-confirm="Apply migration <?= htmlspecialchars($name) ?>?">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="migrate_apply_one">
                    <input type="hidden" name="migration_name" value="<?= htmlspecialchars($name) ?>">
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
<?php   endforeach;
      endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form.tm-confirm').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
</script>
