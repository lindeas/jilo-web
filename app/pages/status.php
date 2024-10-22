<?php

// Jilo components status checks
//

require '../app/classes/agent.php';
$agentObject = new Agent($dbWeb);

include '../app/templates/status-server.php';

foreach ($platformsAll as $platform) {
    include '../app/templates/status-platform.php';
    $agentDetails = $agentObject->getAgentDetails($platform['id']);
    foreach ($agentDetails as $agent) {
        include '../app/templates/status-agent.php';
    }
}

?>
