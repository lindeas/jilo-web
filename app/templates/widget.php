
                <div class="row">
<?php if ($widget['collapsible'] === true) { ?>
                    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= $widget['name'] ?>" role="button" aria-expanded="true" aria-controls="collapse<?= $widget['name'] ?>">
                        <div class="card w-auto bg-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } else { ?>
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } ?>
<?php if ($widget['filter'] === true) {
    include '../app/templates/block-results-filter.php'; } ?>
<?php if ($widget['collapsible'] === true) { ?>
                    </a>
<?php } ?>
                </div>

                <!-- widget "<?= $widget['name']; ?>" -->
                <div class="collapse show" id="collapse<?= $widget['name'] ?>">
<?php if ($time_range_specified) { ?>
                    <p class="m-3">time period: <strong><?= $from_time ?> - <?= $until_time ?></strong></p>
<?php } ?>
                    <div class="mb-5">
<?php if ($widget['full'] === true) { ?>
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
<?php     foreach ($widget['table_headers'] as $header) { ?>
                                    <th scope="col"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                                <tr>
<?php       $stats_id = false;
            $participant_ip = false;
            if (isset($row['event']) && $row['event'] === 'stats_id') $stats_id = true;
            if (isset($row['event']) && $row['event'] === 'pair selected') $participant_ip = true;
            foreach ($row as $key => $column) {
                    if ($key === 'conference ID' && isset($conferenceId) && $conferenceId === $column) { ?>
                                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($key === 'conference ID') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=conferences&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'conference name' && isset($conferenceName) && $conferenceName === $column) { ?>
                                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($key === 'conference name') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=conferences&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'participant ID' && isset($participantId) && $participantId === $column) { ?>
                                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($key === 'participant ID') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component ID') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=components&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($stats_id && $key === 'parameter' && isset($participantName) && $participantName === $column) { ?>
                                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($stats_id && $key === 'parameter') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($participant_ip && $key === 'parameter' && isset($participantIp) && $participantIp === $column) { ?>
                                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php               } elseif ($participant_ip && $key === 'parameter') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&ip=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=components&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } else { ?>
                                    <td><?= htmlspecialchars($column ?? '') ?></td>
<?php               }
                } ?>
                                </tr>
<?php     } ?>
                            </tbody>
                        </table>
<?php } else { ?>
                    <p class="m-3">No matching records found.</p>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= $widget['name']; ?>" -->
