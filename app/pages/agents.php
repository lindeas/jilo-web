<?php

/**
 * Agent cache management
 *
 * This page ("agents") handles caching for agents. It allows storing, clearing, and retrieving
 * agent-related data in the session using AJAX requests. The cache is stored with a timestamp
 * to allow time-based invalidation if needed.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

$action = $_REQUEST['action'] ?? '';
$agent = $_REQUEST['agent'] ?? '';
require '../app/classes/agent.php';

$agentObject = new Agent($dbWeb);

// if it's a POST request, it's saving to cache
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // read the JSON sent from javascript
    $data = file_get_contents("php://input");
    $result = json_decode($data, true);

    // store the data in the session
    if ($result) {
        $_SESSION["agent{$agent}_cache"] = $result;
        $_SESSION["agent{$agent}_cache_time"] = time();  // store the cache time
        echo json_encode([
            'status'    => 'success',
            'message'   => "Cache for agent {$agent} is stored."
        ]);
    } elseif ($result === null && !empty($agent)) {
        unset($_SESSION["agent{$agent}_cache"]);
        unset($_SESSION["agent{$agent}_cache_time"]);
        echo json_encode([
            'status'    => 'success',
            'message'   => "Cache for agent {$agent} is cleared."
        ]);
    } else {
        echo json_encode([
            'status'    => 'error',
            'message'   => 'Invalid data'
        ]);
    }

//// if it's a GET request, it's read/load from cache
//} elseif ($loadcache === true) {
//
//    // check if cached data exists in session
//    if (isset($_SESSION["agent{$agent}_cache"])) {
//        // return the cached data in JSON format
//        echo json_encode(['status' => 'success', 'data' => $_SESSION["agent{$agent}_cache"]]);
//    } else {
//        // if no cached data exists
//        echo json_encode(['status' => 'error', 'message' => 'No cached data found']);
//    }

// no form submitted, show the templates
} else {
    $agentDetails = $agentObject->getAgentDetails($platform_id);
    include '../app/templates/agent-list.php';
}

?>
