
        <div class="auth-card mx-auto">
            <div class="auth-card-body">
                <div class="auth-header">
                    <p class="auth-eyebrow">Secure account</p>
                    <h2 class="auth-title">Choose a new password</h2>
                    <p class="auth-subtitle">Create a password that is at least 8 characters long. We will sign you in once it is updated.</p>
                </div>
                <form method="post" action="?page=login&action=reset&token=<?= htmlspecialchars(urlencode($token)) ?>" class="auth-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New password</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="new_password"
                                   name="new_password"
                                   placeholder="Enter new password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm password</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="confirm_password"
                                   name="confirm_password"
                                   placeholder="Re-enter new password"
                                   required 
                                   minlength="8"
                                   autocomplete="new-password">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary auth-submit w-100">
                        Set new password
                    </button>
                </form>
            </div>
        </div>

