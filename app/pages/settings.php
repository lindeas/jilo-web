<?php

/**
 * Jilo settings management.
 *
 * This page ("settings") handles Jilo settings by
 * adding, editing, and deleting platforms, hosts, agents.
 */

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Get any new feedback messages
include '../app/helpers/feedback.php';

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';
$host = $_REQUEST['host'] ?? '';

require '../app/classes/host.php';
require '../app/classes/agent.php';

$hostObject = new Host($db);
$agentObject = new Agent($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /**
     * Handles form submissions from editing
     */

    // Apply rate limiting for profile operations
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($db, 'profile', $userId);

    // Get hash from URL if present
    $hash = parse_url($_SERVER['REQUEST_URI'], PHP_URL_FRAGMENT) ?? '';
    $redirectUrl = htmlspecialchars($app_root) . '?page=settings';
    if ($hash) {
        $redirectUrl .= '#' . $hash;
    }

    // host operations
    if (isset($_POST['item']) && $_POST['item'] === 'host') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is a host delete
            $host_id = $_POST['host'];
            $result = $hostObject->deleteHost($host_id);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Host deleted successfully.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Deleting the host failed. Error: $result", true);
            }
        } else if (!isset($_POST['host'])) { // This is a new host
            $newHost = [
                'address'       => $_POST['address'],
                'platform_id'   => $_POST['platform'],
                'name'          => empty($_POST['name']) ? $_POST['address'] : $_POST['name'],
            ];
            $result = $hostObject->addHost($newHost);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "New Jilo host added.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Adding the host failed. Error: $result", true);
            }
        } else { // This is an edit of existing host
            $host_id = $_POST['host'];
            $platform_id = $_POST['platform'];
            $updatedHost = [
                'id'      => $host_id,
                'address' => $_POST['address'],
                'name'    => empty($_POST['name']) ? $_POST['address'] : $_POST['name'],
            ];
            $result = $hostObject->editHost($platform_id, $updatedHost);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Host edited.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Editing the host failed. Error: $result", true);
            }
        }
        if (!$isAjax) {
            header('Location: ' . $redirectUrl);
            exit;
        }

    // agent operations
    } elseif (isset($_POST['item']) && $_POST['item'] === 'agent') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is an agent delete
            $agent_id = $_POST['agent'];
            $result = $agentObject->deleteAgent($agent_id);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Agent deleted successfully.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Deleting the agent failed. Error: $result", true);
            }
        } else if (isset($_POST['new']) && $_POST['new'] === 'true') { // This is a new agent
            $newAgent = [
                'type_id'       => $_POST['type'],
                'url'           => $_POST['url'],
                'secret_key'    => empty($_POST['secret_key']) ? null : $_POST['secret_key'],
                'check_period'  => empty($_POST['check_period']) ? 0 : $_POST['check_period'],
            ];
            $result = $agentObject->addAgent($_POST['host'], $newAgent);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "New Jilo agent added.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Adding the agent failed. Error: $result", true);
            }
        } else { // This is an edit of existing agent
            $agent_id = $_POST['agent'];
            $updatedAgent = [
                'agent_type_id' => $_POST['agent_type_id'],
                'url'          => $_POST['url'],
                'secret_key'   => empty($_POST['secret_key']) ? null : $_POST['secret_key'],
                'check_period' => empty($_POST['check_period']) ? 0 : $_POST['check_period'],
            ];
            $result = $agentObject->editAgent($agent_id, $updatedAgent);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Agent edited.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Editing the agent failed. Error: $result", true);
            }
        }
        if (!$isAjax) {
            header('Location: ' . $redirectUrl);
            exit;
        }

    // platform operations
    } elseif (isset($_POST['item']) && $_POST['item'] === 'platform') {
        if (isset($_POST['delete']) && $_POST['delete'] === 'true') { // This is a platform delete
            $platform_id = $_POST['platform'];
            $result = $platformObject->deletePlatform($platform_id);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Platform deleted successfully.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Deleting the platform failed. Error: $result", true);
            }
        } else if (!isset($_POST['platform'])) { // This is a new platform
            $newPlatform = [
                'name'          => $_POST['name'],
                'jitsi_url'     => $_POST['jitsi_url'],
                'jilo_database' => $_POST['jilo_database'],
            ];
            $result = $platformObject->addPlatform($newPlatform);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "New Jitsi platform added.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Adding the platform failed. Error: $result", true);
            }
        } else { // This is an edit of existing platform
            $platform_id = $_POST['platform'];
            $updatedPlatform = [
                'name'          => $_POST['name'],
                'jitsi_url'     => $_POST['jitsi_url'],
                'jilo_database' => $_POST['jilo_database'],
            ];
            $result = $platformObject->editPlatform($platform_id, $updatedPlatform);
            if ($result === true) {
                Feedback::flash('NOTICE', 'DEFAULT', "Platform edited.", true);
            } else {
                Feedback::flash('ERROR', 'DEFAULT', "Editing the platform failed. Error: $result", true);
            }
        }
        header('Location: ' . $redirectUrl);
        exit;
    }

} else {
    /**
     * Handles GET requests to display templates.
     */

    if ($userObject->hasRight($userId, 'view settings') || $userObject->hasRight($userId, 'superuser')) {
        $jilo_agent_types = $agentObject->getAgentTypes();
        include '../app/templates/settings.php';
    } else {
        include '../app/templates/error-unauthorized.php';
    }
}
