<?php

$time_range_specified = false;
if (!isset($_REQUEST['from_time']) || (isset($_REQUEST['from_time']) && $_REQUEST['from_time'] == '')) {
    $from_time = '0000-01-01';
} else {
    $from_time = $_REQUEST['from_time'];
    $time_range_specified = true;
}
if (!isset($_REQUEST['until_time']) || (isset($_REQUEST['until_time']) && $_REQUEST['until_time'] == '')) {
    $until_time = '9999-12-31';
} else {
    $until_time = $_REQUEST['until_time'];
    $time_range_specified = true;
}

?>
