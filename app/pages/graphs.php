<?php

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';

require '../app/classes/agent.php';
require '../app/classes/conference.php';
require '../app/classes/host.php';

$agentObject = new Agent($db);
$hostObject = new Host($db);

// Connect to Jilo database for log data
$response = connectJiloDB($config, $platformDetails[0]['jilo_database'], $platform_id);
if ($response['db'] === null) {
    Feedback::flash('ERROR', 'DEFAULT', $response['error']);
} else {
    $db = $response['db'];
}
$conferenceObject = new Conference($db);

// Get date range for the last 7 days
$from_time = date('Y-m-d', strtotime('-7 days'));
$until_time = date('Y-m-d');

// Define graphs to show
$graphs = [
    [
        'graph_name' => 'conferences',
        'graph_title' => 'Conferences in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time',
        'datasets' => []
    ],
    [
        'graph_name' => 'participants',
        'graph_title' => 'Participants in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time',
        'datasets' => []
    ]
];

// Get Jitsi API data
$conferences_api = $agentObject->getHistoricalData(
    $platform_id, 
    'jicofo',
    'conferences',
    $from_time,
    $until_time
);
$graphs[0]['datasets'][] = [
    'data' => $conferences_api,
    'label' => 'Conferences from Jitsi API',
    'color' => 'rgba(75, 192, 192, 1)'
];

// Get conference data from logs
$conferences_logs = $conferenceObject->conferenceNumber(
    $from_time,
    $until_time
);
$graphs[0]['datasets'][] = [
    'data' => $conferences_logs,
    'label' => 'Conferences from Logs',
    'color' => 'rgba(255, 99, 132, 1)'
];

// Get participants data
$participants_api = $agentObject->getHistoricalData(
    $platform_id, 
    'jicofo',
    'participants',
    $from_time,
    $until_time
);
$graphs[1]['datasets'][] = [
    'data' => $participants_api,
    'label' => 'Participants from Jitsi API',
    'color' => 'rgba(75, 192, 192, 1)'
];

// Prepare data for template
$graph = $graphs;

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'Graphs';
$widget['title'] = 'Jitsi graphs';

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/graphs.php';
