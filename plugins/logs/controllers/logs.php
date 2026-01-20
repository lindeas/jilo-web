<?php

/**
 * Logs Plugin Controller
 *
 * Procedural handler used by the callable dispatcher of the logs plugin.
 */

require_once PLUGIN_LOGS_PATH . 'models/Log.php';
require_once PLUGIN_LOGS_PATH . 'models/LoggerFactory.php';
require_once APP_PATH . 'classes/user.php';
require_once APP_PATH . 'helpers/theme.php';

function logs_plugin_handle(string $action, array $context = []): bool {
    $validSession = (bool)($context['valid_session'] ?? false);
    $app_root = $context['app_root'] ?? (\App\App::get('app_root') ?? '/');
    $db = $context['db'] ?? \App\App::db();
    $userId = $context['user_id'] ?? null;

    if (!$db || !$userId) {
        \Feedback::flash('ERROR', 'DEFAULT', 'Logs service unavailable.');
        header('Location: ' . $app_root);
        exit;
    }

    // Get logger instance from globals (set by logger.system_init hook)
    $logObject = $GLOBALS['logObject'] ?? null;
    if (!$logObject) {
        \Feedback::flash('ERROR', 'DEFAULT', 'Logger not initialized.');
        header('Location: ' . $app_root);
        exit;
    }

    switch ($action) {
        case 'list':
        default:
            logs_plugin_render_list($logObject, $db, $userId, $validSession, $app_root);
            return true;
    }
}

function logs_plugin_render_list($logObject, $db, int $userId, bool $validSession, string $app_root): void {
    // Load User class for permissions check
    $userObject = new \User($db);
    
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

    // Specify time range
    include APP_PATH . 'helpers/time_range.php';

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

    // Pagination variables
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

    // Prepare the result
    $search = $logObject->readLog($userId, $scope, $offset, $items_per_page, $filters);
    $search_all = $logObject->readLog($userId, $scope, 0, 0, $filters);

    $logs = [];
    $totalPages = 0;
    $item_count = 0;

    if (!empty($search)) {
        // Get total items and number of pages
        $item_count = count($search_all);
        $totalPages = ceil($item_count / $items_per_page);

        $logs = [];
        $logs['records'] = [];

        foreach ($search as $item) {
            // When we show only user's logs, omit user_id column
            if ($scope === 'user') {
                // assign title to the field
                $log_record = [
                    'time'          => $item['time'],
                    'log level'     => $item['level'],
                    'log message'   => $item['message']
                ];
            } else {
                // assign title to the field
                $log_record = [
                    'userID'        => $item['user_id'],
                    'username'      => $item['username'],
                    'time'          => $item['time'],
                    'log level'     => $item['level'],
                    'log message'   => $item['message']
                ];
            }

            $logs['records'][] = $log_record;
        }
    }

    $username = $userObject->getUserDetails($userId)[0]['username'];
    $page = 'logs'; // For pagination template

    \App\Helpers\Theme::include('page-header');
    \App\Helpers\Theme::include('page-menu');
    if ($validSession) {
        \App\Helpers\Theme::include('page-sidebar');
    }

    include APP_PATH . 'helpers/feedback.php';
    require_once PLUGIN_LOGS_PATH . 'helpers/logs_view_helper.php';
    include PLUGIN_LOGS_PATH . 'views/logs.php';

    \App\Helpers\Theme::include('page-footer');
}
