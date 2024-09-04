<?php

class Platform {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // get details of a specified platform ID (or all)
    public function getPlatformDetails($platform_id = '') {
        $sql = 'SELECT * FROM platforms';
        if ($platform_id !== '') {
            $sql .= ' WHERE id = :platform_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);
        } else {
            $query = $this->db->prepare($sql);
        }

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // add new platform
    public function addPlatform($newPlatform) {
        try {
            $sql = 'INSERT INTO platforms
                    (name, jitsi_url, jilo_database)
                    VALUES
                    (:name, :jitsi_url, :jilo_database)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':name'			=> $newPlatform['name'],
                ':jitsi_url'		=> $newPlatform['jitsi_url'],
                ':jilo_database'	=> $newPlatform['jilo_database'],
            ]);

            $query->execute();
            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // edit an existing platform
    public function editPlatform($platform_id, $updatedPlatform) {
        try {
            $sql = 'UPDATE platforms SET
                        name = :name,
                        jitsi_url = :jitsi_url,
                        jilo_database = :jilo_database
                    WHERE
                        id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':name'			=> $updatedPlatform['name'],
                ':jitsi_url'		=> $updatedPlatform['jitsi_url'],
                ':jilo_database'	=> $updatedPlatform['jilo_database'],
                ':platform_id'		=> $platform_id,
            ]);

            $query->execute();
            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // delete a platform
    public function deletePlatform($platform_id) {
        try {
            $sql = 'DELETE FROM platforms
                    WHERE
                    id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);

            $query->execute();
            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


}

?>
