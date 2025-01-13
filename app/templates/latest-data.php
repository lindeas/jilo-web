
                <div class="row">
                    <div class="card w-auto bg-light border-light card-body" style="flex-direction: row;"><?= $widget['title'] ?></div>
                </div>

                <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
                    <div class="mb-5">
<?php if ($widget['full'] === true) { ?>
                        <table class="table table-results table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Metric</th>
<?php     foreach ($widget['records'] as $record) { ?>
                                    <th scope="col"><?= htmlspecialchars($record['table_headers']) ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Conferences</td>
<?php     foreach ($widget['records'] as $record) { ?>
                                    <td>
                                        <?php if (isset($record['conferences'])) { ?>
                                            <?php if ($record['conferences'] !== null) { ?>
                                                <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=conferences&from_time=<?= htmlspecialchars($record['from_time']) ?>&until_time=<?= htmlspecialchars($record['until_time']) ?>"><?= htmlspecialchars($record['conferences']) ?></a>
                                                <br>
                                                <small class="text-muted"><?= date('Y-m-d H:i:s', strtotime($record['from_time'])) ?></small>
                                            <?php } else { ?>
                                                <span class="text-muted">0</span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="text-muted">No data</span>
                                        <?php } ?>
                                    </td>
<?php     } ?>
                                </tr>
                                <tr>
                                    <td>Participants</td>
<?php     foreach ($widget['records'] as $record) { ?>
                                    <td>
                                        <?php if (isset($record['participants'])) { ?>
                                            <?php if ($record['participants'] !== null) { ?>
                                                <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=participants&from_time=<?= htmlspecialchars($record['from_time']) ?>&until_time=<?= htmlspecialchars($record['until_time']) ?>"><?= htmlspecialchars($record['participants']) ?></a>
                                                <br>
                                                <small class="text-muted"><?= date('Y-m-d H:i:s', strtotime($record['from_time'])) ?></small>
                                            <?php } else { ?>
                                                <span class="text-muted">0</span>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <span class="text-muted">No data</span>
                                        <?php } ?>
                                    </td>
<?php     } ?>
                                </tr>
                            </tbody>
                        </table>
<?php } else { ?>
                        <div class="alert alert-info m-3" role="alert">
                            No data available from any agents. Please check agent configuration and connectivity.
                        </div>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= htmlspecialchars($widget['name']) ?>" -->
