<?php
/**
 * Combined credentials management template
 * Handles both password changes and 2FA management
 */
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Password Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3>change password</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="?page=credentials&item=password">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="form-group">
                            <label for="current_password">current password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="current_password" 
                                   name="current_password" 
                                   required>
                        </div>

                        <div class="form-group mt-3">
                            <label for="new_password">new password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="new_password" 
                                   name="new_password"
                                   pattern=".{8,}"
                                   title="Password must be at least 8 characters long"
                                   required>
                            <small class="form-text text-muted">minimum 8 characters</small>
                        </div>

                        <div class="form-group mt-3">
                            <label for="confirm_password">confirm new password</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password"
                                   pattern=".{8,}"
                                   required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">change password</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2FA Management -->
            <div class="card">
                <div class="card-header">
                    <h3>two-factor authentication</h3>
                </div>
                <div class="card-body">
                    <p class="mb-4">Two-factor authentication adds an extra layer of security to your account. Once enabled, you'll need to enter both your password and a code from your authenticator app when signing in.</p>

                    <?php if ($has2fa): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> two-factor authentication is enabled
                        </div>
                        <form method="post" action="?page=credentials&item=2fa&action=disable">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                                disable two-factor authentication
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> two-factor authentication is not enabled
                        </div>
                        <form method="post" action="?page=credentials&item=2fa&action=setup">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <button type="submit" class="btn btn-primary">
                                set up two-factor authentication
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
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
});</script>
