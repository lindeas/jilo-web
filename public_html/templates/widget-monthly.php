
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
<?php     foreach ($widget['table_headers'] as $header) { ?>
                    <th scope="col"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>conferences</td>
<?php     foreach ($widget['table_records_conferences'] as $row) { ?>
                    <td><?= htmlspecialchars($row ?? '') ?></td>
<?php     } ?>
                </tr>
                <tr>
                    <td>participants</td>
<?php     foreach ($widget['table_records_participants'] as $row) { ?>
                    <td><?= htmlspecialchars($row ?? '') ?></td>
<?php     } ?>
                </tr>
            </tbody>
        </table>
<?php } else { ?>
        <p class="m-3">No matching records found.</p>
<?php } ?>
    </div>
</div>
