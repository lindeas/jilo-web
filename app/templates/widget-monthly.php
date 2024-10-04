
                <div class="row">

<?php if ($widget['collapsible'] === true) { ?>
                    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= htmlspecialchars($widget['name']) ?>" role="button" aria-expanded="true" aria-controls="collapse<?= htmlspecialchars($widget['name']) ?>">
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

                <!-- widget "<?= htmlspecialchars($widget['name']) ?>" -->
                <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
<?php if ($time_range_specified) { ?>
                    <p class="m-3">time period: <strong><?= htmlspecialchars($from_time) ?> - <?= htmlspecialchars($until_time) ?></strong></p>
<?php } ?>
                    <div class="mb-5">
<?php if ($widget['full'] === true) { ?>
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col"></th>
<?php     foreach ($widget['records'] as $record) { ?>
                                    <th scope="col"><?= htmlspecialchars($record['table_headers']) ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
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
                            </tbody>
                        </table>
<?php } else { ?>
                        <p class="m-3">No matching records found.</p>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= htmlspecialchars($widget['name']) ?>" -->
