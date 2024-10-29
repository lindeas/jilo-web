<?php

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';

require '../app/classes/config.php';
require '../app/classes/agent.php';

$configObject = new Config();
$agentObject = new Agent($dbWeb);

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

// FIXME - if editing the flat file is no more needed, remove this
//    // load the config file and initialize a copy
//    $content = file_get_contents($config_file);
//    $updatedContent = $content;

    // new agent adding
    if (isset($_POST['new']) && isset($_POST['item']) && $_POST['new'] === 'true' && $_POST['item'] === 'agent') {
        $newAgent = [
            'type_id'       => $type,
            'url'           => $url,
            'secret_key'	=> $secret_key,
            'check_period'  => $check_period,
        ];
        $result = $agentObject->addAgent($platform_id, $newAgent);
        if ($result === true) {
            $_SESSION['notice'] = "New Jilo Agent added.";
        } else {
            $_SESSION['error'] = "Adding the agent failed. Error: $result";
        }

    // new platform adding
    } elseif (isset($_POST['new']) && $_POST['new'] === 'true') {
        $newPlatform = [
            'name'          => $name,
            'jitsi_url'		=> $_POST['jitsi_url'],
            'jilo_database'	=> $_POST['jilo_database'],
        ];
        $platformObject->addPlatform($newPlatform);

    // deleting an agent
    } elseif (isset($_POST['delete']) && isset($_POST['agent']) && $_POST['delete'] === 'true') {
        $result = $agentObject->deleteAgent($agent);
        if ($result === true) {
            $_SESSION['notice'] = "Agent id \"{$_REQUEST['agent']}\" deleted.";
        } else {
            $_SESSION['error'] = "Deleting the agent failed. Error: $result";
        }

    // deleting a platform
    } elseif (isset($_POST['delete']) && $_POST['delete'] === 'true') {
        $platform = $_POST['platform'];
        $platformObject->deletePlatform($platform);

    // an update to an existing agent
    } elseif (isset($_POST['agent'])) {
        $updatedAgent = [
            'id'            => $agent,
            'agent_type_id' => $type,
            'url'           => $url,
            'secret_key'	=> $secret_key,
            'check_period'  => $check_period,
        ];
        $result = $agentObject->editAgent($platform_id, $updatedAgent);
        if ($result === true) {
            $_SESSION['notice'] = "Agent id \"{$_REQUEST['agent']}\" edited.";
        } else {
            $_SESSION['error'] = "Editing the agent failed. Error: $result";
        }

    // an update to an existing platform
    } else {
        $platform = $_POST['platform'];
        $updatedPlatform = [
            'name'		    => $name,
            'jitsi_url'		=> $_POST['jitsi_url'],
            'jilo_database'	=> $_POST['jilo_database'],
        ];
        $platformObject->editPlatform($platform, $updatedPlatform);

    }

// FIXME - if this is not needed for editing the flat file, remove it
//    // check if file is writable
//    if (!is_writable($config_file)) {
//        $_SESSION['error'] = getError('Configuration file is not writable.');
//        header("Location: $app_root?platform=$platform_id&page=config");
//        exit();
//    }
//
//    // try to update the config file
//    if (file_put_contents($config_file, $updatedContent) !== false) {
//        // update successful
//        $_SESSION['notice'] = "Configuration for {$_POST['name']} is updated.";
//    } else {
//        // unsuccessful
//        $error = error_get_last();
//        $_SESSION['error'] = getError('Error updating the config: ' . ($error['message'] ?? 'unknown error'));
//    }

// FIXME the new file is not loaded on first page load
    unset($config);
    header("Location: $app_root?platform=$platform_id&page=config");
    exit();

// no form submitted, show the templates
} else {

    switch ($item) {
        case 'platforms':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformConfigjs = $configObject->getPlatformConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/config-list-configjs.php';
            break;
        case 'hosts':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformInterfaceConfigjs = $configObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/config-list-interfaceconfigjs.php';
            break;
        case 'endpoints':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformInterfaceConfigjs = $configObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/config-list-interfaceconfigjs.php';
            break;
        case 'config_file':
            if (isset($action) && $action === 'edit') {
                include '../app/templates/config-configfile-edit.php';
            } else {
                if ($userObject->hasRight($user_id, 'view config file')) {
                    include '../app/templates/config-configfile.php';
                } else {
                    include '../app/templates/error-unauthorized.php';
                }
            }
            break;

    // if there is no $item, we work on the local config DB
        default:
            switch ($action) {
                case 'add-agent':
                    $jilo_agent_types = $agentObject->getAgentTypes();
                    $jilo_agents_in_platform = $agentObject->getPlatformAgentTypes($platform_id);
                    $jilo_agent_types_in_platform = array_column($jilo_agents_in_platform, 'agent_type_id');
                    include '../app/templates/config-add-agent.php';
                    break;
                case 'add':
                    include '../app/templates/config-add-platform.php';
                    break;
                case 'edit':
                    if (isset($_GET['agent'])) {
                        $agentDetails = $agentObject->getAgentDetails($platform_id, $agent);
                        $jilo_agent_types = $agentObject->getAgentTypes();
                        include '../app/templates/config-edit-agent.php';
                    } else {
                        include '../app/templates/config-edit-platform.php';
                    }
                    break;
                case 'delete':
                    if (isset($_GET['agent'])) {
                        $agentDetails = $agentObject->getAgentDetails($platform_id, $agent);
                        include '../app/templates/config-delete-agent.php';
                    } else {
                        include '../app/templates/config-delete-platform.php';
                    }
                    break;
            }
    }
}

?>
