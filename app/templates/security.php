                <!-- security settings -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col">
                            <h2 class="mb-0">Security settings</h2>
                            <small>network restrictions to control flooding and brute force attacks</small>
                            <ul class="nav nav-tabs mt-5">
<?php if ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit whitelist')) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $section === 'whitelist' ? 'active' : '' ?>" href="?page=security&section=whitelist">IP whitelist</a>
                                </li>
<?php } ?>
<?php if ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit blacklist')) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $section === 'blacklist' ? 'active' : '' ?>" href="?page=security&section=blacklist">IP blacklist</a>
                                </li>
<?php } ?>
<?php if ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit ratelimiting')) { ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $section === 'ratelimit' ? 'active' : '' ?>" href="?page=security&section=ratelimit">Rate limiting</a>
                                </li>
<?php } ?>
                            </ul>
                        </div>
                    </div>

<?php if ($section === 'whitelist' && ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit whitelist'))) { ?>
                    <!-- whitelist section -->
                    <div class="row mb-4">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h3>IP whitelist</h3>
                                    IP addresses and networks that will always bypass the ratelimiting login checks.
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="mb-4">
<?php include CSRF_TOKEN_INCLUDE; ?>
                                        <input type="hidden" name="action" value="add_whitelist">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="ip_address" placeholder="IP address or CIDR" required>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" class="form-control" name="description" placeholder="Description">
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="is_network" id="is_network_white">
                                                    <label class="form-check-label" for="is_network_white">is network</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary">Add to whitelist</button>
                                            </div>
                                        </div>
                                    </form>

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>IP address</th>
                                                <th>Network</th>
                                                <th>Description</th>
                                                <th>Added by</th>
                                                <th>Added on</th>
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
<?php include CSRF_TOKEN_INCLUDE; ?>
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

<?php if ($section === 'blacklist' && ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit blacklist'))) { ?>
                    <!-- blacklist section -->
                    <div class="row mb-4">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h3>IP blacklist</h3>
                                    IP addresses and networks that will always get blocked at login.
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="mb-4">
<?php include CSRF_TOKEN_INCLUDE; ?>
                                        <input type="hidden" name="action" value="add_blacklist">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <input type="text" class="form-control" name="ip_address" placeholder="IP address or CIDR" required>
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
                                                    <label class="form-check-label" for="is_network_black">is network</label>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="submit" class="btn btn-primary">Add to blacklist</button>
                                            </div>
                                        </div>
                                    </form>

                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>IP address</th>
                                                <th>Network</th>
                                                <th>Reason</th>
                                                <th>Added by</th>
                                                <th>Added on</th>
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
<?php include CSRF_TOKEN_INCLUDE; ?>
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

<?php if ($section === 'ratelimit' && ($userObject->hasRight($userId, 'superuser') || $userObject->hasRight($userId, 'edit ratelimiting'))) { ?>
                    <!-- rate limiting section -->
                    <div class="row mb-4">
                        <div class="col">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Rate limiting settings</h3>
                                    Rate limiting settings control how many failed login attempts are allowed before blocking an IP address.
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <h4>Current settings</h4>
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

                                    <h4>Recent failed login attempts</h4>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>IP sddress</th>
                                                <th>Username</th>
                                                <th>Attempted at</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php $stmt = $rateLimiter->db->prepare("
    SELECT ip_address, username, attempted_at
    FROM {$rateLimiter->authRatelimitTable}
    ORDER BY attempted_at DESC
    LIMIT 10
");
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($attempts as $attempt) { ?>
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
                <!-- /security settings -->
