        <!-- registration form -->
        <div class="card text-center w-50 mx-auto">
            <h2 class="card-header">Register</h2>
            <div class="card-body">
                <p class="card-text">Enter credentials for registration:</p>
                <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=register">
                    <input type="text" name="username" placeholder="Username" required autofocus />
                    <br />
                    <input type="password" name="password" placeholder="Password" required />
                    <br />
                    <input type="password" name="confirm_password" placeholder="Confirm password" required />
                    <br />&nbsp;<br />
                    <input type="submit" class="btn btn-primary" value="Register" />
                </form>
            </div>
        </div>
        <!-- /registration form -->
