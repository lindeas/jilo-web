<?php

require '../app/classes/participant.php';

// connect to database
$response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);

// if DB connection has error, display it and stop here
if ($response['db'] === null) {
    $error = $response['error'];
    include '../app/templates/block-message.php';

// otherwise if DB connection is OK, go on
} else {
    $db = $response['db'];

    // specify time range
    include '../app/helpers/time_range.php';

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

    $participantObject = new Participant($db);

    // pagination variables
    $items_per_page = 15;
    $browse_page = $_REQUEST['p'] ?? 1;
    $browse_page = (int)$browse_page;
    $offset = ($browse_page -1) * $items_per_page;

    // search and list specific participant ID
    if (isset($participantId)) {
        $search = $participantObject->conferenceByParticipantId($participantId, $from_time, $until_time, $offset, $items_per_page);
        $search_all = $participantObject->conferenceByParticipantId($participantId, $from_time, $until_time);
    // search and list specific participant name (stats_id)
    } elseif (isset($participantName)) {
        $search = $participantObject->conferenceByParticipantName($participantName, $from_time, $until_time, $offset, $items_per_page);
        $search_all = $participantObject->conferenceByParticipantName($participantName, $from_time, $until_time);
    // search and list specific participant IP
    } elseif (isset($participantIp)) {
        $search = $participantObject->conferenceByParticipantIP($participantIp, $from_time, $until_time, $offset, $items_per_page);
        $search_all = $participantObject->conferenceByParticipantIP($participantIp, $from_time, $until_time);
    // list of all participants (default)
    } else {
    // prepare the result
        $search = $participantObject->participantsAll($from_time, $until_time, $offset, $items_per_page);
        $search_all = $participantObject->participantsAll($from_time, $until_time);
    }

    if (!empty($search)) {
        // we get total items and number of pages
        $item_count = count($search_all);
        $page_count = ceil($item_count / $items_per_page);

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
    $widget['pagination'] = true;

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
    include '../app/templates/widget.php';

}

?>
