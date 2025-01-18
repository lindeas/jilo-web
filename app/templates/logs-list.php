<!-- log events -->
<div class="container-fluid mt-2">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-3"><?= htmlspecialchars($widget['title']) ?></h2>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?= $widget['scope'] === 'user' ? 'active' : '' ?>" href="?page=logs&tab=user">
                        Logs for current user
                    </a>
                </li>
<?php if ($widget['has_system_access']) { ?>
                <li class="nav-item">
                    <a class="nav-link <?= $widget['scope'] === 'system' ? 'active' : '' ?>" href="?page=logs&tab=system">
                        Logs for all users
                    </a>
                </li>
<?php } ?>
            </ul>

<?php if ($widget['filter'] === true) {
    include '../app/templates/logs-filter.php';
} ?>

            <!-- widget "<?= htmlspecialchars($widget['name']) ?>" -->
            <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
<?php if ($time_range_specified) { ?>
                <div class="alert alert-info m-3">
                    <i class="fas fa-calendar-alt me-2"></i>Time period: <strong><?= htmlspecialchars($from_time) ?> - <?= htmlspecialchars($until_time) ?></strong>
                </div>
<?php } ?>
                <div class="mb-5">
<?php if ($widget['full'] === true) { ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
<?php if ($widget['scope'] === 'system') { ?>
                                    <th>Username (id)</th>
<?php } ?>
                                    <th>Time</th>
                                    <th>Log message</th>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                                <tr>
<?php         if ($widget['scope'] === 'system') { ?>
                                    <td><strong><?= htmlspecialchars($row['username']) ?> (<?= htmlspecialchars($row['userID']) ?>)</strong></td>
<?php         } ?>
                                    <td><span class="text-muted"><?= date('d M Y H:i', strtotime($row['time'])) ?></span></td>
                                    <td><?= htmlspecialchars($row['log message']) ?></td>
                                </tr>
<?php     } ?>
                            </tbody>
                        </table>
                    </div>
<?php
if ($widget['pagination'] === true) {
    include '../app/templates/pagination.php';
}
?>
<?php } else { ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>No log entries found for the specified criteria.
                    </div>
<?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /log events -->
