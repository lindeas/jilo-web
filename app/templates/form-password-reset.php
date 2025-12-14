
        <div class="action-card">
            <div class="action-card-header">
                <p class="action-eyebrow">Account recovery</p>
                <h2 class="action-title">Reset password</h2>
                <p class="action-subtitle">Create a new password that is at least 8 characters long</p>
            </div>

            <div class="action-card-body">
                <form method="post" action="?page=login&action=reset&token=<?= htmlspecialchars(urlencode($token)) ?>" class="action-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="action-form-group">
                        <label for="new_password" class="action-form-label">New password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password"
                                   class="form-control action-form-control"
                                   id="new_password"
                                   name="new_password"
                                   placeholder="Enter new password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <div class="action-form-group">
                        <label for="confirm_password" class="action-form-label">Confirm password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="password"
                                   class="form-control action-form-control"
                                   id="confirm_password"
                                   name="confirm_password"
                                   placeholder="Confirm new password"
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>
                    </div>

                    <div class="action-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock me-2"></i>Update password
                        </button>
                    </div>
                </form>

                <div class="mt-4 text-center">
                    <a href="?page=login" class="text-decoration-none">← Back to login</a>
                </div>
            </div>
        </div>
