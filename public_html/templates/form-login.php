        <!-- login form -->
        <div class="card text-center w-50 mx-auto">
            <h2 class="card-header">Login</h2>
            <div class="card-body">
                <p class="card-text"><strong>Welcome to JILO!</strong><br />Please enter login credentials:</p>
                <form method="POST" action="?page=login">
                    <input type="text" name="username" placeholder="Username" required />
                    <br />
                    <input type="password" name="password" placeholder="Password" required />
                    <br />
                    <label for="remember_me">
                        <input type="checkbox" id="remember_me" name="remember_me" />
                        remember me
                    </label>
                    <br />&nbsp;<br />
                    <input type="submit" class="btn btn-primary" value="Login" />
                </form>
            </div>
        </div>
        <!-- /login form -->
