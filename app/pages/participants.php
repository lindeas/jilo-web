<?php

/**
 * Participants information
 *
 * This page ("participants") retrieves and displays participant information for conferences.
 * Allows filtering by participant ID, name, or IP address, and listing within a specified time range.
 * Supports pagination.
 */

// connect to database
$response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);

// if DB connection has error, display it and stop here
if ($response['db'] === null) {
    Feedback::flash('ERROR', 'DEFAULT', $response['error']);

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

    require '../app/classes/participant.php';
    $participantObject = new Participant($db);

    // get current page for pagination
    $currentPage = $_REQUEST['page_num'] ?? 1;
    $currentPage = (int)$currentPage;

    // pagination variables
    $items_per_page = 20;
    $offset = ($currentPage -1) * $items_per_page;

    // Build params for pagination
    $params = '';
    if (!empty($_REQUEST['from_time'])) {
        $params .= '&from_time=' . urlencode($_REQUEST['from_time']);
    }
    if (!empty($_REQUEST['until_time'])) {
        $params .= '&until_time=' . urlencode($_REQUEST['until_time']);
    }
    if (!empty($_REQUEST['name'])) {
        $params .= '&name=' . urlencode($_REQUEST['name']);
    }
    if (!empty($_REQUEST['id'])) {
        $params .= '&id=' . urlencode($_REQUEST['id']);
    }
    if (isset($_REQUEST['event'])) {
        $params .= '&ip=' . urlencode($_REQUEST['ip']);
    }

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
        $totalPages = ceil($item_count / $items_per_page);

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

    // filter message
    $filterMessage = array();
    if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
        array_push($filterMessage, 'Conferences with participant name (stats_id) matching "<strong>' . $_REQUEST['name'] . '</strong>"');
    } elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
        array_push($filterMessage, 'Conferences with participant ID matching "<strong>' . $_REQUEST['id'] . '</strong>"');
    } elseif (isset($participantIp)) {
        array_push($filterMessage, 'Conferences with participant IP matching "<strong>' . $participantIp . '</strong>"');
    }

    // Get any new messages
    include '../app/includes/messages.php';
    include '../app/includes/messages-show.php';

    // display the widget
    include '../app/templates/participants.php';

}

?>
