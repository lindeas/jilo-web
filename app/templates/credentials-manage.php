<?php
/**
 * Combined credentials management template
 * Handles both password changes and 2FA management
 */
?>

<div class="action-card">
    <div class="action-card-header">
        <p class="action-eyebrow">Security</p>
        <h2 class="action-title">Manage credentials</h2>
        <p class="action-subtitle">Update your password and keep two-factor authentication status in one place.</p>
    </div>
    <div class="action-card-body">
        <div class="tm-cred-grid">
            <section class="tm-cred-panel">
                <div class="tm-cred-panel-head">
                    <div>
                        <h3>Change password</h3>
                        <p>Choose a strong password to keep your account safe.</p>
                    </div>
                    <span class="badge bg-light text-dark">Required</span>
                </div>
                <form method="post" action="?page=credentials&item=password" class="action-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div class="action-form-group">
                        <label for="current_password" class="action-form-label">Current password</label>
                        <input type="password" class="form-control action-form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="action-form-group">
                        <label for="new_password" class="action-form-label">New password</label>
                        <input type="password" class="form-control action-form-control" id="new_password" name="new_password" pattern=".{8,}" title="Password must be at least 8 characters long" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="action-form-group">
                        <label for="confirm_password" class="action-form-label">Confirm new password</label>
                        <input type="password" class="form-control action-form-control" id="confirm_password" name="confirm_password" pattern=".{8,}" required>
                    </div>

                    <div class="action-actions">
                        <button type="submit" class="btn btn-primary">Save new password</button>
                    </div>
                </form>
            </section>

            <section class="tm-cred-panel">
                <div class="tm-cred-panel-head">
                    <div>
                        <h3>Two-factor authentication</h3>
                        <p>Strengthen security with a verification code from your authenticator app.</p>
                    </div>
                    <span class="badge <?= $has2fa ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $has2fa ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>

                <?php if ($has2fa): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2">
                        <i class="fas fa-shield-check"></i>
                        <span>Two-factor authentication is currently enabled.</span>
                    </div>
                    <form method="post" action="?page=credentials&item=2fa&action=disable" class="action-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="action-actions">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Disable two-factor authentication? This will make your account less secure.')">
                                Disable 2FA
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2">
                        <i class="fas fa-lock"></i>
                        <span>Two-factor authentication is not enabled yet.</span>
                    </div>
                    <form method="post" action="?page=credentials&item=2fa&action=setup" class="action-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="action-actions">
                            <button type="submit" class="btn btn-outline-primary">
                                Set up 2FA
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    if (this.value !== document.getElementById('new_password').value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>
