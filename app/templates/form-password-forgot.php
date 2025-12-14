
        <div class="action-card">
    <div class="action-card-header">
        <p class="action-eyebrow">Account recovery</p>
        <h2 class="action-title">Forgot password</h2>
        <p class="action-subtitle">Enter your email address and we'll send you reset instructions if it exists in our records</p>
    </div>

    <div class="action-card-body">
        <form method="post" action="?page=login&action=forgot" class="action-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
            <div class="action-form-group">
                <label for="email" class="action-form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email"
                           class="form-control action-form-control"
                           id="email"
                           name="email"
                           placeholder="you@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required
                           autocomplete="email">
                </div>
            </div>

            <div class="action-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Send reset instructions
                </button>
            </div>
        </form>

        <div class="mt-4 text-center">
            <a href="?page=login" class="text-decoration-none">← Back to login</a>
        </div>
    </div>
</div>

