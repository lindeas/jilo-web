<?php

class Agent {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // get details of a specified agent ID (or all) in a specified platform ID
    public function getAgentDetails($platform_id, $agent_id = '') {
        $sql = 'SELECT * FROM jilo_agents
                WHERE
                    platform_id = :platform_id';
        if ($agent_id !== '') {
            $sql .= ' AND id = :agent_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':platform_id'		=> $platform_id,
                ':agent_id'		=> $agent_id,
            ]);
        } else {
            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);
        }

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
        $agent_cache_name = $agent_id . '_cache';
        $agent_cache_time = $agent_id . '_time';
        return isset($_SESSION[$agent_cache_name]) && isset($_SESSION[$agent_cache_time]) && (time() - $_SESSION[$agent_cache_time] < 600);
    }

    // fetch result from jilo agent API
    public function fetchAgent($agent_id, $force = false) {

        // we need agent details for URL and JWT token
        $agent = $this->getAgentDetails($agent_id);
        $agent_cache_name = $agent_id . '_cache';
        $agent_cache_time = $agent_id . '_time';

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
        $curl_error = curl_error($ch); // curl error for debugging

        curl_close($ch);

        // Cache the result and the timestamp if the response is successful
        if ($response !== false) {
            $_SESSION[$agent_cache_name] = $response;
            $_SESSION[$agent_cache_time] = time();
        } else {
            $response = "Error: " . $curl_error;
        }
        return $response;
    }


}

?>
