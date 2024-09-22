<?php

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';
require '../app/classes/agent.php';

$agentObject = new Agent($dbWeb);

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // new agent adding
//    if (isset($_POST['new']) && $_POST['new'] === 'true') {
//        $newAgent = [
//            'type_id'		=> 1,
//            'url'		=> $_POST['url'],
//            'secret_key'	=> $_POST['secret_key'],
//        ];
//        $result = $agentObject->addAgent($platform_id, $newAgent);
//        if ($result === true) {
//            $_SESSION['notice'] = "New Jilo Agent added.";
//        } else {
//            $_SESSION['error'] = "Adding the agent failed. Error: $result";
//        }

    // deleting an agent
//    } elseif (isset($_POST['delete']) && $_POST['delete'] === 'true') {
//        $result = $agentObject->deleteAgent($agent);
//        if ($result === true) {
//            $_SESSION['notice'] = "Agent id \"{$_REQUEST['agent']}\" deleted.";
//        } else {
//            $_SESSION['error'] = "Deleting the agent failed. Error: $result";
//        }

    // an update to an existing agent
//    } else {
//        $updatedAgent = [
//            'id'		=> $agent,
//            'type_id'		=> 1,
//            'url'		=> $_POST['url'],
//            'secret_key'	=> $_POST['secret_key'],
//        ];
//        $result = $agentObject->editAgent($platform_id, $updatedAgent);
//        if ($result === true) {
//            $_SESSION['notice'] = "Agent id \"{$_REQUEST['agent']}\" edited.";
//        } else {
//            $_SESSION['error'] = "Editing the agent failed. Error: $result";
//        }
//
//    }

    header("Location: $app_root?platform=$platform_id&page=agents");
    exit();

// no form submitted, show the templates
} else {

    switch ($action) {
        case 'add':
            include '../app/templates/agent-add.php';
            break;
        case 'edit':
            $agentDetails = $agentObject->getAgentDetails($platform_id, $agent);
            include '../app/templates/agent-edit.php';
            break;
        case 'delete':
            $agentDetails = $agentObject->getAgentDetails($platform_id, $agent);
            include '../app/templates/agent-delete.php';
            break;
        default:
            $agentDetails = $agentObject->getAgentDetails($platform_id);
            include '../app/templates/agent-list.php';
    }
}

?>
