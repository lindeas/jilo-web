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
     * Handles form submissions from editing page
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

    // new host adding
    } elseif (isset($_POST['new']) && isset($_POST['item']) && $_POST['new'] === 'true' && $_POST['item'] === 'host') {
        $newHost = [
            'address'       => $address,
            'port'          => $port,
            'platform_id'   => $platform_id,
            'name'          => $name,
        ];
        $result = $hostObject->addHost($newHost);
        if ($result === true) {
            $_SESSION['notice'] = "New Jilo host added.";
        } else {
            $_SESSION['error'] = "Adding the host failed. Error: $result";
        }

    // new agent adding
    } elseif (isset($_POST['new']) && isset($_POST['item']) && $_POST['new'] === 'true' && $_POST['item'] === 'agent') {
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
        $result = $platformObject->addPlatform($newPlatform);
        if ($result === true) {
            $_SESSION['notice'] = "New Jitsi platform added.";
        } else {
            $_SESSION['error'] = "Adding the platform failed. Error: $result";
        }

    // deleting a host
    } elseif (isset($_POST['delete']) && isset($_POST['host']) && $_POST['delete'] === 'true') {
        $result = $hostObject->deleteHost($host);
        if ($result === true) {
            $_SESSION['notice'] = "Host id \"{$_REQUEST['host']}\" deleted.";
        } else {
            $_SESSION['error'] = "Deleting the host failed. Error: $result";
        }

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
        $result = $platformObject->deletePlatform($platform);
        if ($result === true) {
            $_SESSION['notice'] = "Platform \"{$platformObject['name']}\" added.";
        } else {
            $_SESSION['error'] = "Adding the platform failed. Error: $result";
        }

    // an update to an existing host
    } elseif (isset($_POST['item']) && $_POST['item'] === 'host') {
        $host_id = $_POST['host'];
        $platform_id = $_POST['platform'];
        $updatedHost = [
            'id'      => $host_id,
            'address' => $_POST['address'],
            'name'    => $_POST['name']
        ];
        $result = $hostObject->editHost($platform_id, $updatedHost);

        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            // Clear any output buffers to ensure clean JSON
            while (ob_get_level()) ob_end_clean();
            
            header('Content-Type: application/json');
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Editing the host failed. Error: $result"
                ]);
            }
            exit();
        } else {
            // Regular form submission
            if ($result === true) {
                $_SESSION['notice'] = "Host edited.";
            } else {
                $_SESSION['error'] = "Editing the host failed. Error: $result";
            }
            header("Location: $app_root?page=config&item=$item");
            exit();
        }

    // an update to an existing agent
    } elseif (isset($_POST['item']) && $_POST['item'] === 'agent') {
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

        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            // Clear any output buffers to ensure clean JSON
            while (ob_get_level()) ob_end_clean();
            
            header('Content-Type: application/json');
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Editing the agent failed. Error: $result"
                ]);
            }
            exit();
        } else {
            // Regular form submission
            if ($result === true) {
                $_SESSION['notice'] = "Agent edited.";
            } else {
                $_SESSION['error'] = "Editing the agent failed. Error: $result";
            }
            header("Location: $app_root?page=config&item=$item");
            exit();
        }

    // an update to an existing platform
    } else {
        $platform = $_POST['platform_id']; 
        $updatedPlatform = [
            'name'          => $_POST['name'],
            'jitsi_url'     => $_POST['jitsi_url'],
            'jilo_database' => $_POST['jilo_database']
        ];
        $result = $platformObject->editPlatform($platform, $updatedPlatform);
        
        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            
            // Clear any output buffers to ensure clean JSON
            while (ob_get_level()) ob_end_clean();
            
            header('Content-Type: application/json');
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Editing the platform failed. Error: $result"
                ]);
            }
            exit();
        } else {
            // Regular form submission
            if ($result === true) {
                $_SESSION['notice'] = "Platform edited.";
            } else {
                $_SESSION['error'] = "Editing the platform failed. Error: $result";
            }
            header("Location: $app_root?page=config&item=$item");
            exit();
        }
    }

// FIXME the new file is not loaded on first page load
    unset($config);
    header("Location: $app_root?page=config&item=$item");
    exit();

} else {
    /**
     * Handles GET requests to display templates.
     */

    switch ($item) {

        case 'platform':
            if (isset($action) && $action === 'add') {
                include '../app/templates/config-platform-add.php';
            } elseif (isset($action) && $action === 'edit') {
                include '../app/templates/config-platform-edit.php';
            } elseif (isset($action) && $action === 'delete') {
                include '../app/templates/config-platform-delete.php';
            } else {
                if ($userObject->hasRight($user_id, 'view config file')) {
                    include '../app/templates/config-platform.php';
                } else {
                    include '../app/templates/error-unauthorized.php';
                }
            }
            break;

        case 'host':
            if (isset($action) && $action === 'add') {
                include '../app/templates/config-host-add.php';
            } elseif (isset($action) && $action === 'edit') {
                $hostDetails = $hostObject->getHostDetails($platform_id, $agent);
                include '../app/templates/config-host-edit.php';
            } elseif (isset($action) && $action === 'delete') {
                $hostDetails = $hostObject->getHostDetails($platform_id, $agent);
                include '../app/templates/config-host-delete.php';
            } else {
                if ($userObject->hasRight($user_id, 'view config file')) {
                    $hostDetails = $hostObject->getHostDetails();
                    include '../app/templates/config-host.php';
                } else {
                    include '../app/templates/error-unauthorized.php';
                }
            }
            break;

        case 'agent':
            if (isset($action) && $action === 'add') {
                $jilo_agent_types = $agentObject->getAgentTypes();
                $platform_id = $_REQUEST['platform'] ?? '';
                if (!empty($platform_id)) {
                    $jilo_agents_in_platform = $agentObject->getPlatformAgentTypes($platform_id);
                    $jilo_agent_types_in_platform = array_column($jilo_agents_in_platform, 'agent_type_id');
                    include '../app/templates/config-agent-add.php';
                } else {
                    $_SESSION['error'] = "Platform ID is required to add an agent.";
                    header("Location: $app_root?page=config&item=agent");
                    exit();
                }
            } elseif (isset($action) && $action === 'edit') {
                if (isset($_REQUEST['agent'])) {
                    $platform_id = $_REQUEST['platform'] ?? '';
                    $agentDetails = $agentObject->getAgentDetails($platform_id, $agent)['0'];
                    $jilo_agent_types = $agentObject->getAgentTypes();
                    include '../app/templates/config-agent-edit.php';
                }
            } elseif (isset($action) && $action === 'delete') {
                if (isset($_REQUEST['agent'])) {
                    $platform_id = $_REQUEST['platform'] ?? '';
                    $agentDetails = $agentObject->getAgentDetails($platform_id, $agent)['0'];
                    include '../app/templates/config-agent-delete.php';
                }
            } else {
                if ($userObject->hasRight($user_id, 'view config file')) {
                    include '../app/templates/config-agent.php';
                } else {
                    include '../app/templates/error-unauthorized.php';
                }
            }
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

        default:
        // the default config page is the platforms page
            header("Location: $app_root?page=config&item=platform");
            exit();
    }
}

?>
