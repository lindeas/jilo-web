<?php

/**
 * Jilo components status checks
 *
 * This page ("status") checks the status of various Jilo platform components
 * by fetching data from agents and determining their availability.
 * It generates output for each platform and agent.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

require '../app/classes/agent.php';
require '../app/classes/host.php';
$agentObject = new Agent($dbWeb);
$hostObject = new Host($dbWeb);

include '../app/templates/status-server.php';

// loop through all platforms to check their agents
foreach ($platformsAll as $platform) {

    // check if we can connect to the jilo database
    $response = connectDB($config, 'jilo', $platform['jilo_database'], $platform['id']);
    if ($response['error'] !== null) {
        $jilo_database_status = $response['error'];
    } else {
        $jilo_database_status = 'Connected';
    }

    include '../app/templates/status-platform.php';

    // fetch hosts for the current platform
    $hostDetails = $hostObject->getHostDetails($platform['id']);
    foreach ($hostDetails as $host) {
        // fetch agent details for the current host
        $agentDetails = $agentObject->getAgentDetails($host['id']);
        foreach ($agentDetails as $agent) {
            // we try to parse the URL to scheme:/host:port
            $agent_url = parse_url($agent['url']);
            $agent_protocol = isset($agent_url['scheme']) ? $agent_url['scheme']: '';
            // on failure we keep the full value for displaying purpose
            $agent_host = isset($agent_url['host']) ? $agent_url['host']: $agent['url'];
            $agent_port = isset($agent_url['port']) ? $agent_url['port']: '';

            // we get agent data to check availability
            $agent_response = $agentObject->fetchAgent($agent['id'], true);
            $agent_data = json_decode($agent_response);

            // determine agent availability based on response data
            if (json_last_error() === JSON_ERROR_NONE) {
                $agent_availability = 'unknown';
                foreach ($agent_data as $key => $value) {
                    if ($key === 'error') {
                        $agent_availability = $value;
                        break;
                    }
                    if (preg_match('/_state$/', $key)) {
                        if ($value === 'error') {
                            $agent_availability = 'not running';
                            break;
                        }
                        if ($value === 'running') {
                            $agent_availability = 'running';
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
    echo "\n\t\t\t\t\t\t\t</div>\n";
}
echo "\n\t\t\t\t\t\t</div>";
echo "\n\t\t\t\t\t</div>";
echo "\n\t\t\t\t</div>";

?>
