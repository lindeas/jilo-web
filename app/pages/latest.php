<?php

require '../app/classes/agent.php';

$agentObject = new Agent($dbWeb);

$latestJvbConferences = $agentObject->getLatestData($platform_id, 'jvb', 'conferences');
$latestJvbParticipants = $agentObject->getLatestData($platform_id, 'jvb', 'participants');
$latestJicofoConferences = $agentObject->getLatestData($platform_id, 'jicofo', 'conferences');
$latestJicofoParticipants = $agentObject->getLatestData($platform_id, 'jicofo', 'participants');

$widget['records'] = array();

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LatestData';
$widget['title'] = 'Latest data from Jilo Agents';
$widget['collapsible'] = false;
$widget['collapsed'] = false;
$widget['filter'] = false;
if (!empty($latestJvbConferences) && !empty($latestJvbParticipants) && !empty($latestJicofoConferences) && !empty($latestJicofoParticipants)) {
    $widget['full'] = true;
}
$widget['pagination'] = true;


include '../app/templates/latest-data.php';

?>
