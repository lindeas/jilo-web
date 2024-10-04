
                <div class="row">
<?php if ($widget['collapsible'] === true) { ?>
                    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= $widget['name'] ?>" role="button" aria-expanded="true" aria-controls="collapse<?= $widget['name'] ?>">
                        <div class="card w-auto bg-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } else { ?>
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>
<?php } ?>
<?php if ($widget['filter'] === true) {
    include '../app/templates/logs-filter.php'; } ?>
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
                                    <th scope="col" class="th-<?= $header ?>"><?= $header ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                                <tr>
<?php
            foreach ($row as $key => $column) {
                    if ($key === 'user ID' && isset($user_id) && $user_id === $column) { ?>
                                    <td><strong><?= $column ?? '' ?></strong></td>
<?php               } else { ?>
                                    <td><?= $column ?? '' ?></td>
<?php               }
                } ?>
                                </tr>
<?php     } ?>
                            </tbody>
                        </table>
<?php
if ($widget['pagination'] && $item_count > $items_per_page) {
    $url = "$app_root?platform=$platform_id&page=$page";
    include '../app/helpers/pagination.php';
}
?>
<?php } else { ?>
                    <p class="m-3">No matching records found.</p>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "<?= $widget['name']; ?>" -->
