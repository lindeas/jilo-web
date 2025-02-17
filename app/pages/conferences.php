<?php

/**
 * Conference information
 *
 * This page ("conferences") retrieves and displays information about conferences.
 * Allows filtering by conference ID or name, and listing within a specified time range.
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

    // conference id/name are specified when searching specific conference(s)
    // we use $_REQUEST, so that both links and forms work
    // if it's there, but empty, we make it same as the field name; otherwise assign the value

    //$conferenceName = !empty($_REQUEST['name']) ? "'" . $_REQUEST['name'] . "'" : 'conference_name';
    //$conferenceId = !empty($_REQUEST['id']) ? "'" . $_REQUEST['id'] . "'" : 'conference_id';

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


    //
    // Conference listings
    //

    require '../app/classes/conference.php';
    $conferenceObject = new Conference($db);

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

    // search and list specific conference ID
    if (isset($conferenceId)) {
        $search = $conferenceObject->conferenceById($conferenceId, $from_time, $until_time, $offset, $items_per_page);
        $search_all = $conferenceObject->conferenceById($conferenceId, $from_time, $until_time);
    // search and list specific conference name
    } elseif (isset($conferenceName)) {
        $search = $conferenceObject->conferenceByName($conferenceName, $from_time, $until_time, $offset, $items_per_page);
        $search_all = $conferenceObject->conferenceByName($conferenceName, $from_time, $until_time);
    // list of all conferences (default)
    } else {
        $search = $conferenceObject->conferencesAllFormatted($from_time, $until_time, $offset, $items_per_page);
        $search_all = $conferenceObject->conferencesAllFormatted($from_time, $until_time);
    }

    if (!empty($search)) {
        // we get total items and number of pages
        $item_count = count($search_all);
        $totalPages = ceil($item_count / $items_per_page);

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

    // filter message
    $filterMessage = array();
    if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
        array_push($filterMessage, 'Conferences with name matching "<strong>' . $_REQUEST['name'] . '</strong>"');
    } elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
        array_push($filterMessage, 'Conferences with ID "<strong>' . $_REQUEST['id'] . '</strong>"');
    }

    // Get any new feedback messages
    include '../app/includes/feedback-get.php';
    include '../app/includes/feedback-show.php';

    // display the widget
    include '../app/templates/conferences.php';

}

?>
