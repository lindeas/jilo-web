<?php
/**
 * Two-factor authentication setup template
 */
?>

<div class="tm-cred-card mx-auto">
    <div class="tm-profile-header">
        <p class="tm-profile-eyebrow">Security</p>
        <h2 class="tm-profile-title">Set up two-factor authentication</h2>
        <p class="tm-profile-subtitle">Protect your account with an extra verification step whenever you sign in.</p>
    </div>

    <div class="tm-cred-intro alert alert-info">
        Two-factor authentication adds an extra layer of protection. After setup, you will sign in with both your password and a code from your authenticator app.
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($setupData) && is_array($setupData)): ?>
        <div class="tm-cred-steps">
            <div class="tm-cred-step">
                <h3>1. Install an authenticator app</h3>
                <p>Use any TOTP-compatible app such as Google Authenticator, Microsoft Authenticator, or Authy.</p>
            </div>

            <div class="tm-cred-step">
                <h3>2. Scan the QR code</h3>
                <p>Open your authenticator app and scan the QR code below.</p>
                <div class="tm-cred-qr">
                    <div id="qrcode"></div>
                    <div class="tm-cred-secret">
                        <small>Can&apos;t scan? Enter this code manually:</small>
                        <code><?php echo htmlspecialchars($setupData['secret']); ?></code>
                    </div>
                </div>
            </div>

            <div class="tm-cred-step">
                <h3>3. Verify setup</h3>
                <p>Enter the 6-digit code shown in your authenticator app.</p>
                <form method="post" action="?page=credentials&item=2fa&action=setup" class="tm-cred-form" novalidate>
                    <div class="mb-3">
                        <label for="setup_code" class="form-label">One-time code</label>
                        <input type="text"
                               id="setup_code"
                               name="code"
                               class="form-control"
                               pattern="[0-9]{6}"
                               maxlength="6"
                               required
                               placeholder="000000">
                    </div>

                    <input type="hidden" name="secret" value="<?php echo htmlspecialchars($setupData['secret']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <button type="submit" class="btn btn-primary tm-contact-submit w-100">
                        Verify and enable 2FA
                    </button>
                </form>
            </div>

            <div class="tm-cred-step">
                <h3>Backup codes</h3>
                <p class="text-danger mb-3">
                    Save these codes somewhere secure. Each code can be used once if you lose access to your authenticator app.
                </p>
                <div class="tm-cred-backup">
                    <?php foreach ($setupData['backupCodes'] as $code): ?>
                        <code><?php echo htmlspecialchars($code); ?></code>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-outline-secondary mt-3" onclick="window.print()">
                    Print backup codes
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Failed to generate 2FA setup data. Please try again.
        </div>
        <a href="?page=credentials" class="btn btn-primary">Back to credentials</a>
    <?php endif; ?>
</div>

<?php if (isset($setupData) && is_array($setupData)): ?>
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
