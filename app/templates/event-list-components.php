
                <div class="row">
                    <div class="card w-auto bg-light border-light card-body"  style="flex-direction: row;"><?= $widget['title'] ?></div>

                    <!-- Results filter -->
                    <div class="card w-auto bg-light border-light card-body text-right" style="text-align: right;">
                        <form method="POST" id="filter_form" action="?platform=<?= htmlspecialchars($platform_id) ?>&page=<?= htmlspecialchars($page) ?>">
                            <label for="from_time">from</label>
                            <input type="date" id="from_time" name="from_time"<?php if (isset($_REQUEST['from_time'])) echo " value=\"" . htmlspecialchars($from_time) . "\"" ?> />
                            <label for="until_time">until</label>
                            <input type="date" id="until_time" name="until_time"<?php if (isset($_REQUEST['until_time'])) echo " value=\"" . htmlspecialchars($until_time) . "\"" ?> />
                            <input type="text" name="id" placeholder="component ID"<?php if (isset($_REQUEST['id'])) echo " value=\"" . htmlspecialchars($_REQUEST['id']) . "\"" ?> />
                            <input type="text" name="name" placeholder="component name"<?php if (isset($_REQUEST['name'])) echo " value=\"" . htmlspecialchars($_REQUEST['name']) . "\"" ?> />
                            <input type="text" name="event" placeholder="event name"<?php if (isset($_REQUEST['event'])) echo " value=\"" . htmlspecialchars($_REQUEST['event']) . "\"" ?> />
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

                <!-- widget "<?= htmlspecialchars($widget['name']) ?>" -->
                <div class="collapse show" id="collapse<?= htmlspecialchars($widget['name']) ?>">
<?php if ($time_range_specified) { ?>
                    <p class="m-3">time period: <strong><?= htmlspecialchars($from_time) ?> - <?= htmlspecialchars($until_time) ?></strong></p>
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
<?php        foreach ($row as $key => $column) { ?>
<?php               if ($key === 'component ID') { ?>
                                    <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&id=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } elseif ($key === 'component') { ?>
                                    <td><a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=components&name=<?= htmlspecialchars($column ?? '') ?>"><?= htmlspecialchars($column ?? '') ?></a></td>
<?php               } else { ?>
                                    <td><?= htmlspecialchars($column ?? '') ?></td>
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
                <!-- /widget "<?= htmlspecialchars($widget['name']) ?>" -->
