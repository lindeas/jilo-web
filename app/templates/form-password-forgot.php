
        <div class="auth-card mx-auto">
            <div class="auth-card-body">
                <div class="auth-header">
                    <p class="auth-eyebrow">Forgot password</p>
                    <h2 class="auth-title">Reset your access</h2>
                    <p class="auth-subtitle">Enter the email linked to your account and if it exists in our records we will send you reset instructions.</p>
                </div>
                <form method="post" action="?page=login&action=forgot" class="auth-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email address</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email"
                                   class="form-control"
                                   id="email"
                                   name="email"
                                   placeholder="you@example.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required
                                   autocomplete="email">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary auth-submit w-100">
                        Send reset instructions
                    </button>
                </form>
                <div class="mt-4 text-center">
                    <a class="auth-link" href="?page=login">Back to login</a>
                </div>
            </div>
        </div>

