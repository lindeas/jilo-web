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
                    (platform_id, type_id, url, secret_key)
                    VALUES
                    (:platform_id, :type_id, :url, :secret_key)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':platform_id'		=> $platform_id,
                ':type_id'		=> $newAgent['type_id'],
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
                        type_id = :type_id,
                        url = :url,
                        secret_key = :secret_key
                    WHERE
                        id = :agent_id
                    AND
                        platform_id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':type_id'		=> $updatedAgent['type_id'],
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


}

?>
