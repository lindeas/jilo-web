
                <!-- jitsi components events -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-5">
                            <h2 class="mb-0">Jitsi components events</h2>
                            <small>log events related to Jitsi Meet components like Jicofo, Videobridge, Jigasi, etc.</small>
                        </div>
                        <div class="row mb-4">

                            <!-- component events filter -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form method="get" action="" class="row g-3 align-items-end">
                                        <input type="hidden" name="page" value="components">
                                        <div class="col-md-auto">
                                            <label for="from_time" class="form-label">From date</label>
                                            <input type="date" class="form-control" id="from_time" name="from_time" value="<?= htmlspecialchars($_REQUEST['from_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-auto">
                                            <label for="until_time" class="form-label">Until date</label>
                                            <input type="date" class="form-control" id="until_time" name="until_time" value="<?= htmlspecialchars($_REQUEST['until_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="name" class="form-label">Component name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_REQUEST['name'] ?? '') ?>" placeholder="Component name">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="id" name="id" value="<?= htmlspecialchars($_REQUEST['id'] ?? '') ?>" placeholder="Search in component IDs">
                                            <input type="text" class="form-control" id="event" name="event" value="<?= htmlspecialchars($_REQUEST['event'] ?? '') ?>" placeholder="Search in event messages">
                                        </div>
                                        <div class="col-md-auto align-middle">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                            <a href="?page=components" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /component events filter -->

                            <!-- component events -->
<?php if ($time_range_specified) { ?>
                            <div class="alert alert-info m-0 mb-3 small">
                                <i class="fas fa-calendar-alt me-2"></i>Time period: <strong><?= htmlspecialchars($from_time) ?> - <?= htmlspecialchars($until_time) ?></strong>
                            </div>
<?php } ?>
                            <div class="mb-5">
<?php if (!empty($components['records'])) { ?>
                                <div class="table-responsive border">
                                    <table class="table table-results table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>component</th>
                                                <th>log level</th>
                                                <th>time</th>
                                                <th>component ID</th>
                                                <th>event</th>
                                                <th>parameter</th>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php     foreach ($components['records'] as $row) { ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&name=<?= htmlspecialchars($row['component'] ?? '') ?>">
                                                        <?= htmlspecialchars($row['component'] ?? '') ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($row['loglevel']) ?></td>
                                                <td><span class="text-muted"><?= date('d M Y H:i', strtotime($row['time'])) ?></span></td>
                                                <td>
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&id=<?= htmlspecialchars($row['component ID'] ?? '') ?>">
                                                        <?= htmlspecialchars($row['component ID'] ?? '') ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($row['event']) ?></td>
                                                <td><?= htmlspecialchars($row['param']) ?></td>
                                            </tr>
<?php     } ?>
                                        </tbody>
                                    </table>
                                </div>
<?php include '../app/templates/pagination.php'; ?>
<?php } else { ?>
                                <div class="alert alert-danger m-0">
                                    <i class="fas fa-info-circle me-2"></i>No component events found for the specified criteria.
                                </div>
<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /jitsi components events -->
