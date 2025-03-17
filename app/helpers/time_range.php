<?php

$time_range_specified = false;
if (!isset($from_time) || (isset($from_time) && $from_time == '')) {
    $from_time = '0000-01-01';
} else {
    $time_range_specified = true;
}
if (!isset($until_time) || (isset($until_time) && $until_time == '')) {
    $until_time = '9999-12-31';
} else {
    $time_range_specified = true;
}
