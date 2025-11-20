
                <div class="row">
<?php if ($widget['collapsible'] === true) { ?>
                    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= htmlspecialchars($widget['name']) ?>" role="button" aria-expanded="true" aria-controls="collapse<?= htmlspecialchars($widget['name']) ?>">
                        <div class="card w-auto bg-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } else { ?>
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } ?>
<?php if ($widget['collapsible'] === true) { ?>
                    </a>
<?php } ?>
                </div>

                <!-- widget "<?= htmlspecialchars($widget['name']) ?>" -->
                <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
<?php if ($time_range_specified) { ?>
                    <p class="m-3">time period:
                        <strong>
                            <?= $from_time == '0000-01-01' ? 'beginning' : date('d M Y', strtotime($from_time)) ?> - <?= $until_time == '9999-12-31' ? 'now' : date('d M Y', strtotime($until_time)) ?>
                        </strong>
                    </p>
<?php } ?>
                    <div class="mb-5">
<?php if ($widget['full'] === true) { ?>
                        <table class="table table-results table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
<?php     foreach ($widget['table_headers'] as $header) { ?>
                                    <th scope="col" class="text-nowrap"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                                <tr>
<?php        foreach ($row as $key => $column) {
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
<?php               } elseif ($key === 'conference host') { ?>
                                    <td class="text-nowrap">
                                        <span <?= (strlen($column ?? '') > 30) ? 'data-toggle="tooltip" title="' . htmlspecialchars($column) . '"' : '' ?>>
                                            <?= htmlspecialchars(strlen($column ?? '') > 30 ? substr($column, 0, 30) . '...' : $column ?? '') ?>
                                        </span>
                                    </td>
<?php               } elseif ($key === 'component ID') { ?>
                                    <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component') { ?>
                                    <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php
                    } elseif ($key === 'start' || $key === 'end') { ?>
                                    <td class="text-nowrap"><?= !empty($column) ? date('d M Y H:i:s',strtotime($column)) : '<small class="text-muted">n/a</small>' ?></td>
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
                <!-- /widget "<?= htmlspecialchars($widget['name']) ?>" -->
