<?php

/**
 * Logs listings
 *
 * This page ("logs") retrieves and displays logs for a specified user within a time range.
 * It supports pagination and filtering, and generates a widget to display the logs.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

// Check for rights; user or system
$has_system_access = ($userObject->hasRight($user_id, 'superuser') ||
                     $userObject->hasRight($user_id, 'view app logs'));

// Get selected tab
$selected_tab = $_REQUEST['tab'] ?? 'user';
if ($selected_tab === 'system' && !$has_system_access) {
    $selected_tab = 'user';
}

// Set scope based on selected tab
$scope = ($selected_tab === 'system') ? 'system' : 'user';

// specify time range
include '../app/helpers/time_range.php';

// Prepare search filters
$filters = [];
if (isset($_REQUEST['from_time']) && !empty($_REQUEST['from_time'])) {
    $filters['from_time'] = $_REQUEST['from_time'];
}
if (isset($_REQUEST['until_time']) && !empty($_REQUEST['until_time'])) {
    $filters['until_time'] = $_REQUEST['until_time'];
}
if (isset($_REQUEST['message']) && !empty($_REQUEST['message'])) {
    $filters['message'] = $_REQUEST['message'];
}
if ($scope === 'system' && isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
    $filters['id'] = $_REQUEST['id'];
}

// pagination variables
$items_per_page = 15;
$browse_page = $_REQUEST['p'] ?? 1;
$browse_page = (int)$browse_page;
$offset = ($browse_page -1) * $items_per_page;

// prepare the result
$search = $logObject->readLog($user_id, $scope, $offset, $items_per_page, $filters);
$search_all = $logObject->readLog($user_id, $scope, 0, '', $filters);

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
                'time'          => $item['time'],
                'log message'   => $item['message']
            );
        } else {
            $log_record = array(
                // assign title to the field in the array record
                'userID'        => $item['user_id'],
                'username'      => $item['username'],
                'time'          => $item['time'],
                'log message'   => $item['message']
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
$widget['title'] = "Log events";
$widget['filter'] = true;
$widget['scope'] = $scope;
$widget['has_system_access'] = $has_system_access;
if (!empty($logs['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($logs['records'][0]);
    $widget['table_records'] = $logs['records'];
}
$widget['pagination'] = true;

// display the widget
include '../app/templates/logs-list.php';

?>
