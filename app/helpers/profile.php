<?php

// get the UTC offset of a specified timezone
function getUTCOffset($timezone) {
    $formattedOffset = '';
    if (isset($timezone)) {

        $datetime = new DateTime("now", new DateTimeZone($timezone));
        $offsetInSeconds = $datetime->getOffset();

        $hours = intdiv($offsetInSeconds, 3600);
        $minutes = ($offsetInSeconds % 3600) / 60;
        $formattedOffset = sprintf("UTC%+03d:%02d", $hours, $minutes); // Format UTC+01:00
    }

    return $formattedOffset;

}

?>
