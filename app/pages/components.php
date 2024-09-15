<?php

require '../app/classes/component.php';

// connect to database
$db = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);

// specify time range
include '../app/helpers/time_range.php';

// jitsi component events list
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    $jitsi_component = "'" . $_REQUEST['name'] . "'";
    $component_id = 'component_id';
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $component_id = "'" . $_REQUEST['id'] . "'";
    $jitsi_component = 'jitsi_component';
} else {
    // we need the variables to use them later in sql for columnname = columnname
    $jitsi_component = 'jitsi_component';
    $component_id = 'component_id';
}


//
// Component events listings
//


// list of all component events (default)
$componentObject = new Component($db);

// pagination variables
$items_per_page = 15;
$browse_page = $_REQUEST['p'] ?? 1;
$browse_page = (int)$browse_page;
$offset = ($browse_page -1) * $items_per_page;

// prepare the result
$search = $componentObject->jitsiComponents($jitsi_component, $component_id, $from_time, $until_time, $offset, $items_per_page);
$search_all = $componentObject->jitsiComponents($jitsi_component, $component_id, $from_time, $until_time);

if (!empty($search)) {
    // we get total items and number of pages
    $item_count = count($search_all);
    $page_count = ceil($item_count / $items_per_page);

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

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'AllComponents';
$widget['collapsible'] = false;
$widget['collapsed'] = false;
$widget['filter'] = true;
$widget['pagination'] = true;

// widget title
if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    $widget['title'] = 'Jitsi events for component&nbsp;<strong>' . $_REQUEST['name'] . '</strong>';
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $widget['title'] = 'Jitsi events for component ID&nbsp;<strong>' . $_REQUEST['id'] . '</strong>';
} else {
    $widget['title'] = 'Jitsi events for&nbsp;<strong>all components</strong>';
}
// widget records
if (!empty($components['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($components['records'][0]);
    $widget['table_records'] = $components['records'];
}

// display the widget
include '../app/templates/widget.php';

?>
