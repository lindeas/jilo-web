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
     * Retrieves details of agents for a specified host.
     *
     * @param int $host_id The host ID to filter agents by.
     * @param int $agent_id Optional agent ID to filter by.
     *
     * @return array The list of agent details.
     */
    public function getAgentDetails($host_id, $agent_id = '') {
        $sql = 'SELECT
                    ja.id,
                    ja.host_id,
                    ja.agent_type_id,
                    ja.url,
                    ja.secret_key,
                    ja.check_period,
                    jat.description AS agent_description,
                    jat.endpoint AS agent_endpoint,
                    h.platform_id
                FROM
                    jilo_agents ja
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                JOIN
                    hosts h ON ja.host_id = h.id
                WHERE
                    ja.host_id = :host_id';

        if ($agent_id !== '') {
            $sql .= ' AND ja.id = :agent_id';
        }

        $query = $this->db->prepare($sql);

        $query->bindParam(':host_id', $host_id);
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
                    ja.host_id,
                    ja.agent_type_id,
                    ja.url,
                    ja.secret_key,
                    ja.check_period,
                    jat.description AS agent_description,
                    jat.endpoint AS agent_endpoint,
                    h.platform_id
                FROM
                    jilo_agents ja
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                JOIN
                    hosts h ON ja.host_id = h.id
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
     * Retrieves agent types already configured for a specific host.
     *
     * @param int $host_id The host ID to filter agents by.
     *
     * @return array List of agent types configured for the host.
     */
    public function getHostAgentTypes($host_id) {
        $sql = 'SELECT
                    id,
                    agent_type_id
                FROM
                    jilo_agents
                WHERE
                    host_id = :host_id';
        $query = $this->db->prepare($sql);
        $query->bindParam(':host_id', $host_id);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Add a new agent to the database.
     *
     * @param int $host_id The host ID to add the agent to.
     * @param array $newAgent An associative array containing the details of the agent to be added.
     *
     * @return bool|string True if the agent was added successfully, otherwise error message.
     */
    public function addAgent($host_id, $newAgent) {
        try {
            $sql = 'INSERT INTO jilo_agents
                    (host_id, agent_type_id, url, secret_key, check_period)
                    VALUES
                    (:host_id, :agent_type_id, :url, :secret_key, :check_period)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':host_id'       => $host_id,
                ':agent_type_id' => $newAgent['type_id'],
                ':url'          => $newAgent['url'],
                ':secret_key'   => $newAgent['secret_key'],
                ':check_period' => $newAgent['check_period'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Edit an existing agent in the database.
     *
     * @param int $agent_id The ID of the agent to edit.
     * @param array $updatedAgent An associative array containing the updated details of the agent.
     *
     * @return bool|string True if the agent was updated successfully, otherwise error message.
     */
    public function editAgent($agent_id, $updatedAgent) {
        try {
            $sql = 'UPDATE jilo_agents
                    SET
                        url = :url,
                        secret_key = :secret_key,
                        check_period = :check_period
                    WHERE
                        id = :agent_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':agent_id'     => $agent_id,
                ':url'          => $updatedAgent['url'],
                ':secret_key'   => $updatedAgent['secret_key'],
                ':check_period' => $updatedAgent['check_period'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Deletes an agent from the database.
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
     * Gets a value from a nested array using dot notation
     * e.g. "bridge_selector.bridge_count" will get $array['bridge_selector']['bridge_count']
     *
     * @param array $array The array to search in
     * @param string $path The path in dot notation
     * @return mixed|null The value if found, null otherwise
     */
    private function getNestedValue($array, $path) {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Retrieves the latest stored data for a specific host, agent type, and metric type.
     *
     * @param int $host_id The host ID.
     * @param string $agent_type The agent type.
     * @param string $metric_type The metric type to filter by.
     *
     * @return mixed The latest stored data.
     */
    public function getLatestData($host_id, $agent_type, $metric_type) {
        $sql = 'SELECT
                    jac.timestamp,
                    jac.response_content,
                    jac.agent_id,
                    jat.description
                FROM
                    jilo_agent_checks jac
                JOIN
                    jilo_agents ja ON jac.agent_id = ja.id
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                JOIN
                    hosts h ON ja.host_id = h.id
                WHERE
                    h.id = :host_id
                    AND jat.description = :agent_type
                    AND jac.status_code = 200
                ORDER BY
                    jac.timestamp DESC
                LIMIT 1';

        $query = $this->db->prepare($sql);
        $query->execute([
            ':host_id' => $host_id,
            ':agent_type' => $agent_type
        ]);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Parse the JSON response content
            $data = json_decode($result['response_content'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            // Extract the specific metric value from the response based on agent type
            if ($agent_type === 'jvb') {
                $value = $this->getNestedValue($data['jvb_api_data'], $metric_type);
                if ($value !== null) {
                    return [
                        'value' => $value,
                        'timestamp' => $result['timestamp']
                    ];
                }

            } elseif ($agent_type === 'jicofo') {
                $value = $this->getNestedValue($data['jicofo_api_data'], $metric_type);
                if ($value !== null) {
                    return [
                        'value' => $value,
                        'timestamp' => $result['timestamp']
                    ];
                }

            } elseif ($agent_type === 'jigasi') {
                $value = $this->getNestedValue($data['jigasi_api_data'], $metric_type);
                if ($value !== null) {
                    return [
                        'value' => $value,
                        'timestamp' => $result['timestamp']
                    ];
                }

            } elseif ($agent_type === 'prosody') {
                $value = $this->getNestedValue($data['prosody_api_data'], $metric_type);
                if ($value !== null) {
                    return [
                        'value' => $value,
                        'timestamp' => $result['timestamp']
                    ];
                }

            } elseif ($agent_type === 'nginx') {
                $value = $this->getNestedValue($data['nginx_api_data'], $metric_type);
                if ($value !== null) {
                    return [
                        'value' => $value,
                        'timestamp' => $result['timestamp']
                    ];
                }
            }


        }

        return null;
    }

    /**
     * Gets historical data for a specific metric from agent checks
     *
     * @param int $host_id The host ID
     * @param string $agent_type The type of agent (e.g., 'jvb', 'jicofo')
     * @param string $metric_type The type of metric to retrieve
     * @param string $from_time Start time in Y-m-d format
     * @param string $until_time End time in Y-m-d format
     * @return array Array with the dataset from agent checks
     */
    public function getHistoricalData($host_id, $agent_type, $metric_type, $from_time, $until_time) {
        // Get data from agent checks
        $sql = 'SELECT
                    DATE(jac.timestamp) as date,
                    jac.response_content,
                    COUNT(*) as checks_count
                FROM
                    jilo_agent_checks jac
                JOIN
                    jilo_agents ja ON jac.agent_id = ja.id
                JOIN
                    jilo_agent_types jat ON ja.agent_type_id = jat.id
                JOIN
                    hosts h ON ja.host_id = h.id
                WHERE
                    h.id = :host_id
                    AND jat.description = :agent_type
                    AND jac.status_code = 200
                    AND DATE(jac.timestamp) BETWEEN :from_time AND :until_time
                GROUP BY
                    DATE(jac.timestamp)
                ORDER BY
                    DATE(jac.timestamp)';

        $query = $this->db->prepare($sql);
        $query->execute([
            ':host_id' => $host_id,
            ':agent_type' => $agent_type,
            ':from_time' => $from_time,
            ':until_time' => $until_time
        ]);

        $results = $query->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $json_data = json_decode($row['response_content'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $api_data = [];
                if ($agent_type === 'jvb') {
                    $api_data = $json_data['jvb_api_data'] ?? [];
                } elseif ($agent_type === 'jicofo') {
                    $api_data = $json_data['jicofo_api_data'] ?? [];
                } elseif ($agent_type === 'jigasi') {
                    $api_data = $json_data['jigasi_api_data'] ?? [];
                } elseif ($agent_type === 'prosody') {
                    $api_data = $json_data['prosody_api_data'] ?? [];
                } elseif ($agent_type === 'nginx') {
                    $api_data = $json_data['nginx_api_data'] ?? [];
                }

                $value = $this->getNestedValue($api_data, $metric_type);
                if ($value !== null) {
                    $data[] = [
                        'date' => $row['date'],
                        'value' => $value
                    ];
                }
            }
        }

        return $data;
    }
}

?>
