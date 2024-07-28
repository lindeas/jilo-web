<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// connect to database
try {
    $db = new Database($config['jilo_database']);
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

//
// dashboard widget listings
//

// conferences in last 7 days
try {
    $conference = new Conference($db);

    // conferences for last 2 days
    $from_time = date('Y-m-d', time() - 60 * 60 * 24 * 2);
    $until_time = date('Y-m-d', time());
    $time_range_specified = true;

    // prepare the result
    $search = $conference->conferencesAllFormatted($from_time, $until_time);

    if (!empty($search)) {
        $conferences = array();
        $conferences['records'] = array();

        foreach ($search as $item) {
            extract($item);

            // we don't have duration field, so we calculate it
            if (!empty($start) && !empty($end)) {
                $duration = gmdate("H:i:s", abs(strtotime($end) - strtotime($start)));
            } else {
                $duration = '';
            }
            $conference_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'duration'		=> $duration,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'participants'		=> $participants,
                'name count'		=> $name_count,
                'conference host'	=> $conference_host
            );
            // populate the result array
            array_push($conferences['records'], $conference_record);
        }
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LastDays';
$widget['title'] = 'Conferences for the last 2 days';
$widget['collapsible'] = true;
$widget['collapsed'] = false;
$widget['filter'] = false;
if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($conferences['records'][0]);
    $widget['table_records'] = $conferences['records'];
}

// display the widget
include('templates/widget.php');

echo "<br />";

// last 10 conferences
try {
    $conference = new Conference($db);

    // all time
    $from_time = '0000-01-01';
    $until_time = '9999-12-31';
    $time_range_specified = false;
    // number of conferences to show
    $conference_number = 10;

    // prepare the result
    $search = $conference->conferencesAllFormatted($from_time, $until_time);

    if (!empty($search)) {
        $conferences = array();
        $conferences['records'] = array();

        $i = 0;
        foreach ($search as $item) {
            extract($item);

            // we don't have duration field, so we calculate it
            if (!empty($start) && !empty($end)) {
                $duration = gmdate("H:i:s", abs(strtotime($end) - strtotime($start)));
            } else {
                $duration = '';
            }
            $conference_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'duration'		=> $duration,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'participants'		=> $participants,
                'name count'		=> $name_count,
                'conference host'	=> $conference_host
            );
            // populate the result array
            array_push($conferences['records'], $conference_record);

            // we only take the first 10 results
            $i++;
            if ($i == 10) break;
        }
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LastConferences';
$widget['title'] = 'The last ' . $conference_number . ' conferences';
$widget['collapsible'] = true;
$widget['collapsed'] = false;
$widget['filter'] = false;

if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($conferences['records'][0]);
    $widget['table_records'] = $conferences['records'];
}

// display the widget
include('templates/widget.php');

?>
