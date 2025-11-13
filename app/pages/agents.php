<?php

/**
 * Agent cache management
 *
 * This page ("agents") handles caching for agents. It allows storing, clearing, and retrieving
 * agent-related data in the session using AJAX requests. The cache is stored with a timestamp
 * to allow time-based invalidation if needed.
 */

// Constants for session keys and cache settings
define('SESSION_CACHE_SUFFIX', '_cache');
define('SESSION_CACHE_TIME_SUFFIX', '_cache_time');
define('CACHE_EXPIRY_TIME', 3600); // 1 hour in seconds

// Input validation
$action = isset($_GET['action']) ? htmlspecialchars(trim($_GET['action']), ENT_QUOTES, 'UTF-8') : '';
$agentId = filter_input(INPUT_GET, 'agent', FILTER_VALIDATE_INT);

require '../app/classes/agent.php';
require '../app/classes/host.php';
$agentObject = new Agent($db);
$hostObject = new Host($db);

/**
 * Get the cache key for an agent
 * @param int $agentId The agent ID
 * @param string $suffix The suffix to append (_cache or _cache_time)
 * @return string The cache key
 */
function getAgentCacheKey($agentId, $suffix) {
    return "agent{$agentId}{$suffix}";
}

/**
 * Check if cache is expired
 * @param int $agentId The agent ID
 * @return bool True if cache is expired or doesn't exist
 */
function isCacheExpired($agentId) {
    $timeKey = getAgentCacheKey($agentId, SESSION_CACHE_TIME_SUFFIX);
    if (!isset($_SESSION[$timeKey])) {
        return true;
    }
    return (time() - $_SESSION[$timeKey]) > CACHE_EXPIRY_TIME;
}

// Handle POST request (saving to cache)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Apply rate limiting for adding new contacts
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($db, 'contact', $userId);

    // Validate agent ID for POST operations
    if ($agentId === false || $agentId === null) {
        Feedback::flash('ERROR', 'DEFAULT', 'Invalid agent ID format');
        echo json_encode(['status' => 'error', 'message' => 'Invalid agent ID format']);
        exit;
    }

    // Read and validate JSON data
    $jsonData = file_get_contents("php://input");
    if ($jsonData === false) {
        Feedback::flash('ERROR', 'DEFAULT', 'Failed to read input data');
        echo json_encode(['status' => 'error', 'message' => 'Failed to read input data']);
        exit;
    }

    $data = json_decode($jsonData, true);

    // Handle cache clearing
    if ($data === null && !empty($agentId)) {
        $cacheKey = getAgentCacheKey($agentId, SESSION_CACHE_SUFFIX);
        $timeKey = getAgentCacheKey($agentId, SESSION_CACHE_TIME_SUFFIX);

        unset($_SESSION[$cacheKey]);
        unset($_SESSION[$timeKey]);

        Feedback::flash('SUCCESS', 'DEFAULT', "Cache for agent {$agentId} is cleared.");
        echo json_encode([
            'status' => 'success',
            'message' => "Cache for agent {$agentId} is cleared."
        ]);
    }
    // Handle cache storing
    elseif ($data) {
        $cacheKey = getAgentCacheKey($agentId, SESSION_CACHE_SUFFIX);
        $timeKey = getAgentCacheKey($agentId, SESSION_CACHE_TIME_SUFFIX);

        $_SESSION[$cacheKey] = $data;
        $_SESSION[$timeKey] = time();

        Feedback::flash('SUCCESS', 'DEFAULT', "Cache for agent {$agentId} is stored.");
        echo json_encode([
            'status' => 'success',
            'message' => "Cache for agent {$agentId} is stored."
        ]);
    }
    else {
        Feedback::flash('ERROR', 'DEFAULT', 'Invalid data format');
        echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
    }

// Handle AJAX requests
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
    $agentId = filter_input(INPUT_GET, 'agent', FILTER_VALIDATE_INT);

    if ($action === 'fetch') {
        $response = ['status' => 'success', 'data' => $data];
        echo json_encode($response);
        exit;
    }

    if ($action === 'status') {
        $response = ['status' => 'success', 'data' => $statusData];
        echo json_encode($response);
        exit;
    }

// Handle template display
} else {

    // Validate platform_id is set
    if (!isset($platform_id)) {
        Feedback::flash('ERROR', 'DEFAULT', 'Platform ID is not set');
    }

    // Get host details for this platform
    $hostDetails = $hostObject->getHostDetails($platform_id);

    // Group agents by host
    $agentsByHost = [];
    foreach ($hostDetails as $host) {
        $hostId = $host['id'];
        $agentsByHost[$hostId] = [
            'host_name' => $host['name'],
            'agents' => []
        ];

        // Get agents for this host
        $hostAgents = $agentObject->getAgentDetails($hostId);
        if ($hostAgents) {
            $agentsByHost[$hostId]['agents'] = $hostAgents;
        }

        // Generate JWT tokens for each agent beforehand
        $agentTokens = [];
        foreach ($agentsByHost[$hostId]['agents'] as $agent) {
            $payload = [
                'iss' => 'Jilo Web',
                'aud' => $config['domain'],
                'iat' => time(),
                'exp' => time() + 3600,
                'agent_id' => $agent['id']
            ];
            $agentTokens[$agent['id']] = $agentObject->generateAgentToken($payload, $agent['secret_key']);
        }

        /**
         * Now we have:
         * $hostDetails - hosts in this platform
         * $agentsByHost[$hostId]['agents'] - agents details by hostId
         * $agentTokens[$agent['id']] - tokens for the agentsIds
         */
    }

    // Get any new feedback messages
    include_once '../app/helpers/feedback.php';

    // Load the template
    include '../app/templates/agents.php';
}
