<?php

/**
 * Logs listings
 *
 * This page ("logs") retrieves and displays logs within a time range
 * either for a specified user or for all users.
 * It supports pagination and filtering.
 */

// Define plugin base path if not already defined
if (!defined('PLUGIN_LOGS_PATH')) {
    define('PLUGIN_LOGS_PATH', dirname(__FILE__, 2) . '/');
}
require_once PLUGIN_LOGS_PATH . 'models/Log.php';
require_once PLUGIN_LOGS_PATH . 'models/LoggerFactory.php';
require_once dirname(__FILE__, 4) . '/app/classes/user.php';

// Check for rights; user or system
$has_system_access = ($userObject->hasRight($userId, 'superuser') ||
                     $userObject->hasRight($userId, 'view app logs'));

// Get current page for pagination
$currentPage = $_REQUEST['page_num'] ?? 1;
$currentPage = (int)$currentPage;

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
$offset = ($currentPage - 1) * $items_per_page;

// Build params for pagination
$params = '';
if (!empty($_REQUEST['from_time'])) {
    $params .= '&from_time=' . urlencode($_REQUEST['from_time']);
}
if (!empty($_REQUEST['until_time'])) {
    $params .= '&until_time=' . urlencode($_REQUEST['until_time']);
}
if (!empty($_REQUEST['message'])) {
    $params .= '&message=' . urlencode($_REQUEST['message']);
}
if (!empty($_REQUEST['id'])) {
    $params .= '&id=' . urlencode($_REQUEST['id']);
}
if (isset($_REQUEST['tab'])) {
    $params .= '&tab=' . urlencode($_REQUEST['tab']);
}

// prepare the result
$search = $logObject->readLog($userId, $scope, $offset, $items_per_page, $filters);
$search_all = $logObject->readLog($userId, $scope, 0, 0, $filters);

if (!empty($search)) {
    // we get total items and number of pages
    $item_count = count($search_all);
    $totalPages = ceil($item_count / $items_per_page);

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

$username = $userObject->getUserDetails($userId)[0]['username'];

// Get any new feedback messages
include dirname(__FILE__, 4) . '/app/helpers/feedback.php';

// Display messages list
include PLUGIN_LOGS_PATH . 'views/logs.php';
