        <!-- registration form -->
        <div class="card text-center w-50 mx-auto">
            <h2 class="card-header">Register</h2>
            <div class="card-body">
                <p class="card-text">Enter credentials for registration:</p>
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=register">
<?php include CSRF_TOKEN_INCLUDE; ?>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control w-50 mx-auto" name="username" placeholder="Username"
                            pattern="[A-Za-z0-9_\-]{3,20}" title="3-20 characters, letters, numbers, - and _"
                            required autofocus />
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" class="form-control w-50 mx-auto" name="password" placeholder="Password"
                            pattern=".{8,}" title="Eight or more characters"
                            required />
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" class="form-control w-50 mx-auto" name="confirm_password" placeholder="Confirm password"
                            pattern=".{8,}" title="Eight or more characters"
                            required />
                    </div>
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <label class="form-check-label" for="terms">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                I agree to the <a href="<?= htmlspecialchars($app_root) ?>?page=terms" target="_blank">terms & conditions</a> and <a href="<?= htmlspecialchars($app_root) ?>?page=privacy" target="_blank">privacy policy</a>
                            </label>
                        </div>
                        <small class="text-muted mt-2">
                            We use cookies to improve your experience. See our <a href="<?= htmlspecialchars($app_root) ?>?page=cookies" target="_blank">cookies policy</a>
                        </small>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Register" />
                </form>
            </div>
        </div>
        <!-- /registration form -->
