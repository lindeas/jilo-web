
<div class="row">

<?php if ($widget['collapsible'] === true) { ?>
    <a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= $widget['name'] ?>" role="button" aria-expanded="true" aria-controls="collapse<?= $widget['name'] ?>">
<?php } ?>
    <div class="card w-25 bg-light card-body"><?= $widget['title'] ?></div>
<?php if ($widget['filter'] === true) {
    include('templates/results-filter.php');
} ?>
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
<?php     foreach ($widget['table_headers'] as $header) { ?>
                    <th scope="col"><?= htmlspecialchars($header) ?></th>
<?php     } ?>
                </tr>
            </thead>
            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                <tr>
<?php           foreach ($row as $key => $column) {
                    if ($key === 'conference ID') { ?>
                    <td><a href="<?= $app_root ?>?page=conferences&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'conference name') { ?>
                    <td><a href="<?= $app_root ?>?page=conferences&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component ID') { ?>
                    <td><a href="<?= $app_root ?>?page=components&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component') { ?>
                    <td><a href="<?= $app_root ?>?page=components&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
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
