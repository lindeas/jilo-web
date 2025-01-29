
                <!-- jitsi participants events -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-5">
                            <h2 class="mb-0">Jitsi participants events</h2>
                            <small>log events related to participants in Jitsi Meet conferences</small>
                        </div>
                        <div class="row mb-4">

                            <!-- participant events filter -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <form method="get" action="" class="row g-3 align-items-end">
                                        <input type="hidden" name="page" value="participants">
                                        <div class="col-md-auto">
                                            <label for="from_time" class="form-label">From date</label>
                                            <input type="date" class="form-control" id="from_time" name="from_time" value="<?= htmlspecialchars($_REQUEST['from_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-auto">
                                            <label for="until_time" class="form-label">Until date</label>
                                            <input type="date" class="form-control" id="until_time" name="until_time" value="<?= htmlspecialchars($_REQUEST['until_time'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <label for="name" class="form-label">Participant name</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($_REQUEST['name'] ?? '') ?>" placeholder="Participant name">
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" id="id" name="id" value="<?= htmlspecialchars($_REQUEST['id'] ?? '') ?>" placeholder="Search in participant IDs">
                                            <input type="text" class="form-control" id="ip" name="ip" value="<?= htmlspecialchars($_REQUEST['ip'] ?? '') ?>" placeholder="Search in participant IPs">
                                        </div>
                                        <div class="col-md-auto align-middle">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="fas fa-search me-2"></i>Search
                                            </button>
                                            <a href="?page=participants" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Clear
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- /participant events filter -->

                            <!-- participant events -->
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
<?php if (!empty($participants['records'])) { ?>
                                <div class="table-responsive border">
                                    <table class="table table-results table-hover">
                                        <thead class="table-light">
                                            <tr>
<?php     foreach (array_keys($participants['records'][0]) as $header) { ?>
                                                <th scope="col" class="text-nowrap"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
<?php     foreach ($participants['records'] as $row) { ?>
                                            <tr>
<?php       $stats_id = false;
            $participant_ip = false;
            if (isset($row['event']) && $row['event'] === 'stats_id') $stats_id = true;
            if (isset($row['event']) && $row['event'] === 'pair selected') $participant_ip = true;
            foreach ($row as $key => $column) {
                    if ($key === 'conference ID') { ?>
                                                <td class="text-nowrap">
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&id=<?= htmlspecialchars($column ?? '') ?>"
                                                       <?= (strlen($column ?? '') > 16) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 16 ? substr($column, 0, 16) . '...' : $column ?? '') ?>
                                                    </a>
                                                </td>
<?php               } elseif ($key === 'conference name') { ?>
                                                <td class="text-nowrap">
                                                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&name=<?= htmlspecialchars($column ?? '') ?>"
                                                       <?= (strlen($column ?? '') > 16) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                                        <?= htmlspecialchars(strlen($column ?? '') > 16 ? substr($column, 0, 16) . '...' : $column ?? '') ?>
                                                    </a>
                                                </td>
<?php               } elseif ($key === 'participant ID' && isset($participantId) && $participantId === $column) { ?>
                                                <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($key === 'participant ID') { ?>
                                                <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component ID') { ?>
                                                <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($stats_id && $key === 'parameter' && isset($participantName) && $participantName === $column) { ?>
                                                <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($stats_id && $key === 'parameter') { ?>
                                                <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($participant_ip && $key === 'parameter' && isset($participantIp) && $participantIp === $column) { ?>
                                                <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($participant_ip && $key === 'parameter') { ?>
                                                <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants&ip=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component') { ?>
                                                <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php
                    } elseif ($key === 'time') { ?>
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
                                    <i class="fas fa-info-circle me-2"></i>No participant events found for the specified criteria.
                                </div>
<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /jitsi participants events -->
