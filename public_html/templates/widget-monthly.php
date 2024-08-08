
                <div class="row">

<?php if ($widget['collapsible'] === true) { ?>
                    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= $widget['name'] ?>" role="button" aria-expanded="true" aria-controls="collapse<?= $widget['name'] ?>">
                        <div class="card w-auto bg-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } else { ?>
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } ?>
<?php if ($widget['filter'] === true) {
    include('templates/block-results-filter.php'); } ?>
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
                                    <th scope="col"></th>
<?php     foreach ($widget['records'] as $record) { ?>
                                    <th scope="col"><?= htmlspecialchars($record['table_headers']) ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>conferences</td>
<?php     foreach ($widget['records'] as $record) {
print_r($record);
//            $from_time = $record['fromMonth']->format('Y-m-d');
//            $until_time = $record['untilMonth']->format('Y-m-d');
?>
                                    <td><?php if (!empty($record['conferences'])) { ?>
                                        <a href="?page=conferences&from_time=<?= $record['from_time'] ?>&until_time=<?= $record['until_time'] ?>"><?= htmlspecialchars($record['conferences']) ?></a> <?php } else { ?>
                                        0<?php } ?>
                                    </td>
<?php     } ?>
                                </tr>
                                <tr>
                                    <td>participants</td>
<?php     foreach ($widget['records'] as $record) {
//            $from_time = $record['fromMonth']->format('Y-m-d');
//            $until_time = $record['untilMonth']->format('Y-m-d');
?>
                                    <td><?= !empty($record['participants']) ? '<a href="?page=participants&from_time=$from_time&until_time=$until_time">' . htmlspecialchars($record['participants']) . '</a>' : '0'; ?></td>
<?php     } ?>
                                </tr>
                            </tbody>
                        </table>
<?php } else { ?>
                        <p class="m-3">No matching records found.</p>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= $widget['name']; ?>" -->
