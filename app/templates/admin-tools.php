<?php
/** @var bool $maintenance_enabled */
/** @var string $maintenance_message */
/** @var array $pending */
/** @var array $applied */
/** @var string $csrf_token */
?>
<div class="container mt-4">
  <h2>Admin tools</h2>
  <p class="text-muted">System maintenance and database utilities.</p>

  <div class="row mt-4">
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">Maintenance mode</div>
        <div class="card-body">
          <p>Status: <strong class="<?= $maintenance_enabled ? 'text-danger' : 'text-success' ?>">
            <?= $maintenance_enabled ? 'Enabled' : 'Disabled' ?></strong></p>
          <form method="post" class="mb-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="maintenance_on">
            <div class="mb-2">
              <label for="maintenance_message" class="form-label">Message (optional)</label>
              <input type="text" id="maintenance_message" name="maintenance_message" class="form-control" value="<?= htmlspecialchars($maintenance_message) ?>" placeholder="Upgrading database">
            </div>
            <button type="submit" class="btn btn-warning" <?= $maintenance_enabled ? 'disabled' : '' ?>>Enable maintenance</button>
          </form>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="maintenance_off">
            <button type="submit" class="btn btn-secondary" <?= $maintenance_enabled ? '' : 'disabled' ?>>Disable maintenance</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">Database migrations</div>
        <div class="card-body">
          <?php if (!empty($migration_error)): ?>
            <div class="alert alert-danger">Error: <?= htmlspecialchars($migration_error) ?></div>
          <?php endif; ?>
          <p>
            <strong>Pending (<?= count($pending) ?>):</strong>
            <?php if (empty($pending)): ?>
              <span class="text-success">None</span>
            <?php else: ?>
              <code><?= htmlspecialchars(implode(', ', $pending)) ?></code>
            <?php endif; ?>
          </p>
          <p>
            <strong>Applied (<?= count($applied) ?>):</strong>
            <?php if (empty($applied)): ?>
              <span class="text-muted">None</span>
            <?php else: ?>
              <code><?= htmlspecialchars(implode(', ', $applied)) ?></code>
            <?php endif; ?>
          </p>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="migrate_up">
            <button type="submit" class="btn btn-primary" <?= empty($pending) ? 'disabled' : '' ?>>Apply pending migrations</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
