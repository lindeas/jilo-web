<?php
/** @var bool $maintenance_enabled */
/** @var string $maintenance_message */
/** @var array $pending */
/** @var array $applied */
/** @var string $csrf_token */
?>
<!-- admin tools page -->
<div class="container-fluid mt-2">
    <div class="row mb-4">
        <div class="col-12 mb-2">
            <h2>Admin tools</h2>
            <small class="text-muted">System maintenance and database utilities.</small>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools me-2 text-secondary"></i>
                        Maintenance mode
                    </h5>
                    <span class="badge <?= $maintenance_enabled ? 'bg-danger' : 'bg-success' ?>"><?= $maintenance_enabled ? 'enabled' : 'disabled' ?></span>
                </div>
                <div class="card-body p-4">
                    <form method="post" class="mb-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="maintenance_on">
                        <div class="mb-3">
                            <label for="maintenance_message" class="form-label mb-1">Message (optional)</label>
                            <input type="text" id="maintenance_message" name="maintenance_message" class="form-control form-control-sm" value="<?= htmlspecialchars($maintenance_message) ?>" placeholder="Upgrading database">
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm" <?= $maintenance_enabled ? 'disabled' : '' ?>>Enable maintenance</button>
                    </form>
                    <form method="post" class="mt-2">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="maintenance_off">
                        <button type="submit" class="btn btn-outline-secondary btn-sm" <?= $maintenance_enabled ? '' : 'disabled' ?>>Disable maintenance</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-database me-2 text-secondary"></i>
                        Database migrations
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($migration_error)): ?>
                        <div class="alert alert-danger">Error: <?= htmlspecialchars($migration_error) ?></div>
                    <?php endif; ?>
                    <div class="alert alert-info mb-3">
                        <strong>Test Migration Tools</strong><br>
                        <small class="text-muted">These tools create fake migrations in the database only (no files) for testing the migration warning functionality.</small>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="action" value="create_test_migration">
                            <button type="submit" class="btn btn-outline-info btn-sm" <?= !empty($test_migrations_exist) ? 'disabled' : '' ?>>Create test migration</button>
                        </form>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <input type="hidden" name="action" value="clear_test_migrations">
                            <button type="submit" class="btn btn-outline-secondary btn-sm" <?= empty($test_migrations_exist) ? 'disabled' : '' ?>>Clear test migrations</button>
                        </form>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>Pending</strong></div>
                            <span class="badge <?= empty($pending) ? 'bg-success' : 'bg-warning text-dark' ?>"><?= count($pending) ?></span>
                        </div>
                        <div class="mt-2 small border rounded" style="max-height: 240px; overflow: auto;">
                            <?php if (empty($pending)): ?>
                                <div class="p-2"><span class="text-success">none</span></div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($pending as $fname): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-monospace small"><?= htmlspecialchars($fname) ?></span>
                                            <button type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#migrationModal<?= md5($fname) ?>">View
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>Applied</strong></div>
                            <span class="badge bg-secondary"><?= count($applied) ?></span>
                        </div>
                        <div class="mt-2 small border rounded" style="max-height: 240px; overflow: auto;">
                            <?php if (empty($applied)): ?>
                                <div class="p-2"><span class="text-muted">none</span></div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($applied as $fname): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-monospace small"><?= htmlspecialchars($fname) ?></span>
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#migrationModal<?= md5($fname) ?>">View
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="post" class="mt-3">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        <input type="hidden" name="action" value="migrate_up">
                        <button type="submit" class="btn btn-primary btn-sm" <?= empty($pending) ? 'disabled' : '' ?>>Apply pending migrations</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
            <div class="modal-body p-0">
                <pre class="mb-0" style="max-height: 60vh; overflow: auto;"><code class="p-3 d-block"><?= htmlspecialchars($content) ?></code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php   endforeach;
      endif; ?>
