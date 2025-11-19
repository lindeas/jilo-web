<?php
/**
 * Combined credentials management template
 * Handles both password changes and 2FA management
 */
?>

<div class="tm-cred-card mx-auto">
    <div class="tm-profile-header">
        <p class="tm-profile-eyebrow">Security</p>
        <h2 class="tm-profile-title">Manage credentials</h2>
        <p class="tm-profile-subtitle">Update your password and keep two-factor authentication status in one place.</p>
    </div>

    <div class="tm-cred-grid">
        <section class="tm-cred-panel">
            <div class="tm-cred-panel-head">
                <div>
                    <h3>Change password</h3>
                    <p>Choose a strong password to keep your account safe.</p>
                </div>
                <span class="badge bg-light text-dark">Required</span>
            </div>
            <form method="post" action="?page=credentials&item=password" class="tm-cred-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="mb-3">
                    <label for="current_password" class="form-label">Current password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label">New password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" pattern=".{8,}" title="Password must be at least 8 characters long" required>
                    <small class="form-text text-muted">Minimum 8 characters</small>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm new password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" pattern=".{8,}" required>
                </div>

                <button type="submit" class="btn btn-primary tm-contact-submit w-100">Save new password</button>
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
                <form method="post" action="?page=credentials&item=2fa&action=disable" class="tm-cred-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Disable two-factor authentication? This will make your account less secure.')">
                        Disable 2FA
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="fas fa-lock"></i>
                    <span>Two-factor authentication is not enabled yet.</span>
                </div>
                <form method="post" action="?page=credentials&item=2fa&action=setup" class="tm-cred-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        Set up 2FA
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    if (this.value !== document.getElementById('new_password').value) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});</script>
