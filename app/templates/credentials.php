
                <!-- Two-Factor Authentication -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3>Two-factor authentication</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($has2fa): ?>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">
                                        <i class="fas fa-shield-alt text-success"></i>
                                        Two-factor authentication is enabled
                                    </p>
                                    <small class="text-muted">
                                        Your account is protected with an authenticator app
                                    </small>
                                </div>
                                <form method="post" class="ml-3">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="item" value="2fa">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Are you sure you want to disable two-factor authentication? This will make your account less secure.')">
                                        Disable 2FA
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0">
                                        <i class="fas fa-shield-alt text-muted"></i>
                                        Two-factor authentication is not enabled
                                    </p>
                                    <small class="text-muted">
                                        Add an extra layer of security to your account by requiring both your password and an authentication code
                                    </small>
                                </div>
                                <form method="post" class="ml-3">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <input type="hidden" name="item" value="2fa">
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" class="btn btn-primary">
                                        Enable 2FA
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
