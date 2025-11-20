                <section class="tm-widget-card tm-call-widget">
                    <div class="tm-widget-header">
                        <div>
                            <p class="tm-widget-eyebrow">Conferences</p>
                            <h3 class="tm-widget-title">
                                <?= $widget['title'] ?>
                            </h3>
<?php if ($time_range_specified) { ?>
                            <p class="m-1 mb-0" style="font-size: 0.75rem;">time period:
                                <strong>
                                    <?= $from_time == '0000-01-01' ? 'beginning' : date('d M Y', strtotime($from_time)) ?> - <?= $until_time == '9999-12-31' ? 'now' : date('d M Y', strtotime($until_time)) ?>
                                </strong>
                            </p>
<?php } ?>
                        </div>
                        <div class="tm-widget-tools">
<?php if ($widget['filter'] === true) { include '../app/templates/block-results-filter.php'; } ?>
                        </div>
                    </div>

                    <!-- calls -->
                    <div class="tm-widget-body">
                            <div class="table-responsive">
                                <table class="table tm-widget-table" style="font-size: 0.75rem;">
                                    <thead>
                                        <tr>
                                            <th scope="col"></th>
<?php     foreach ($widget['records'] as $record) { ?>
                                            <th scope="col"><?= htmlspecialchars($record['table_headers']) ?></th>
<?php     } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php if (!empty($widget['records'])) { ?>
                                        <tr>
                                            <td>conferences</td>
<?php     foreach ($widget['records'] as $record) { ?>
                                            <td><?php if (!empty($record['conferences'])) { ?>
                                                <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&from_time=<?= htmlspecialchars($record['from_time']) ?>&until_time=<?= htmlspecialchars($record['until_time']) ?>"><?= htmlspecialchars($record['conferences']) ?></a> <?php } else { ?>
                                                0<?php } ?>
                                            </td>
<?php     } ?>
                                        </tr>
                                        <tr>
                                            <td>participants</td>
<?php     foreach ($widget['records'] as $record) { ?>
                                            <td><?php if (!empty($record['participants'])) { ?>
                                                <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants&from_time=<?= htmlspecialchars($record['from_time']) ?>&until_time=<?= htmlspecialchars($record['until_time']) ?>"><?= htmlspecialchars($record['participants']) ?></a> <?php } else { ?>
                                                0<?php } ?>
                                            </td>
<?php     } ?>
                                        </tr>
<?php } else { ?>
                                        <tr>
                                            <td colspan="6">
                                                <p class="tm-widget-empty">No matching records found.</p>
                                            </td>
                                        </tr>
<?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <!-- /monthly conferences -->
                </section>
