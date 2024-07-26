
<a style="text-decoration: none;" data-toggle="collapse" href="#collapseLastDays" role="button" aria-expanded="true" aria-controls="collapseLastDays">
<div class="card bg-light card-body"><?= $widget['title'] ?></div></a>

<div class="collapse show" id="collapseLastDays">
    <?= $widget['time_period'] ?>
    <div class="mb-5">
        <table class="table table-striped table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
<?php foreach ($widget['table_headers'] as $header) { ?>
                    <th scope="col"><?= htmlspecialchars($header) ?></th>
<?php } ?>
                </tr>
            </thead>
            <tbody>
<?php foreach ($widget['table_records'] as $row) { ?>
                <tr>
<?php foreach ($row as $key => $column) {
            if ($key === 'conference ID' && $column === $conference_id) { ?>
                    <td><strong><?= htmlspecialchars($column ?? '') ?></strong></td>
<?php       } elseif ($key === 'conference name') { ?>
                    <td><a href="<?= $app_root ?>?page=conferences&name="<?= htmlspecialchars($column ?? '') ?>"">"<?= htmlspecialchars($column ?? '') ?>"</a></td>
<?php       } ?>
                </tr>
<?php } ?>
            </tbody>
        </table>
    </div>
</div>
