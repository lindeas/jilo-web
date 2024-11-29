<?php

/**
 * Logs listings
 *
 * This page ("logs") retrieves and displays logs for a specified user within a time range.
 * It supports pagination and filtering, and generates a widget to display the logs.
 */

// specify time range
include '../app/helpers/time_range.php';

// pagination variables
$items_per_page = 15;
$browse_page = $_REQUEST['p'] ?? 1;
$browse_page = (int)$browse_page;
$offset = ($browse_page -1) * $items_per_page;

// logs scope: user or system
$scope = 'user';

// prepare the result
$search = $logObject->readLog($user_id, $scope, $offset, $items_per_page);
$search_all = $logObject->readLog($user_id, $scope);

if (!empty($search)) {
    // we get total items and number of pages
    $item_count = count($search_all);
    $page_count = ceil($item_count / $items_per_page);

    $logs = array();
    $logs['records'] = array();

    foreach ($search as $item) {

        // when we show only user's logs, omit user_id column
        if ($scope === 'user') {
            $log_record = array(
                // assign title to the field in the array record
                'time'		=> $item['time'],
                'log message'	=> $item['message']
            );
        } else {
            $log_record = array(
                // assign title to the field in the array record
                'userID'	=> $item['user_id'],
                'time'		=> $item['time'],
                'log message'	=> $item['message']
            );
        }

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
if (!empty($logs['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($logs['records'][0]);
    $widget['table_records'] = $logs['records'];
}
$widget['pagination'] = true;

// display the widget
include '../app/templates/logs-list.php';

?>
