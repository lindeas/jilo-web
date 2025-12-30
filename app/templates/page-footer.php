
<?php if ($page !== 'login' && $page !== 'register') { ?>
            <!-- /Main content -->
            </div>

        </div>
<?php } ?>

<?php
        // Preparing the remaining session time debug message
        $sessionDebugMarkup = '';
        if (Session::getUsername()) {
            $canSeeSessionDebug = false;
            if (isset($userId, $userObject) && method_exists($userObject, 'hasRight')) {
                $canSeeSessionDebug = ($userId === 1) || (bool)$userObject->hasRight($userId, 'superuser');
            }

            if ($canSeeSessionDebug) {
                Session::startSession();
                $remember = !empty($_SESSION['REMEMBER_ME']);
                $timeoutSeconds = $remember ? (30 * 24 * 60 * 60) : 7200;
                $lastActivity = $_SESSION['LAST_ACTIVITY'] ?? null;

                $remainingLabel = 'Session activity timestamp unavailable.';
                $expiresAtLabel = 'unknown expiry';

                if ($lastActivity !== null) {
                    $elapsed = time() - (int)$lastActivity;
                    $secondsRemaining = max(0, $timeoutSeconds - $elapsed);

                    $days = intdiv($secondsRemaining, 86400);
                    $hours = intdiv($secondsRemaining % 86400, 3600);
                    $minutes = intdiv($secondsRemaining % 3600, 60);
                    $seconds = $secondsRemaining % 60;

                    $parts = [];
                    if ($days > 0) {
                        $parts[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
                    }
                    if ($hours > 0) {
                        $parts[] = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
                    }
                    if ($minutes > 0) {
                        $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
                    }
                    if ($seconds > 0 || empty($parts)) {
                        $parts[] = $seconds . ' ' . ($seconds === 1 ? 'second' : 'seconds');
                    }

                    $remainingLabel = implode(' ', $parts);
                    $expiresAtLabel = date('Y-m-d H:i:s', time() + $secondsRemaining);
                }

                ob_start();
?>
            <span class="tm-session-debug">
                <strong>Session debug:</strong>
                <?= $remember ? 'Remember-me' : 'Standard' ?> session expires in <?= htmlspecialchars($remainingLabel) ?> (<?= htmlspecialchars($expiresAtLabel) ?>)
            </span>
<?php
                $sessionDebugMarkup = ob_get_clean();
            }
        }
?>

        <!-- Footer -->
        <div id="footer">
            &laquo; <?= htmlspecialchars($config['site_name'] . (!empty($config['site_slogan']) ? ' - ' . ucfirst($config['site_slogan']) : '')) ?> &raquo; 
            v.<?= htmlspecialchars($config['version']) ?> &copy; 2024-<?= date('Y') ?> &mdash; web interface for <a href="https://lindeas.com/jilo">Jilo</a>
<?php if ($sessionDebugMarkup !== ''): ?>
            <?= $sessionDebugMarkup ?>
<?php endif; ?>
        </div>
        <!-- /Footer -->

<?php if (Session::getUsername() && $page !== 'logout') { ?>
    <script src="static/js/sidebar.js"></script>
<?php } ?>

<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>


<script>
// dismissible messages
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap alerts
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        var closeButton = alert.querySelector('.btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 150);
            });
        }
    });
});
</script>

    </body>
</html>
