
                <!-- log events -->
                <div class="container-fluid mt-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="mb-0">Log events</h2>
                            <small>events recorded in the Jilo monitoring platform</small>
                        </div>
                    </div>

                    <!-- Tabs navigation -->
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link <?= $scope === 'user' ? 'active' : '' ?>" href="?page=logs&tab=user">
                                Logs for current user
                            </a>
                        </li>
<?php if ($has_system_access) { ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $scope === 'system' ? 'active' : '' ?>" href="?page=logs&tab=system">
                                Logs for all users
                            </a>
                        </li>
<?php } ?>
                    </ul>

                    <!-- logs filter -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="" class="row g-3 align-items-end">
                                <input type="hidden" name="page" value="logs">
                                <input type="hidden" name="tab" value="<?= htmlspecialchars($scope) ?>">

                                <div class="col-md-3">
                                    <label for="from_time" class="form-label">From date</label>
                                    <input type="date" class="form-control" id="from_time" name="from_time" value="<?= htmlspecialchars($_REQUEST['from_time'] ?? '') ?>">
                                </div>

                                <div class="col-md-3">
                                    <label for="until_time" class="form-label">Until date</label>
                                    <input type="date" class="form-control" id="until_time" name="until_time" value="<?= htmlspecialchars($_REQUEST['until_time'] ?? '') ?>">
                                </div>

<?php if ($scope === 'system') { ?>
                                <div class="col-md-2">
                                    <label for="id" class="form-label">User ID</label>
                                    <input type="text" class="form-control" id="id" name="id" value="<?= htmlspecialchars($_REQUEST['id'] ?? '') ?>" placeholder="Enter user ID">
                                </div>
<?php } ?>

                                <div class="col-md">
                                    <label for="message" class="form-label">Message</label>
                                    <input type="text" class="form-control" id="message" name="message" value="<?= htmlspecialchars($_REQUEST['message'] ?? '') ?>" placeholder="Search in log messages">
                                </div>

                                <div class="col-md-auto">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                    <a href="?page=logs&tab=<?= htmlspecialchars($scope) ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /logs filter -->

                    <!-- logs -->
<?php if ($time_range_specified) { ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-calendar-alt me-2"></i>Time period: <strong><?= htmlspecialchars($from_time) ?> - <?= htmlspecialchars($until_time) ?></strong>
                    </div>
<?php } ?>
                    <div class="mb-5">
<?php if (!empty($logs['records'])) { ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
<?php if ($scope === 'system') { ?>
                                        <th>Username (id)</th>
<?php } ?>
                                        <th>Time</th>
                                        <th>Log message</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php     foreach ($logs['records'] as $row) { ?>
                                    <tr>
<?php         if ($scope === 'system') { ?>
                                        <td><strong><?= htmlspecialchars($row['username']) ?> (<?= htmlspecialchars($row['userID']) ?>)</strong></td>
<?php         } ?>
                                        <td><span class="text-muted"><?= date('d M Y H:i', strtotime($row['time'])) ?></span></td>
                                        <td><?= htmlspecialchars($row['log message']) ?></td>
                                    </tr>
<?php     } ?>
                                </tbody>
                            </table>
                        </div>
<?php include '../app/templates/pagination.php'; ?>
<?php } else { ?>
                        <div class="alert alert-info m-3">
                            <i class="fas fa-info-circle me-2"></i>No log entries found for the specified criteria.
                        </div>
<?php } ?>
                    </div>
                </div>
                <!-- /log events -->
