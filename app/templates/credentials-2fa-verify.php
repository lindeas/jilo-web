<?php
/**
 * Two-factor authentication verification template
 */
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Two-factor authentication</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <p>Enter the 6-digit code from your authenticator app:</p>

                    <form method="post" action="?page=credentials&action=verify" class="mt-3">
                        <div class="form-group">
                            <input type="text" 
                                   name="code" 
                                   class="form-control form-control-lg text-center" 
                                   pattern="[0-9]{6}" 
                                   maxlength="6"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   required
                                   autofocus
                                   placeholder="000000">
                        </div>

                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                        <button type="submit" class="btn btn-primary btn-block mt-4">
                            Verify code
                        </button>
                    </form>

                    <div class="mt-4">
                        <p class="text-muted text-center">
                            Lost access to your authenticator app?<br>
                            <a href="#" data-toggle="collapse" data-target="#backupCodeForm">
                                Use a backup code
                            </a>
                        </p>

                        <div class="collapse mt-3" id="backupCodeForm">
                            <form method="post" action="?page=credentials&action=verify" class="mt-3">
                                <div class="form-group">
                                    <label>Enter backup code:</label>
                                    <input type="text" 
                                           name="backup_code" 
                                           class="form-control"
                                           pattern="[a-f0-9]{8}"
                                           maxlength="8"
                                           required
                                           placeholder="Enter backup code">
                                </div>

                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

                                <button type="submit" class="btn btn-secondary btn-block">
                                    Use backup code
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-submit when 6 digits are entered
document.querySelector('input[name="code"]').addEventListener('input', function(e) {
    if (e.target.value.length === 6 && e.target.checkValidity()) {
        e.target.form.submit();
    }
});
</script>
