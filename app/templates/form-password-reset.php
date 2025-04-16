
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card mt-5">
                        <div class="card-body">
                            <h3 class="card-title mb-4">Set new password</h3>
                            <form method="post" action="?page=login&action=reset&token=<?= htmlspecialchars(urlencode($token)) ?>">
<?php include CSRF_TOKEN_INCLUDE; ?>
                                <div class="form-group">
                                    <label for="new_password">new password:</label>
                                    <input type="password"
                                        class="form-control"
                                        id="new_password"
                                        name="new_password"
                                        required
                                        minlength="8"
                                        autocomplete="new-password">
                                </div>
                                <div class="form-group mt-3">
                                    <label for="confirm_password">confirm password:</label>
                                    <input type="password"
                                        class="form-control"
                                        id="confirm_password"
                                        name="confirm_password"
                                        required 
                                        minlength="8"
                                        autocomplete="new-password">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block mt-4">
                                    Set new password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
