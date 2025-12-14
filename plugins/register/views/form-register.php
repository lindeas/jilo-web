        <!-- registration form -->
        <div class="action-card">
            <div class="action-card-header">
                <p class="action-eyebrow">Create account</p>
                <h2 class="action-title">Register</h2>
                <p class="action-subtitle">Enter your credentials to create a new account</p>
            </div>
            
            <div class="action-card-body">
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=register" class="action-form">
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="action-form-group">
                        <label for="username" class="action-form-label">Username</label>
                        <input type="text" class="form-control action-form-control" name="username" placeholder="Username"
                            pattern="[A-Za-z0-9_\-]{3,20}" title="3-20 characters, letters, numbers, - and _"
                            required />
                    </div>
                    
                    <div class="action-form-group">
                        <label for="password" class="action-form-label">Password</label>
                        <input type="password" class="form-control action-form-control" name="password" placeholder="Password"
                            pattern=".{8,}" title="Eight or more characters"
                            required />
                    </div>
                    
                    <div class="action-form-group">
                        <label for="confirm_password" class="action-form-label">Confirm Password</label>
                        <input type="password" class="form-control action-form-control" name="confirm_password" placeholder="Confirm password"
                            pattern=".{8,}" title="Eight or more characters"
                            required />
                    </div>
                    
                    <div class="action-form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="<?= htmlspecialchars($app_root) ?>?page=terms" target="_blank">terms & conditions</a> and <a href="<?= htmlspecialchars($app_root) ?>?page=privacy" target="_blank">privacy policy</a>
                            </label>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            We use cookies to improve your experience. See our <a href="<?= htmlspecialchars($app_root) ?>?page=cookies" target="_blank">cookies policy</a>
                        </small>
                    </div>
                    
                    <div class="action-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>Create account
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- /registration form -->
