        <!-- login form -->
        <div class="auth-card mx-auto">
            <div class="auth-card-body">
                <div class="auth-header">
                    <p class="auth-eyebrow">Welcome back</p>
                    <h2 class="auth-title">Sign in to <?= htmlspecialchars($config['site_name']); ?></h2>
                    <p class="auth-subtitle">Enter your credentials to continue</p>
                </div>
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=login" class="auth-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" class="form-control" name="username" placeholder="Username"
                                pattern="[A-Za-z0-9_\-]{3,20}" title="3-20 characters, letters, numbers, - and _"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                required />
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" class="form-control" name="password" placeholder="Password"
                                pattern=".{8,}" title="Eight or more characters"
                                required />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4 auth-remember">
                        <label class="form-check-label" for="remember_me">
                            <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" />
                            Remember me
                        </label>
                        <a class="auth-link" href="?page=login&action=forgot">Forgot password?</a>
                    </div>
<?php if (isset($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
<?php endif; ?>
                    <button type="submit" class="btn btn-primary auth-submit w-100">Login</button>
                </form>
            </div>
        </div>
        <!-- /login form -->
