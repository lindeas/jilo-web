<?php

/**
 * Configuration management.
 *
 * This page ("config") handles configuration by adding, editing, and deleting platforms,
 * hosts, agents, and the configuration file itself.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';
$host = $_REQUEST['host'] ?? '';

require '../app/classes/config.php';
require '../app/classes/host.php';
require '../app/classes/agent.php';

$configObject = new Config();
$hostObject = new Host($dbWeb);
$agentObject = new Agent($dbWeb);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
     * Handles form submissions from editing
     */

    // editing the config file
    if (isset($_POST['item']) && $_POST['item'] === 'config_file') {
        // check if file is writable
        if (!is_writable($config_file)) {
            $_SESSION['error'] = "Configuration file is not writable.";
        } else {
            $result = $configObject->editConfigFile($_POST, $config_file);
            if ($result === true) {
                $_SESSION['notice'] = "The config file is edited.";
            } else {
                $_SESSION['error'] = "Editing the config file failed. Error: $result";
            }
        }

    // host operations
    } elseif (isset($_POST['item']) && $_POST['item'] === 'host') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is a host delete
            $host_id = $_POST['host'];
            $result = $hostObject->deleteHost($host_id);
            if ($result === true) {
                $_SESSION['notice'] = "Host deleted successfully.";
            } else {
                $_SESSION['error'] = "Deleting the host failed. Error: $result";
            }
        } else if (!isset($_POST['host'])) { // This is a new host
            $newHost = [
                'address'       => $_POST['address'],
                'platform_id'   => $_POST['platform'],
                'name'          => $_POST['name'],
            ];
            $result = $hostObject->addHost($newHost);
            if ($result === true) {
                $_SESSION['notice'] = "New Jilo host added.";
            } else {
                $_SESSION['error'] = "Adding the host failed. Error: $result";
            }
        } else { // This is an edit of existing host
            $host_id = $_POST['host'];
            $platform_id = $_POST['platform'];
            $updatedHost = [
                'id'      => $host_id,
                'address' => $_POST['address'],
                'name'    => $_POST['name'],
            ];
            $result = $hostObject->editHost($platform_id, $updatedHost);
            if ($result === true) {
                $_SESSION['notice'] = "Host edited.";
            } else {
                $_SESSION['error'] = "Editing the host failed. Error: $result";
            }
        }

    // agent operations
    } elseif (isset($_POST['item']) && $_POST['item'] === 'agent') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is an agent delete
            $agent_id = $_POST['agent'];
            $result = $agentObject->deleteAgent($agent_id);
            if ($result === true) {
                $_SESSION['notice'] = "Agent deleted successfully.";
            } else {
                $_SESSION['error'] = "Deleting the agent failed. Error: $result";
            }
        } else if (isset($_POST['new']) && $_POST['new'] === 'true') { // This is a new agent
            $newAgent = [
                'type_id'       => $_POST['type'],
                'url'           => $_POST['url'],
                'secret_key'    => $_POST['secret_key'],
                'check_period'  => $_POST['check_period'],
            ];
            $result = $agentObject->addAgent($_POST['platform'], $newAgent);
            if ($result === true) {
                $_SESSION['notice'] = "New Jilo Agent added.";
            } else {
                $_SESSION['error'] = "Adding the agent failed. Error: $result";
            }
        } else { // This is an edit of existing agent
            $agent_id = $_POST['agent'];
            $platform_id = $_POST['platform'];
            $updatedAgent = [
                'id'            => $agent_id,
                'agent_type_id' => $_POST['agent_type_id'],
                'url'           => $_POST['url'],
                'secret_key'    => $_POST['secret_key'],
                'check_period'  => $_POST['check_period']
            ];
            $result = $agentObject->editAgent($platform_id, $updatedAgent);
            if ($result === true) {
                $_SESSION['notice'] = "Agent edited.";
            } else {
                $_SESSION['error'] = "Editing the agent failed. Error: $result";
            }
        }

    // platform operations
    } elseif (isset($_POST['item']) && $_POST['item'] === 'platform') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is a platform delete
            $platform_id = $_POST['platform'];
            $result = $platformObject->deletePlatform($platform_id);
            if ($result === true) {
                $_SESSION['notice'] = "Platform deleted successfully.";
            } else {
                $_SESSION['error'] = "Deleting the platform failed. Error: $result";
            }
        } else if (!isset($_POST['platform'])) { // This is a new platform
            $newPlatform = [
                'name'          => $_POST['name'],
                'jitsi_url'     => $_POST['jitsi_url'],
                'jilo_database' => $_POST['jilo_database'],
            ];
            $result = $platformObject->addPlatform($newPlatform);
            if ($result === true) {
                $_SESSION['notice'] = "New Jitsi platform added.";
            } else {
                $_SESSION['error'] = "Adding the platform failed. Error: $result";
            }
        } else { // This is an edit of existing platform
            $platform_id = $_POST['platform'];
            $updatedPlatform = [
                'id'            => $platform_id,
                'name'          => $_POST['name'],
                'jitsi_url'     => $_POST['jitsi_url'],
                'jilo_database' => $_POST['jilo_database'],
            ];
            $result = $platformObject->editPlatform($updatedPlatform);
            if ($result === true) {
                $_SESSION['notice'] = "Platform edited.";
            } else {
                $_SESSION['error'] = "Editing the platform failed. Error: $result";
            }
        }
    }

    // After any POST operation, redirect back to the main config page
    header("Location: $app_root?page=config");
    exit();

} else {
    /**
     * Handles GET requests to display templates.
     */

    switch ($item) {

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

        default:
            if ($userObject->hasRight($user_id, 'view config file')) {
                include '../app/templates/config-jilo.php';
            } else {
                include '../app/templates/error-unauthorized.php';
            }
    }
}

?>
