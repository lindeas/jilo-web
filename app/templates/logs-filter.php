
                    <!-- Logs filter -->
                    <div class="card w-auto bg-light border-light card-body text-right" style="text-align: right;">
                        <form method="POST" id="filter_form" action="?page=logs">
                            <label for="from_time">from</label>
                            <input type="date" id="from_time" name="from_time"<?php if (isset($_REQUEST['from_time'])) echo " value=\"" . $from_time . "\"" ?> />
                            <label for="until_time">until</label>
                            <input type="date" id="until_time" name="until_time"<?php if (isset($_REQUEST['until_time'])) echo " value=\"" . $until_time . "\"" ?> />
                            <input type="text" name="id" placeholder="user ID"<?php if (isset($_REQUEST['id'])) echo " value=\"" . $_REQUEST['id'] . "\"" ?> />
                            <input type="text" name="message" placeholder="message"<?php if (isset($_REQUEST['message'])) echo " value=\"" . $_REQUEST['message'] . "\"" ?> />
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
                    <!-- /Logs filter -->
