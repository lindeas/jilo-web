<?php

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';

require '../app/classes/settings.php';
require '../app/classes/agent.php';
require '../app/classes/conference.php';
require '../app/classes/host.php';

$settingsObject = new Settings();
$agentObject = new Agent($dbWeb);
$hostObject = new Host($dbWeb);

// connect to Jilo database
//$response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);
//
//// if DB connection has error, display it and stop here
//if ($response['db'] === null) {
//    Messages::flash('ERROR', 'DEFAULT', $response['error']);

// otherwise if DB connection is OK, go on
//} else {
//    $db = $response['db'];
//
//    $conferenceObject = new Conference($db);

    switch ($item) {

        case 'graphs':
            // Connect to Jilo database for log data
            $jilo_response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);
            if ($jilo_response['db'] === null) {
                Messages::flash('ERROR', 'DEFAULT', $jilo_response['error']);
                break;
            }
            $jilo_db = $jilo_response['db'];

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

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

// Load the template
            include '../app/templates/graphs-combined.php';
            break;

        case 'configjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformConfigjs = $settingsObject->getPlatformConfigjs($platformDetails[0]['jitsi_url'], $raw);
// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

// Load the template
            include '../app/templates/data-configjs.php';
            break;

        case 'interfaceconfigjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformInterfaceConfigjs = $settingsObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

// Load the template
            include '../app/templates/data-interfaceconfigjs.php';
            break;

        default:
    }

//}

?>
