<?php

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';
require '../app/classes/agent.php';

$agentObject = new Agent($dbWeb);

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

// FIXME code here

    $force = isset($_POST['force']) && $_POST['force'] == 'true';
    $result = fetchAgent($agent_id, $force);

    if ($result !== false) {
        echo $result; // Return the API response as JSON
    } else {
        echo json_encode(['error' => 'Failed to fetch API data']);
    }

//    header("Location: $app_root?platform=$platform_id&page=config");
//    exit();

// no form submitted, show the templates
} else {
    $agentDetails = $agentObject->getAgentDetails($platform_id);
    include '../app/templates/agent-list.php';
}

?>
