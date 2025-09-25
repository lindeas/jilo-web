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
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>Pending</strong></div>
                            <span class="badge <?= empty($pending) ? 'bg-success' : 'bg-warning text-dark' ?>"><?= count($pending) ?></span>
                        </div>
                        <div class="mt-2 small">
                            <?php if (empty($pending)): ?>
                                <span class="text-success">none</span>
                            <?php else: ?>
                                <code><?= htmlspecialchars(implode(', ', $pending)) ?></code>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div><strong>Applied</strong></div>
                            <span class="badge bg-secondary"><?= count($applied) ?></span>
                        </div>
                        <div class="mt-2 small">
                            <?php if (empty($applied)): ?>
                                <span class="text-muted">none</span>
                            <?php else: ?>
                                <code><?= htmlspecialchars(implode(', ', $applied)) ?></code>
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
