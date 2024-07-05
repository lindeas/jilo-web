
<div class="results-filter">

    <form method="POST" action="?page=<?= $page ?>">

        <label for="from_time">from</label>
        <input type="date" id="from_time" name="from_time"<?php if (isset($_REQUEST['from_time'])) echo " value=\"" . $_REQUEST['from_time'] . "\"" ?> />

        <label for="until_time">until</label>
        <input type="date" id="until_time" name="until_time"<?php if (isset($_REQUEST['until_time'])) echo " value=\"" . $_REQUEST['until_time'] . "\"" ?> />

        <input type="text" name="id" placeholder="ID"<?php if (isset($_REQUEST['id'])) echo " value=\"" . $_REQUEST['id'] . "\"" ?> />

        <input type="text" name="name" placeholder="name"<?php if (isset($_REQUEST['name'])) echo " value=\"" . $_REQUEST['name'] . "\"" ?> />

        <input type="submit" value="search" />

    </form>

</div>
