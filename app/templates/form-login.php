        <!-- login form -->
        <div class="card text-center w-50 mx-auto">
            <h2 class="card-header">Login</h2>
            <div class="card-body">
                <p class="card-text"><strong>Welcome to <?= htmlspecialchars($config['site_name']); ?>!</strong><br />Please enter login credentials:</p>
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=login">
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
                        <label for="remember_me">
                            <input type="checkbox" id="remember_me" name="remember_me" />
                            remember me
                        </label>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Login" />
                </form>
                <div class="mt-3">
                    <a href="?page=login&action=forgot">forgot password?</a>
                </div>
            </div>
        </div>
        <!-- /login form -->
