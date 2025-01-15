
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
                                    <th scope="col">
                                        <?= htmlspecialchars($record['table_headers']) ?>
                                        <?php if ($record['timestamp']) { ?>
                                            <br>
                                            <small class="text-muted">as of <?= date('Y-m-d H:i:s', strtotime($record['timestamp'])) ?></small>
                                        <?php } ?>
                                    </th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['metrics'] as $section => $section_metrics) { ?>
                                <tr class="table-secondary">
                                    <th colspan="<?= count($widget['records']) + 1 ?>"><?= htmlspecialchars($section) ?></th>
                                </tr>
<?php         foreach ($section_metrics as $metric => $metricConfig) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($metricConfig['label']) ?></td>
<?php             foreach ($widget['records'] as $record) { ?>
                                    <td>
                                        <?php if (isset($record['metrics'][$section][$metric])) { 
                                            $metric_data = $record['metrics'][$section][$metric];
                                            if ($metric_data['link']) { ?>
                                                <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=<?= htmlspecialchars($metric_data['link']) ?>&from_time=<?= htmlspecialchars($record['timestamp']) ?>&until_time=<?= htmlspecialchars($record['timestamp']) ?>"><?= htmlspecialchars($metric_data['value']) ?></a>
                                            <?php } else { ?>
                                                <?= htmlspecialchars($metric_data['value']) ?>
                                            <?php }
                                        } else { ?>
                                            <span class="text-muted">No data</span>
                                        <?php } ?>
                                    </td>
<?php             } ?>
                                </tr>
<?php         } ?>
<?php     } ?>
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
