<!-- Security Settings -->
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Security Settings</h2>
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            <ul class="nav nav-tabs">
                <?php if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit whitelist')) { ?>
                <li class="nav-item">
                    <a class="nav-link <?= $section === 'whitelist' ? 'active' : '' ?>" href="?page=security&section=whitelist">IP Whitelist</a>
                </li>
                <?php } ?>
                <?php if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit blacklist')) { ?>
                <li class="nav-item">
                    <a class="nav-link <?= $section === 'blacklist' ? 'active' : '' ?>" href="?page=security&section=blacklist">IP Blacklist</a>
                </li>
                <?php } ?>
                <?php if ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit ratelimiting')) { ?>
                <li class="nav-item">
                    <a class="nav-link <?= $section === 'ratelimit' ? 'active' : '' ?>" href="?page=security&section=ratelimit">Rate Limiting</a>
                </li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <?php if ($section === 'whitelist' && ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit whitelist'))) { ?>
    <!-- Whitelist Section -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3>IP Whitelist</h3>
                    IP addresses and networks that will always bypass the ratelimiting login checks.
                </div>
                <div class="card-body">
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add_whitelist">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="ip_address" placeholder="IP Address or CIDR" required>
                            </div>
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="description" placeholder="Description">
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_network" id="is_network_white">
                                    <label class="form-check-label" for="is_network_white">Is Network</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Add to Whitelist</button>
                            </div>
                        </div>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Network</th>
                                <th>Description</th>
                                <th>Added By</th>
                                <th>Added On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($whitelisted as $ip) { ?>
                            <tr>
                                <td><?= htmlspecialchars($ip['ip_address']) ?></td>
                                <td><?= $ip['is_network'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($ip['description']) ?></td>
                                <td><?= htmlspecialchars($ip['created_by']) ?></td>
                                <td><?= htmlspecialchars($ip['created_at']) ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_whitelist">
                                        <input type="hidden" name="ip_address" value="<?= htmlspecialchars($ip['ip_address']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this IP from whitelist?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php if ($section === 'blacklist' && ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit blacklist'))) { ?>
    <!-- Blacklist Section -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3>IP Blacklist</h3>
                    IP addresses and networks that will always get blocked at login.
                </div>
                <div class="card-body">
                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="add_blacklist">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="ip_address" placeholder="IP Address or CIDR" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="reason" placeholder="Reason">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="expiry_hours" placeholder="Expiry (hours)">
                            </div>
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="is_network" id="is_network_black">
                                    <label class="form-check-label" for="is_network_black">Is Network</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Add to Blacklist</button>
                            </div>
                        </div>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Network</th>
                                <th>Reason</th>
                                <th>Added By</th>
                                <th>Added On</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blacklisted as $ip) { ?>
                            <tr>
                                <td><?= htmlspecialchars($ip['ip_address']) ?></td>
                                <td><?= $ip['is_network'] ? 'Yes' : 'No' ?></td>
                                <td><?= htmlspecialchars($ip['reason']) ?></td>
                                <td><?= htmlspecialchars($ip['created_by']) ?></td>
                                <td><?= htmlspecialchars($ip['created_at']) ?></td>
                                <td><?= $ip['expiry_time'] ? htmlspecialchars($ip['expiry_time']) : 'Never' ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_blacklist">
                                        <input type="hidden" name="ip_address" value="<?= htmlspecialchars($ip['ip_address']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to remove this IP from blacklist?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php if ($section === 'ratelimit' && ($userObject->hasRight($user_id, 'superuser') || $userObject->hasRight($user_id, 'edit ratelimiting'))) { ?>
    <!-- Rate Limiting Section -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h3>Rate Limiting Settings</h3>
                    Restricts brute force or flooding attempts at login page.
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4>Current Settings</h4>
                        <ul>
                            <li>Maximum login attempts: <?= $rateLimiter->maxAttempts ?></li>
                            <li>Time window: <?= $rateLimiter->decayMinutes ?> minutes</li>
                            <li>Auto-blacklist threshold: <?= $rateLimiter->autoBlacklistThreshold ?> attempts</li>
                            <li>Auto-blacklist duration: <?= $rateLimiter->autoBlacklistDuration ?> hours</li>
                        </ul>
                        <p class="mb-0">
                            <small>Note: These settings can be modified in the RateLimiter class configuration.</small>
                        </p>
                    </div>

                    <h4>Recent Failed Login Attempts</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Username</th>
                                <th>Attempted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $rateLimiter->db->prepare("
                                SELECT ip_address, username, attempted_at 
                                FROM {$rateLimiter->ratelimitTable} 
                                ORDER BY attempted_at DESC 
                                LIMIT 10
                            ");
                            $stmt->execute();
                            $attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($attempts as $attempt) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($attempt['ip_address']) ?></td>
                                <td><?= htmlspecialchars($attempt['username']) ?></td>
                                <td><?= htmlspecialchars($attempt['attempted_at']) ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
<!-- /Security Settings -->

<script>
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
