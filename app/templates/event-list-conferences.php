
                <div class="row">
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>

                    <!-- Results filter -->
                    <div class="card w-auto bg-light border-light card-body text-right" style="text-align: right;">
                        <form method="POST" id="filter_form" action="?platform=<?= $platform_id?>&page=<?= $page ?>">
                            <label for="from_time">from</label>
                            <input type="date" id="from_time" name="from_time"<?php if (isset($_REQUEST['from_time'])) echo " value=\"" . $from_time . "\"" ?> />
                            <label for="until_time">until</label>
                            <input type="date" id="until_time" name="until_time"<?php if (isset($_REQUEST['until_time'])) echo " value=\"" . $until_time . "\"" ?> />
                            <input type="text" name="id" placeholder="conference ID"<?php if (isset($_REQUEST['id'])) echo " value=\"" . $_REQUEST['id'] . "\"" ?> />
                            <input type="text" name="name" placeholder="conference name"<?php if (isset($_REQUEST['name'])) echo " value=\"" . $_REQUEST['name'] . "\"" ?> />
                            <input type="button" onclick="clearFilter()" value="clear" />
                            <input type="submit" value="search" />
                        </form>
                        <script>
                            function clearFilter() {
                                document.getElementById("filter_form").reset();
                                const filterFields = document.querySelectorAll("#filter_form input");
                                filterFields.forEach(input => {
                                    if (input.type === 'text' ||input.type === 'date') {
                                        input.value = '';
                                    }
                                });
                            }
                        </script>
                    </div>
                    <!-- /Results filter -->

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
                                    <th scope="col"><?= $header ?></th>
<?php     } ?>
                                </tr>
                            </thead>
                            <tbody>
<?php     foreach ($widget['table_records'] as $row) { ?>
                                <tr>
<?php       $stats_id = false;
            $participant_ip = false;
            if (isset($row['event']) && $row['event'] === 'stats_id') $stats_id = true;
            if (isset($row['event']) && $row['event'] === 'pair selected') $participant_ip = true;

            foreach ($row as $key => $column) {
                    if ($key === 'conference ID' && isset($conferenceId) && $conferenceId === $column) { ?>
                                    <td><strong><?= $column ?? '' ?></strong></td>
<?php               } elseif ($key === 'conference ID') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=conferences&id=<?= htmlspecialchars($column ?? '') ?>"><?= $column ?? '' ?></a></td>
<?php               } elseif ($key === 'conference name' && isset($conferenceName) && $conferenceName === $column) { ?>
                                    <td><strong><?= $column ?? '' ?></strong></td>
<?php               } elseif ($key === 'conference name') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=conferences&name=<?= htmlspecialchars($column ?? '') ?>"><?= $column ?? '' ?></a></td>
<?php               } elseif ($key === 'participant ID') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&id=<?= htmlspecialchars($column ?? '') ?>"><?= $column ?? '' ?></a></td>
<?php               } elseif ($stats_id && $key === 'parameter') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&name=<?= htmlspecialchars($column ?? '') ?>"><?= $column ?? '' ?></a></td>
<?php               } elseif ($participant_ip && $key === 'parameter') { ?>
                                    <td><a href="<?= $app_root ?>?platform=<?= $platform_id?>&page=participants&ip=<?= htmlspecialchars($column ?? '') ?>"><?= $column ?? '' ?></a></td>
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
