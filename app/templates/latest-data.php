
                <div class="row">
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
                </div>

                <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
                    <div class="mb-5">
                        <hr /><p class="m-3">NB: This functionality is still under development. The data is just an example.</p><hr /><!-- FIXME remove when implemented -->
<?php if ($widget['full'] === true) { ?>
                        <table class="table table-results table-striped table-hover table-bordered">
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
                        <p class="m-3">No records found.</p>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= htmlspecialchars($widget['name']) ?>" -->
