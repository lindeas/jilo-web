<?php
/**
 * Two-factor authentication verification template
 */
?>

<div class="auth-card mx-auto">
    <div class="auth-card-body">
        <div class="auth-header">
            <p class="auth-eyebrow">Security check</p>
            <h2 class="auth-title">Two-factor authentication</h2>
            <p class="auth-subtitle">Enter the 6-digit code from your authenticator app to continue.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="?page=login&action=verify" class="auth-form" novalidate>
            <div class="text-center mb-3">
                <label for="code" class="form-label">One-time code</label>
                <input type="text"
                       id="code"
                       name="code"
                       class="form-control text-center auth-otp-input"
                       pattern="[0-9]{6}"
                       maxlength="6"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       required
                       autofocus
                       placeholder="000000">
            </div>

            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

            <button type="submit" class="btn btn-primary auth-submit w-100">
                Verify code
            </button>
        </form>

        <div class="mt-4 text-center">
            <p class="text-muted mb-2">Lost access to your authenticator app?</p>
            <button class="btn btn-link auth-link p-0" type="button" data-toggle="collapse" data-target="#backupCodeForm">
                Use a backup code
            </button>
        </div>

        <div class="collapse mt-3" id="backupCodeForm">
            <form method="post" action="?page=login&action=verify" class="auth-form" novalidate>
                <div class="mb-3">
                    <label for="backup_code" class="form-label">Backup code</label>
                    <input type="text"
                           id="backup_code"
                           name="backup_code"
                           class="form-control"
                           pattern="[a-f0-9]{8}"
                           maxlength="8"
                           required
                           placeholder="Enter 8-character code">
                </div>

                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                <button type="submit" class="btn btn-secondary w-100">
                    Use backup code
                </button>
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
