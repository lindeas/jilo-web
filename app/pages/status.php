<?php

/**
 * Jilo components status checks
 *
 * This page ("status") checks the status of various Jilo platform components
 * by fetching data from agents and determining their availability.
 * It generates output for each platform and agent.
 */

require '../app/classes/agent.php';
$agentObject = new Agent($dbWeb);

include '../app/templates/status-server.php';

// loop through all platforms to check their agents
foreach ($platformsAll as $platform) {

    // check if we can connect to the jilo database
    $response = connectDB($config, 'jilo', $platform['jilo_database'], $platform['id']);
    if ($response['error'] !== null) {
        $jilo_database_status = '<span class="text-danger">' . htmlspecialchars($response['error']) . '</span>';
    } else {
        $jilo_database_status = '<span class="text-success">OK</span>';
    }

    include '../app/templates/status-platform.php';

    // fetch agent details for the current platform
    $agentDetails = $agentObject->getAgentDetails($platform['id']);
    foreach ($agentDetails as $agent) {
        $agent_url = parse_url($agent['url']);
        $agent_protocol = isset($agent_url['scheme']) ? $agent_url['scheme']: '';
        $agent_host = isset($agent_url['host']) ? $agent_url['host']: '';
        $agent_port = isset($agent_url['port']) ? $agent_url['port']: '';

        // we get agent data to check availability
        $agent_response = $agentObject->fetchAgent($agent['id'], true);
        $agent_data = json_decode($agent_response);

        // determine agent availability based on response data
        if (json_last_error() === JSON_ERROR_NONE) {
            $agent_availability = '<span class="text-warning">unknown</span>';
            foreach ($agent_data as $key => $value) {
                if ($key === 'error') {
                    $agent_availability = '<span class="text-danger">' . htmlspecialchars($value) . '</span>';
                    break;
                }
                if (preg_match('/_state$/', $key)) {
                    if ($value === 'error') {
                        $agent_availability = '<span class="text-danger">not running</span>';
                        break;
                    }
                    if ($value === 'running') {
                        $agent_availability = '<span class="text-success">running</span>';
                        break;
                    }
                }
            }
        } else {
            $agent_availability = 'json error';
        }

        include '../app/templates/status-agent.php';
    }
}

?>
