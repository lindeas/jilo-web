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
        // Get latest data for both JVB and Jicofo agents
        $latestJvbConferences = $agentObject->getLatestData($platform_id, 'jvb', 'conferences');
        $latestJvbParticipants = $agentObject->getLatestData($platform_id, 'jvb', 'participants');
        $latestJicofoConferences = $agentObject->getLatestData($platform_id, 'jicofo', 'conferences');
        $latestJicofoParticipants = $agentObject->getLatestData($platform_id, 'jicofo', 'participants');

        $widget['records'] = array();

        // Format data for JVB metrics
        if ($latestJvbConferences !== null || $latestJvbParticipants !== null) {
            $widget['records'][] = [
                'table_headers' => 'JVB',
                'conferences' => $latestJvbConferences ? $latestJvbConferences['value'] : null,
                'participants' => $latestJvbParticipants ? $latestJvbParticipants['value'] : null,
                'from_time' => $latestJvbConferences ? $latestJvbConferences['timestamp'] : ($latestJvbParticipants ? $latestJvbParticipants['timestamp'] : null),
                'until_time' => $latestJvbConferences ? $latestJvbConferences['timestamp'] : ($latestJvbParticipants ? $latestJvbParticipants['timestamp'] : null)
            ];
        }

        // Format data for Jicofo metrics
        if ($latestJicofoConferences !== null || $latestJicofoParticipants !== null) {
            $widget['records'][] = [
                'table_headers' => 'Jicofo',
                'conferences' => $latestJicofoConferences ? $latestJicofoConferences['value'] : null,
                'participants' => $latestJicofoParticipants ? $latestJicofoParticipants['value'] : null,
                'from_time' => $latestJicofoConferences ? $latestJicofoConferences['timestamp'] : ($latestJicofoParticipants ? $latestJicofoParticipants['timestamp'] : null),
                'until_time' => $latestJicofoConferences ? $latestJicofoConferences['timestamp'] : ($latestJicofoParticipants ? $latestJicofoParticipants['timestamp'] : null)
            ];
        }

        // prepare the widget
        $widget['full'] = false;
        $widget['name'] = 'LatestData';
        $widget['title'] = 'Latest data from Jilo Agents';
        $widget['collapsible'] = false;
        $widget['collapsed'] = false;
        $widget['filter'] = false;
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
