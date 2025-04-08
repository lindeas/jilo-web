
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mt-5">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Reset password</h3>
                            <p>Enter your email address and we will send you<br />
                                instructions to reset your password.</p>
                            <form method="post" action="?page=login&action=forgot">
<?php include 'csrf_token.php'; ?>
                                <div class="form-group">
                                    <label for="email">email address:</label>
                                    <input type="email"
                                           class="form-control"
                                           id="email"
                                           name="email"
                                           required
                                           autocomplete="email">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block mt-4">
                                    Send reset instructions
                                </button>
                            </form>
                            <div class="mt-3 text-center">
                                <a href="?page=login">back to login</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
