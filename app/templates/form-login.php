        <!-- login form -->
        <div class="card text-center w-50 mx-auto">
            <h2 class="card-header">Login</h2>
            <div class="card-body">
                <p class="card-text"><strong>Welcome to JILO!</strong><br />Please enter login credentials:</p>
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=login">
<?php include 'csrf_token.php'; ?>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username".
                            pattern="[a-zA-Z0-9_-]{3,20}" title="3-20 characters, letters, numbers, - and _"
                            required autofocus />
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password".
                            pattern=".{2,}" title="Eight or more characters"
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
            </div>
        </div>
        <!-- /login form -->
