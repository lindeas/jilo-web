
                    <!-- Results filter -->
                    <div class="card w-auto bg-light border-light card-body text-right" style="text-align: right;">
                        <form method="POST" id="filter_form" action="?platform=<?= $platform_id?>&page=<?= $page ?>">
                            <label for="from_time">from</label>
                            <input type="date" id="from_time" name="from_time"<?php if (isset($_REQUEST['from_time'])) echo " value=\"" . $_REQUEST['from_time'] . "\"" ?> />
                            <label for="until_time">until</label>
                            <input type="date" id="until_time" name="until_time"<?php if (isset($_REQUEST['until_time'])) echo " value=\"" . $_REQUEST['until_time'] . "\"" ?> />
                            <input type="text" name="id" placeholder="ID"<?php if (isset($_REQUEST['id'])) echo " value=\"" . $_REQUEST['id'] . "\"" ?> />
                            <input type="text" name="name" placeholder="name"<?php if (isset($_REQUEST['name'])) echo " value=\"" . $_REQUEST['name'] . "\"" ?> />
<?php if ($page == 'participants') { ?>
                            <input type="text" name="ip" placeholder="ip address"<?php if (isset($_REQUEST['ip'])) echo " value=\"" . $_REQUEST['ip'] . "\"" ?> maxlength="15" size="15" />
<?php } ?>
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
