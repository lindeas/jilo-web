<?php

/**
 * class Agent
 *
 * Provides methods to interact with Jilo agents, including retrieving details, managing agents, generating JWT tokens,
 * and fetching data from agent APIs.
 */
class Agent {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Agent constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    /**
     * Retrieves details of a specified agent ID (or all agents) in a specified platform.
     *
     * @param int $platform_id The platform ID to filter agents by.
     * @param int $agent_id The agent ID to filter by. If empty, all agents are returned.
     *
     * @return array The list of agent details.
     */
    public function getAgentDetails($platform_id, $agent_id = '') {
        $sql = 'SELECT
                    ja.id,
                    ja.platform_id,
                    ja.agent_type_id,
                    ja.url,
                    ja.secret_key,
                    ja.check_period,
                    jat.description AS agent_description,
                    jat.endpoint AS agent_endpoint
                FROM
                    jilo_agents ja
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                WHERE
                    platform_id = :platform_id';

        if ($agent_id !== '') {
            $sql .= ' AND ja.id = :agent_id';
        }

        $query = $this->db->prepare($sql);

        $query->bindParam(':platform_id', $platform_id);
        if ($agent_id !== '') {
            $query->bindParam(':agent_id', $agent_id);
        }

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieves details of a specified agent by its agent ID.
     *
     * @param int $agent_id The agent ID to filter by.
     *
     * @return array The agent details.
     */
    public function getAgentIDDetails($agent_id) {
        $sql = 'SELECT
                    ja.id,
                    ja.platform_id,
                    ja.agent_type_id,
                    ja.url,
                    ja.secret_key,
                    ja.check_period,
                    jat.description AS agent_description,
                    jat.endpoint AS agent_endpoint
                FROM
                    jilo_agents ja
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                WHERE
                    ja.id = :agent_id';

        $query = $this->db->prepare($sql);
        $query->bindParam(':agent_id', $agent_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieves all agent types.
     *
     * @return array List of all agent types.
     */
    public function getAgentTypes() {
        $sql = 'SELECT *
                    FROM jilo_agent_types
                    ORDER BY id';
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Retrieves agent types already configured for a specific platform.
     *
     * @param int $platform_id The platform ID to filter agents by.
     *
     * @return array List of agent types configured for the platform.
     */
    public function getPlatformAgentTypes($platform_id) {
        $sql = 'SELECT
                    id,
                    agent_type_id
                FROM
                    jilo_agents
                WHERE
                    platform_id = :platform_id';
        $query = $this->db->prepare($sql);
        $query->bindParam(':platform_id', $platform_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Adds a new agent to the platform.
     *
     * @param int $platform_id The platform ID where the agent is to be added.
     * @param array $newAgent The new agent details to add.
     *
     * @return bool|string Returns true on success or an error message on failure.
     */
    public function addAgent($platform_id, $newAgent) {
        try {
            $sql = 'INSERT INTO jilo_agents
                    (platform_id, agent_type_id, url, secret_key, check_period)
                    VALUES
                    (:platform_id, :agent_type_id, :url, :secret_key, :check_period)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':platform_id'		=> $platform_id,
                ':agent_type_id'	=> $newAgent['type_id'],
                ':url'			=> $newAgent['url'],
                ':secret_key'		=> $newAgent['secret_key'],
                ':check_period'     => $newAgent['check_period'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Edits an existing agent's details.
     *
     * @param int $platform_id The platform ID where the agent exists.
     * @param array $updatedAgent The updated agent details.
     *
     * @return bool|string Returns true on success or an error message on failure.
     */
    public function editAgent($platform_id, $updatedAgent) {
        try {
            $sql = 'UPDATE jilo_agents SET
                        agent_type_id = :agent_type_id,
                        url = :url,
                        secret_key = :secret_key,
                        check_period = :check_period
                    WHERE
                        id = :agent_id
                    AND
                        platform_id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':agent_type_id'	=> $updatedAgent['agent_type_id'],
                ':url'			=> $updatedAgent['url'],
                ':secret_key'		=> $updatedAgent['secret_key'],
                ':check_period' => $updatedAgent['check_period'],
                ':agent_id'		=> $updatedAgent['id'],
                ':platform_id'		=> $platform_id,
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Deletes an agent from the platform.
     *
     * @param int $agent_id The agent ID to delete.
     *
     * @return bool|string Returns true on success or an error message on failure.
     */
    public function deleteAgent($agent_id) {
        try {
            $sql = 'DELETE FROM jilo_agents
                    WHERE
                    id = :agent_id';

            $query = $this->db->prepare($sql);
            $query->bindParam(':agent_id', $agent_id);

            $query->execute();
            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Checks if the agent cache is still valid.
     *
     * @param int $agent_id The agent ID to check.
     *
     * @return bool Returns true if cache is valid, false otherwise.
     */
    public function checkAgentCache($agent_id) {
        $agent_cache_name = 'agent' . $agent_id . '_cache';
        $agent_cache_time = 'agent' . $agent_id . '_time';
        return isset($_SESSION[$agent_cache_name]) && isset($_SESSION[$agent_cache_time]) && (time() - $_SESSION[$agent_cache_time] < 600);
    }


    /**
     * Base64 URL encodes the input data. Used for encoding JWT tokens
     *
     * @param string $data The data to encode.
     *
     * @return string The base64 URL encoded string.
     */
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }


    /**
     * Generates a JWT token for a Jilo agent.
     *
     * @param array $payload The payload data to include in the token.
     * @param string $secret_key The secret key used to sign the token.
     *
     * @return string The generated JWT token.
     */
    public function generateAgentToken($payload, $secret_key) {

        // header
        $header = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        $base64Url_header = $this->base64UrlEncode($header);

        // payload
        $payload = json_encode($payload);
        $base64Url_payload = $this->base64UrlEncode($payload);

        // signature
        $signature = hash_hmac('sha256', $base64Url_header . "." . $base64Url_payload, $secret_key, true);
        $base64Url_signature = $this->base64UrlEncode($signature);

        // build the JWT
        $jwt = $base64Url_header . "." . $base64Url_payload . "." . $base64Url_signature;

        return $jwt;
    }


    /**
     * Fetches data from a Jilo agent's API, optionally forcing a refresh of the cache.
     *
     * @param int $agent_id The agent ID to fetch data for.
     * @param bool $force Whether to force-refresh the cache (default: false).
     *
     * @return string The API response, or an error message in JSON format.
     */
    public function fetchAgent($agent_id, $force = false) {

        // we need agent details for URL and JWT token
        $agentDetails = $this->getAgentIDDetails($agent_id);

        // Safe exit in case the agent is not found
        if (empty($agentDetails)) {
            return json_encode(['error' => 'Agent not found']);
        }

        $agent = $agentDetails[0];
        $agent_cache_name = 'agent' . $agent_id . '_cache';
        $agent_cache_time = 'agent' . $agent_id . '_time';

        // check if the cache is still valid, unless force-refresh is requested
        if (!$force && $this->checkAgentCache($agent_id)) {
            return $_SESSION[$agent_cache_name];
        }

        // generate the JWT token
        $payload = [
            'agent_id'      => $agent_id,
            'timestamp'     => time()
        ];
        $jwt = $this->generateAgentToken($payload, $agent['secret_key']);

        // Make the API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $agent['url'] . $agent['agent_endpoint']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout 10 seconds
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $jwt,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // curl error
        if ($curl_errno) {
            return json_encode(['error' => 'curl error: ' . $curl_error]);
        }

        // response is not 200 OK
        if ($http_code !== 200) {
            return json_encode(['error' => 'HTTP error: ' . $http_code]);
        }

        // other custom error(s)
        if (strpos($response, 'Auth header not received') !== false) {
            return json_encode(['error' => 'Auth header not received']);
        }

        // Cache the result and the timestamp if the response is successful
        // We decode it so that it's pure JSON and not escaped
        $_SESSION[$agent_cache_name] = json_decode($response, true);
        $_SESSION[$agent_cache_time] = time();

        return $response;
    }


    /**
     * Clears the cached data for a specific agent.
     *
     * @param int $agent_id The agent ID for which the cache should be cleared.
     */
    public function clearAgentCache($agent_id) {
        $_SESSION["agent{$agent_id}_cache"] = '';
        $_SESSION["agent{$agent_id}_cache_time"] = '';
    }


    /**
     * Retrieves the latest stored data for a specific platform, agent type, and metric type.
     *
     * @param int $platform_id The platform ID.
     * @param string $agent_type The agent type.
     * @param string $metric_type The metric type to filter by.
     *
     * @return mixed The latest stored data.
     */
    public function getLatestData($platform_id, $agent_type, $metric_type) {
        // TODO
        // retrieves data already stored in db from another function (or the jilo-server to-be)
    }

}

?>
