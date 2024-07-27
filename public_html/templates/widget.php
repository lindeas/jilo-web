
<a style="text-decoration: none;" data-toggle="collapse" href="#collapse<?= $widget['name'] ?>" role="button" aria-expanded="true" aria-controls="collapse<?= $widget['name'] ?>">
<div class="card bg-light card-body"><?= $widget['title'] ?></div></a>

<div class="collapse show" id="collapse<?= $widget['name'] ?>">
    <?= $widget['time_period'] ?>
    <div class="mb-5">
<?php if ($widget['full'] == true) { ?>
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
