<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// FIXME move thi sto a special function
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

// conference id/name are specified when searching specific conference(s)
// either id OR name, id has precedence
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $conferenceId = $_REQUEST['id'];
    unset($_REQUEST['name']);
    unset($conferenceName);
} elseif (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    unset($conferenceId);
    $conferenceName = $_REQUEST['name'];
} else {
    unset($conferenceId);
    unset($conferenceName);
}

// connect to database
try {
    $db = new Database($config['jilo_database']);
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}


//
// Conference listings
//


$conference = new Conference($db);

// search and list specific conference ID
if (isset($conferenceId)) {
    $search = $conference->conferenceById($conferenceId, $from_time, $until_time);
// search and list specific conference name
} elseif (isset($conferenceName)) {
    $search = $conference->conferenceByName($conferenceName, $from_time, $until_time);
// list of all conferences (default)
} else {
    $search = $conference->conferencesAllFormatted($from_time, $until_time);
}

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

        // search and list specific conference ID
        if (isset($conferenceId)) {
            $conference_record = array(
                // assign title to the field in the array record
                'time'			=> $time,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'conference host'	=> $conference_host,
                'loglevel'		=> $loglevel,
                'participant ID'	=> $participant_id,
                'event'			=> $event_type,
                'parameter'		=> $event_param
            );
        // search and list specific conference name
        } elseif (isset($conferenceName)) {
            $conference_record = array(
                // assign title to the field in the array record
                'time'			=> $time,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'conference host'	=> $conference_host,
                'loglevel'		=> $loglevel,
                'participant ID'	=> $participant_id,
                'event'			=> $event_type,
                'parameter'		=> $event_param
            );
        // list of all conferences (default)
        } else {
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
        }

        // populate the result array
        array_push($conferences['records'], $conference_record);
    }
}

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'Conferences';
$widget['collapsible'] = false;
$widget['collapsed'] = false;
$widget['filter'] = true;

// widget title
if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    $widget['title'] = 'Conferences with name matching "<strong>' . $_REQUEST['name'] . '"</strong>';
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $widget['title'] = 'Conference with ID "<strong>' . $_REQUEST['id'] . '"</strong>';
} else {
    $widget['title'] = 'All conferences';
}
// widget records
if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($conferences['records'][0]);
    $widget['table_records'] = $conferences['records'];
}

// display the widget
include('templates/widget.php');

?>
