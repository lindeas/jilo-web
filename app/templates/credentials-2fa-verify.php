<?php
/**
 * Two-factor authentication verification template
 */
?>

<div class="action-card">
    <div class="action-card-header">
        <p class="action-eyebrow">Security check</p>
        <h2 class="action-title">Two-factor authentication</h2>
        <p class="action-subtitle">Enter the 6-digit code from your authenticator app to continue.</p>
    </div>
    <div class="action-card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="?page=login&action=verify" class="action-form" novalidate>
            <div class="action-form-group">
                <label for="code" class="action-form-label">One-time code</label>
                <input type="text"
                       id="code"
                       name="code"
                       class="form-control action-form-control text-center"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       required
                       autofocus
                       placeholder="000000">
            </div>

            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

            <div class="action-actions">
                <button type="submit" class="btn btn-primary">
                    Verify code
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <p class="text-muted mb-2">Lost access to your authenticator app?</p>
            <button class="btn btn-link p-0" type="button" data-toggle="collapse" data-target="#backupCodeForm">
                Use a backup code
            </button>
        </div>

        <div class="collapse mt-3" id="backupCodeForm">
            <form method="post" action="?page=login&action=verify" class="action-form" novalidate>
                <div class="action-form-group">
                    <label for="backup_code" class="action-form-label">Backup code</label>
                    <input type="text"
                           id="backup_code"
                           name="backup_code"
                           class="form-control action-form-control"
                           pattern="[a-f0-9]{8}"
                           maxlength="8"
                           required
                           placeholder="Enter 8-character code">
                </div>

                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                <div class="action-actions">
                    <button type="submit" class="btn btn-secondary">
                        Use backup code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-submit when 6 digits are entered
document.querySelector('input[name="code"]').addEventListener('input', function(e) {
    if (e.target.value.length === 6 && e.target.checkValidity()) {
        e.target.form.submit();
    }
});
</script>
