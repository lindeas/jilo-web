
                <!-- jitsi conferences events -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-5">
                            <h2 class="mb-0">Jitsi conferences events</h2>
                            <small>log events related to conferences in Jitsi Meet</small>
                        </div>
                        <div class="row mb-4">

                            <!-- conference events filter -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form method="get" action="" class="row g-3 align-items-end">
                                        <input type="hidden" name="page" value="conferences">
                                        <div class="col-md-auto">
                                            <label for="from_time" class="form-label">From date</label>
                                            <input type="date" class="form-control" id="from_time" name="from_time" value="<?= htmlspecialchars($_REQUEST['from_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-auto">
                                            <label for="until_time" class="form-label">Until date</label>
                                            <input type="date" class="form-control" id="until_time" name="until_time" value="<?= htmlspecialchars($_REQUEST['until_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="name" class="form-label">Conference ID</label>
                                            <input type="text" class="form-control" id="id" name="name" value="<?= htmlspecialchars($_REQUEST['id'] ?? '') ?>" placeholder="Conference ID">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="name" class="form-label">Conference name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_REQUEST['name'] ?? '') ?>" placeholder="Search in conference names">
                                        </div>
                                        <div class="col-md-auto align-middle">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                            <a href="?page=conferences" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /conference events filter -->

                            <!-- conference events -->
<?php if ($time_range_specified || count($filterMessage)) { ?>
                            <div class="alert alert-info m-0 mb-3 small">
<?php   if ($time_range_specified) { ?>
                                <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Time period:
                                    <strong>
                                        <?= $from_time == '0000-01-01' ? 'beginning' : date('d M Y', strtotime($from_time)) ?> - <?= $until_time == '9999-12-31' ? 'now' : date('d M Y', strtotime($until_time)) ?>
                                    </strong>
                                </p>
<?php   } ?>
<?php   if (count($filterMessage)) {
          foreach ($filterMessage as $message) { ?>
                                <p class="mb-0"><i class="fas fa-users me-2"></i><?= $message ?></strong></p>
<?php     } ?>
<?php   } ?>
                            </div>
<?php } ?>

                            <div class="mb-5">
<?php if (!empty($conferences['records'])) { ?>
                                <div class="table-responsive border">
                                    <table class="table table-results table-hover">
                                        <thead class="table-light">
                                            <tr>
<?php     foreach (array_keys($conferences['records'][0]) as $header) { ?>
                                                <th scope="col" class="text-nowrap"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php     foreach ($conferences['records'] as $row) { ?>
                                            <tr>
<?php       foreach ($row as $key => $column) {
                    if ($key === 'conference ID' && isset($conferenceId) && $conferenceId === $column) { ?>
                                                <td class="text-nowrap">
                                                    <strong <?= (strlen($column ?? '') > 20) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 20 ? substr($column, 0, 20) . '...' : $column ?? '') ?>
                                                    </strong>
                                                </td>
<?php               } elseif ($key === 'conference ID') { ?>
                                                <td class="text-nowrap">
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&id=<?= htmlspecialchars($column ?? '') ?>"
                                                       <?= (strlen($column ?? '') > 16) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 16 ? substr($column, 0, 16) . '...' : $column ?? '') ?>
                                                    </a>
                                                </td>
<?php               } elseif ($key === 'conference name' && isset($conferenceName) && $conferenceName === $column) { ?>
                                                <td class="text-nowrap">
                                                    <strong <?= (strlen($column ?? '') > 20) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 20 ? substr($column, 0, 20) . '...' : $column ?? '') ?>
                                                    </strong>
                                                </td>
<?php               } elseif ($key === 'conference name') { ?>
                                                <td class="text-nowrap">
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&name=<?= htmlspecialchars($column ?? '') ?>"
                                                       <?= (strlen($column ?? '') > 16) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 16 ? substr($column, 0, 16) . '...' : $column ?? '') ?>
                                                    </a>
                                                </td>
<?php               } elseif ($key === 'conference host') { ?>
                                                <td class="text-nowrap">
                                                    <span <?= (strlen($column ?? '') > 30) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 30 ? substr($column, 0, 30) . '...' : $column ?? '') ?>
                                                    </span>
                                                </td>
<?php
                    } elseif ($key === 'time' || $key === 'start' || $key === 'end') { ?>
                                                <td class="text-nowrap"><?= !empty($column) ? date('d M Y H:i:s',strtotime($column)) : '<small class="text-muted">n/a</small>' ?></td>
<?php               } else { ?>
                                                <td><?= htmlspecialchars($column ?? '') ?></td>
<?php               }
            } ?>
                                            </tr>
<?php     } ?>
                                        </tbody>
                                    </table>
                                </div>
<?php include '../app/templates/pagination.php'; ?>
<?php } else { ?>
                                <div class="alert alert-danger m-0">
                                    <i class="fas fa-info-circle me-2"></i>No conference events found for the specified criteria.
                                </div>
<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /jitsi conferences events -->
