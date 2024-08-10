<?php

require_once 'classes/database.php';
require 'classes/participant.php';

// connect to database
require 'helpers/database.php';
$db = connectDB($config, 'jilo');

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

// participant id/name/IP are specified when searching specific participant(s)
// participant name - this is 'stats_id' in the db
// either id, name, OR IP - in that order
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $participantId = $_REQUEST['id'];
    unset($_REQUEST['name']);
    unset($participantName);
} elseif (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    unset($participantId);
    $participantName = $_REQUEST['name'];
} elseif (isset($_REQUEST['ip']) && $_REQUEST['ip'] != '') {
    unset($participantId);
    $participantIp = $_REQUEST['ip'];
} else {
    unset($participantId);
    unset($participantName);
}


//
// Participant listings
//

$participant = new Participant($db);

// search and list specific participant ID
if (isset($participantId)) {
    $search = $participant->conferenceByParticipantId($participantId, $from_time, $until_time, $participantId, $from_time, $until_time);
// search and list specific participant name (stats_id)
} elseif (isset($participantName)) {
    $search = $participant->conferenceByParticipantName($participantName, $from_time, $until_time);
// search and list specific participant IP
} elseif (isset($participantIp)) {
    $search = $participant->conferenceByParticipantIP($participantIp, $from_time, $until_time);
// list of all participants (default)
} else {
// prepare the result
    $search = $participant->participantsAll($from_time, $until_time);
}

if (!empty($search)) {
    $participants = array();
    $participants['records'] = array();

    foreach ($search as $item) {
        extract($item);

        // search and list specific participant ID
        if (isset($participantId)) {
            $participant_record = array(
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
        // search and list specific participant name (stats_id)
        } elseif (isset($participantName)) {
            $participant_record = array(
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
        // search and list specific participant IP
        } elseif (isset($participantIp)) {
            $participant_record = array(
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
        // list of all participants (default)
        } else {
            $participant_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'participant ID'	=> $endpoint_id,
                'conference ID'		=> $conference_id
            );
        }

        // populate the result array
        array_push($participants['records'], $participant_record);
    }
}

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'Participants';
$widget['collapsible'] = false;
$widget['collapsed'] = false;
$widget['filter'] = true;

// widget title
if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    $widget['title'] = 'Conferences with participant name (stats_id) matching "<strong>' . $_REQUEST['name'] . '"</strong>';
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $widget['title'] = 'Conference with participant ID matching "<strong>' . $_REQUEST['id'] . '"</strong>';
} elseif (isset($participantIp)) {
    $widget['title'] = 'Conference with participant IP matching "<strong>' . $participantIp . '"</strong>';
} else {
    $widget['title'] = 'All participants';
}
// widget records
if (!empty($participants['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($participants['records'][0]);
    $widget['table_records'] = $participants['records'];
}

// display the widget
include('templates/widget.php');

?>
