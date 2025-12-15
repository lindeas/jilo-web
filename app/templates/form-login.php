        <!-- login form -->
        <div class="action-card">
            <div class="action-card-header">
                <p class="action-eyebrow">Welcome back</p>
                <h2 class="action-title">Sign in</h2>
                <p class="action-subtitle">Enter your credentials to continue to <?= htmlspecialchars($config['site_name']); ?></p>
            </div>

            <div class="action-card-body">
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=login" class="action-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="action-form-group">
                        <label for="username" class="action-form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" class="form-control action-form-control" name="username" placeholder="Username"
                                pattern="[A-Za-z0-9_\-]{3,20}" title="3-20 characters, letters, numbers, - and _"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                required />
                        </div>
                    </div>

                    <div class="action-form-group">
                        <label for="password" class="action-form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" class="form-control action-form-control" name="password" placeholder="Password"
                                pattern=".{8,}" title="Eight or more characters"
                                required />
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember" class="form-check-input" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <a href="<?= htmlspecialchars($app_root) ?>?page=login&action=forgot" class="text-decoration-none">Forgot password?</a>
                    </div>

                    <div class="action-actions">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign in
                        </button>
                    </div>
<?php if (isset($_GET['redirect'])):
    $loginRawRedirect = $_GET['redirect'];
?>
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($loginRawRedirect, ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>
                </form>
            </div>
        </div>
        <!-- /login form -->
