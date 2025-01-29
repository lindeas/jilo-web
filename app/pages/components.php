<?php

/**
 * Components information
 *
 * This page ("components") retrieves and displays information about Jitsi components events.
 * Allows filtering by component ID, name, or event name, and listing within a specified time range.
 * Supports pagination.
 */

// connect to database
$response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);

// if DB connection has error, display it and stop here
if ($response['db'] === null) {
    Messages::flash('ERROR', 'DEFAULT', $response['error']);

// otherwise if DB connection is OK, go on
} else {
    $db = $response['db'];

    // Get current page for pagination
    $currentPage = $_REQUEST['page_num'] ?? 1;
    $currentPage = (int)$currentPage;

    // specify time range
    include '../app/helpers/time_range.php';

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
        $params .= '&event=' . urlencode($_REQUEST['event']);
    }

    // pagination variables
    $items_per_page = 15;
    $offset = ($currentPage -1) * $items_per_page;

    // jitsi component events list
    // we use $_REQUEST, so that both links and forms work
    // if it's there, but empty, we make it same as the field name; otherwise assign the value
    $jitsi_component = !empty($_REQUEST['name']) ? "'" . $_REQUEST['name'] . "'" : 'jitsi_component';
    $component_id = !empty($_REQUEST['id']) ? "'" . $_REQUEST['id'] . "'" : 'component_id';
    $event_type = !empty($_REQUEST['event']) ? "'" . $_REQUEST['event'] . "'" : 'event_type';


    //
    // Component events listings
    //

    require '../app/classes/component.php';
    $componentObject = new Component($db);


    // prepare the result
    $search = $componentObject->jitsiComponents($jitsi_component, $component_id, $event_type, $from_time, $until_time, $offset, $items_per_page);
    $search_all = $componentObject->jitsiComponents($jitsi_component, $component_id, $event_type, $from_time, $until_time);

    if (!empty($search)) {
        // we get total items and number of pages
        $item_count = count($search_all);
        $totalPages = ceil($item_count / $items_per_page);

        $components = array();
        $components['records'] = array();

        foreach ($search as $item) {
            extract($item);
            $component_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'loglevel'		=> $loglevel,
                'time'		=> $time,
                'component ID'	=> $component_id,
                'event'		=> $event_type,
                'param'		=> $event_param,
            );
            // populate the result array
            array_push($components['records'], $component_record);
        }
    }

    // filter message
    $filterMessage = array();
    if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
        array_push($filterMessage, 'Jitsi events for component&nbsp;"<strong>' . $_REQUEST['name'] . '</strong>"');
    } elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
        array_push($filterMessage, 'Jitsi events for component ID&nbsp;"<strong>' . $_REQUEST['id'] . '</strong>"');
    }

    // Get any new messages
    include '../app/includes/messages.php';
    include '../app/includes/messages-show.php';

    // display the widget
    include '../app/templates/components.php';

}

?>
