<?php

class Agent {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // get details of a specified agent ID (or all) in a specified platform ID
    public function getAgentDetails($platform_id, $agent_id = '') {
        $sql = 'SELECT
                    ja.id,
                    ja.platform_id,
                    ja.agent_type_id,
                    ja.url,
                    ja.secret_key,
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

    // get agent types
    public function getAgentTypes() {
        $sql = 'SELECT *
                    FROM jilo_agent_types
                    ORDER BY id';
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // add new agent
    public function addAgent($platform_id, $newAgent) {
        try {
            $sql = 'INSERT INTO jilo_agents
                    (platform_id, agent_type_id, url, secret_key)
                    VALUES
                    (:platform_id, :agent_type_id, :url, :secret_key)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':platform_id'		=> $platform_id,
                ':agent_type_id'	=> $newAgent['type_id'],
                ':url'			=> $newAgent['url'],
                ':secret_key'		=> $newAgent['secret_key'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // edit an existing agent
    public function editAgent($platform_id, $updatedAgent) {
        try {
            $sql = 'UPDATE jilo_agents SET
                        agent_type_id = :agent_type_id,
                        url = :url,
                        secret_key = :secret_key
                    WHERE
                        id = :agent_id
                    AND
                        platform_id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':agent_type_id'	=> $updatedAgent['agent_type_id'],
                ':url'			=> $updatedAgent['url'],
                ':secret_key'		=> $updatedAgent['secret_key'],
                ':agent_id'		=> $updatedAgent['id'],
                ':platform_id'		=> $platform_id,
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // delete an agent
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


    // check for agent cache
    public function checkAgentCache($agent_id) {
        $agent_cache_name = 'agent' . $agent_id . '_cache';
        $agent_cache_time = 'agent' . $agent_id . '_time';
        return isset($_SESSION[$agent_cache_name]) && isset($_SESSION[$agent_cache_time]) && (time() - $_SESSION[$agent_cache_time] < 600);
    }


    // method for base64 URL encoding for JWT tokens
    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }


    // generate a JWT token for jilo agent
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


    // fetch result from jilo agent API
    public function fetchAgent($agent_id, $force = false) {

        // we need agent details for URL and JWT token
        $agent = $this->getAgentDetails($agent_id);
        $agent_cache_name = 'agent' . $agent_id . '_cache';
        $agent_cache_time = 'agent' . $agent_id . '_time';

        // check if the cache is still valid, unless force-refresh is requested
        if (!$force && this->checkAgentCache($agent_id)) {
            return $_SESSION[$agent_cache_name];
        }

        // Make the API request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $agent[0]['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout 10 seconds

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        $curl_errno = curl_errno($ch);

        curl_close($ch);

        // general curl error
        if ($curl_error) {
            return json_encode(['error' => 'curl error: ' . $curl_error]);
        }

        // other custom error(s)
        if (strpos($response, 'Auth header not received') !== false) {
            return json_encode(['error' => 'Auth header not received']);
        }

        // Cache the result and the timestamp if the response is successful
        $_SESSION[$agent_cache_name] = $response;
        $_SESSION[$agent_cache_time] = time();

        return $response;
    }


    // clear agent cache
    public function clearAgentCache($agent_id) {
        $_SESSION["agent{$agent_id}_cache"] = '';
        $_SESSION["agent{$agent_id}_cache_time"] = '';
    }


    // get latest stored jilo agents data
    public function getLatestData($platform_id, $agent_type, $metric_type) {
        // retrieves data already stored in db from another function (or the jilo-server to-be)
    }

}

?>
