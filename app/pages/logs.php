<?php

//
// logs listings
//

// specify time range
include '../app/helpers/time_range.php';

// prepare the result
$search = $logObject->readLog($user_id, 'user');

if (!empty($search)) {
    $logs = array();
    $logs['records'] = array();

    foreach ($search as $item) {
        extract($item);

        $log_record = array(
            // assign title to the field in the array record
            'user ID'		=> $user_id,
            'time'		=> $time,
            'log message'	=> $message
        );
        // populate the result array
        array_push($logs['records'], $log_record);
    }
}

// prepare the widget
$widget['full'] = false;
$widget['collapsible'] = false;
$widget['name'] = 'Logs';
$username = $userObject->getUserDetails($user_id)[0]['username'];
$widget['title'] = "Log events for user \"$username\"";
$widget['filter'] = true;
if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($logs['records'][0]);
    $widget['table_records'] = $logs['records'];
}
$widget['pagination'] = true;

// display the widget
include '../app/templates/logs-list.php';

?>
