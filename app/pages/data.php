<?php

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

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
$response = connectDB($config, 'jilo', $platformDetails[0]['jilo_database'], $platform_id);

// if DB connection has error, display it and stop here
if ($response['db'] === null) {
    Messages::flash('ERROR', 'DEFAULT', $response['error']);

// otherwise if DB connection is OK, go on
} else {
    $db = $response['db'];

    $conferenceObject = new Conference($db);

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

            include '../app/templates/graphs-combined.php';
            break;

        case 'latest':
            // Define metrics to display
            $metrics = [
                'Basic stats' => [
                    'conferences' => ['label' => 'Current conferences', 'link' => 'conferences'],
                    'participants' => ['label' => 'Current participants', 'link' => 'participants'],
                    'total_conferences_created' => ['label' => 'Total conferences created'],
                    'total_participants' => ['label' => 'Total participants']
                ],
                'Bridge stats' => [
                    'bridge_selector.bridge_count' => ['label' => 'Bridge count'],
                    'bridge_selector.operational_bridge_count' => ['label' => 'Operational bridges'],
                    'bridge_selector.in_shutdown_bridge_count' => ['label' => 'Bridges in shutdown']
                ],
                'Jibri stats' => [
                    'jibri_detector.count' => ['label' => 'Jibri count'],
                    'jibri_detector.available' => ['label' => 'Jibri idle'],
                    'jibri.live_streaming_active' => ['label' => 'Jibri active streaming'],
                    'jibri.recording_active' => ['label' => 'Jibri active recording'],
                ],
                'System stats' => [
                    'threads' => ['label' => 'Threads'],
                    'stress_level' => ['label' => 'Stress level'],
                    'version' => ['label' => 'Version']
                ]
            ];

            // Get all hosts for this platform
            $hosts = $hostObject->getHostDetails($platform_id);
            $hostsData = [];

            // For each host, get its agents and their metrics
            foreach ($hosts as $host) {
                $hostData = [
                    'id' => $host['id'],
                    'name' => $host['name'] ?: $host['address'],
                    'address' => $host['address'],
                    'agents' => []
                ];

                // Get agents for this host
                $hostAgents = $agentObject->getAgentDetails($host['id']);
                foreach ($hostAgents as $agent) {
                    $agentData = [
                        'id' => $agent['id'],
                        'type' => $agent['agent_description'],
                        'name' => strtoupper($agent['agent_description']),
                        'metrics' => [],
                        'timestamp' => null
                    ];

                    // Fetch all metrics for this agent
                    foreach ($metrics as $section => $section_metrics) {
                        foreach ($section_metrics as $metric => $metricConfig) {
                            // Get latest data
                            $latestData = $agentObject->getLatestData($host['id'], $agent['agent_description'], $metric);
                            
                            if ($latestData !== null) {
                                // Get the previous record
                                $previousData = $agentObject->getPreviousRecord(
                                    $host['id'], 
                                    $agent['agent_description'], 
                                    $metric,
                                    $latestData['timestamp']
                                );
                                
                                $agentData['metrics'][$section][$metric] = [
                                    'latest' => [
                                        'value' => $latestData['value'],
                                        'timestamp' => $latestData['timestamp']
                                    ],
                                    'previous' => $previousData,
                                    'label' => $metricConfig['label'],
                                    'link' => isset($metricConfig['link']) ? $metricConfig['link'] : null
                                ];
                                
                                // Use the most recent timestamp for the agent
                                if ($agentData['timestamp'] === null || strtotime($latestData['timestamp']) > strtotime($agentData['timestamp'])) {
                                    $agentData['timestamp'] = $latestData['timestamp'];
                                }
                            }
                        }
                    }

                    if (!empty($agentData['metrics'])) {
                        $hostData['agents'][] = $agentData;
                    }
                }

                if (!empty($hostData['agents'])) {
                    $hostsData[] = $hostData;
                }
            }

            // Load the template
            include '../app/templates/latest-data.php';
            break;

        case 'configjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformConfigjs = $settingsObject->getPlatformConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/data-configjs.php';
            break;

        case 'interfaceconfigjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformInterfaceConfigjs = $settingsObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/data-interfaceconfigjs.php';
            break;

        default:
    }

}

?>
