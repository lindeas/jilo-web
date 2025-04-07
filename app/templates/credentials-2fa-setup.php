<?php
/**
 * Two-factor authentication setup template
 */
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Set up two-factor authentication</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p>Two-factor authentication adds an extra layer of security to your account. Once enabled, you'll need to enter both your password and a code from your authenticator app when signing in.</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($setupData)): ?>
                        <div class="setup-steps">
                            <h4>1. Install an authenticator app</h4>
                            <p>If you haven't already, install an authenticator app on your mobile device:</p>
                            <ul>
                                <li>Google Authenticator</li>
                                <li>Microsoft Authenticator</li>
                                <li>Authy</li>
                            </ul>

                            <h4 class="mt-4">2. Scan the QR code</h4>
                            <p>Open your authenticator app and scan this QR code:</p>

                            <div class="text-center my-4">
                                <div id="qrcode"></div>
                                <div class="mt-2">
                                    <small class="text-muted">Can't scan? Use this code instead:</small><br>
                                    <code class="secret-key"><?php echo htmlspecialchars($setupData['secret']); ?></code>
                                </div>
                            </div>

                            <h4 class="mt-4">3. Verify setup</h4>
                            <p>Enter the 6-digit code from your authenticator app to verify the setup:</p>

                            <form method="post" action="?page=credentials&action=setup" class="mt-3">
                                <div class="form-group">
                                    <input type="text" 
                                           name="code" 
                                           class="form-control" 
                                           pattern="[0-9]{6}" 
                                           maxlength="6"
                                           required
                                           placeholder="Enter 6-digit code">
                                </div>

                                <input type="hidden" name="secret" value="<?php echo htmlspecialchars($setupData['secret']); ?>">

                                <button type="submit" class="btn btn-primary mt-3">
                                    Verify and enable 2FA
                                </button>
                            </form>

                            <div class="mt-4">
                                <h4>Backup codes</h4>
                                <p class="text-warning">
                                    <strong>Important:</strong> Save these backup codes in a secure place. 
                                    If you lose access to your authenticator app, you can use these codes to sign in.
                                    Each code can only be used once.
                                </p>
                                <div class="backup-codes bg-light p-3 rounded">
                                    <?php foreach ($setupData['backupCodes'] as $code): ?>
                                        <code class="d-block"><?php echo htmlspecialchars($code); ?></code>
                                    <?php endforeach; ?>
                                </div>
                                <button class="btn btn-secondary mt-2" onclick="window.print()">
                                    Print backup codes
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="post" action="?page=credentials&action=setup" class="mt-3">
                            <p>Click the button below to begin setting up two-factor authentication:</p>
                            <button type="submit" class="btn btn-primary">
                                Begin setup
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($setupData)): ?>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qrcode"), {
        text: <?php echo json_encode($setupData['otpauthUrl']); ?>,
        width: 200,
        height: 200
    });
});
</script>
<?php endif; ?>
