<?php

require '../app/classes/agent.php';
require '../app/classes/host.php';

$agentObject = new Agent($dbWeb);
$hostObject = new Host($dbWeb);

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

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/latest.php';
