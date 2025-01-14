<?php

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';

require '../app/classes/config.php';
require '../app/classes/agent.php';

$configObject = new Config();
$agentObject = new Agent($dbWeb);

switch ($item) {

    case 'graphs':
        // FIXME example data
        $one = date('Y-m-d',strtotime("-5 days"));
        $two = date('Y-m-d',strtotime("-4 days"));
        $three = date('Y-m-d',strtotime("-2 days"));
        $four = date('Y-m-d',strtotime("-1 days"));

        $graph[0]['data0'] = [
            ['date' => $one, 'value' => 10],
            ['date' => $two, 'value' => 20],
            ['date' => $three, 'value' => 15],
            ['date' => $four, 'value' => 25],
        ];

        $graph[0]['data1'] = [
            ['date' => $one, 'value' => 12],
            ['date' => $two, 'value' => 23],
            ['date' => $three, 'value' => 11],
            ['date' => $four, 'value' => 27],
        ];

        $graph[0]['graph_name'] = 'conferences';
        $graph[0]['graph_title'] = 'Conferences in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
        $graph[0]['graph_data0_label'] = 'Conferences from Jitsi logs (Jilo)';
        $graph[0]['graph_data1_label'] = 'Conferences from Jitsi API (Jilo Agents)';

        $graph[1]['data0'] = [
            ['date' => $one, 'value' => 20],
            ['date' => $two, 'value' => 30],
            ['date' => $three, 'value' => 15],
            ['date' => $four, 'value' => 55],
        ];

        $graph[1]['data1'] = [
            ['date' => $one, 'value' => 22],
            ['date' => $two, 'value' => 33],
            ['date' => $three, 'value' => 11],
            ['date' => $four, 'value' => 57],
        ];

        $graph[1]['graph_name'] = 'participants';
        $graph[1]['graph_title'] = 'Participants in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
        $graph[1]['graph_data0_label'] = 'Participants from Jitsi logs (Jilo)';
        $graph[1]['graph_data1_label'] = 'Participants from Jitsi API (Jilo Agents)';

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

        // Get latest data for all the agents
        $agents = ['jvb', 'jicofo', 'jibri', 'prosody', 'nginx'];
        $widget['records'] = [];

        // Initialize records for each agent
        foreach ($agents as $agent) {
            $record = [
                'table_headers' => strtoupper($agent),
                'metrics' => [],
                'timestamp' => null
            ];

            // Fetch all metrics for this agent
            foreach ($metrics as $section => $section_metrics) {
                foreach ($section_metrics as $metric => $config) {
                    $data = $agentObject->getLatestData($platform_id, $agent, $metric);
                    if ($data !== null) {
                        $record['metrics'][$section][$metric] = [
                            'value' => $data['value'],
                            'label' => $config['label'],
                            'link' => isset($config['link']) ? $config['link'] : null
                        ];
                        // Use the most recent timestamp
                        if ($record['timestamp'] === null || strtotime($data['timestamp']) > strtotime($record['timestamp'])) {
                            $record['timestamp'] = $data['timestamp'];
                        }
                    }
                }
            }

            if (!empty($record['metrics'])) {
                $widget['records'][] = $record;
            }
        }

        // prepare the widget
        $widget['full'] = false;
        $widget['name'] = 'LatestData';
        $widget['title'] = 'Latest data from Jilo Agents';
        $widget['collapsible'] = false;
        $widget['collapsed'] = false;
        $widget['filter'] = false;
        $widget['metrics'] = $metrics; // Pass metrics configuration to template
        if (!empty($widget['records'])) {
            $widget['full'] = true;
        }
        $widget['pagination'] = false;

        include '../app/templates/latest-data.php';
        break;

    case 'configjs':
        $mode = $_REQUEST['mode'] ?? '';
        $raw = ($mode === 'raw');
        $platformConfigjs = $configObject->getPlatformConfigjs($platformDetails[0]['jitsi_url'], $raw);
        include '../app/templates/data-configjs.php';
        break;

    case 'interfaceconfigjs':
        $mode = $_REQUEST['mode'] ?? '';
        $raw = ($mode === 'raw');
        $platformInterfaceConfigjs = $configObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
        include '../app/templates/data-interfaceconfigjs.php';
        break;

    default:
}

?>
