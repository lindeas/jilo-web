<?php

class Host {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // get details of a specified host ID (or all) in a specified platform ID
    public function getHostDetails($platform_id = '', $host_id = '') {
        $sql = 'SELECT
                    id,
                    address,
                    port,
                    platform_id,
                    name
                FROM
                    hosts';

        if ($platform_id !== '' && $host_id !== '') {
            $sql .= ' WHERE platform_id = :platform_id AND id = :host_id';
        } elseif ($platform_id !== '') {
            $sql .= ' WHERE platform_id = :platform_id';
        } elseif ($host_id !== '') {
            $sql .= ' WHERE id = :host_id';
        }

        $query = $this->db->prepare($sql);

        if ($platform_id !== '') {
            $query->bindParam(':platform_id', $platform_id);
        }
        if ($host_id !== '') {
            $query->bindParam(':host_id', $host_id);
        }

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }


    // add new host
    public function addHost($newHost) {
        try {
            $sql = 'INSERT INTO hosts
                    (address, port, platform_id, name)
                    VALUES
                    (:address, :port, :platform_id, :name)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':address'          => $newHost['address'],
                ':port'             => $newHost['port'],
                ':platform_id'		=> $newHost['platform_id'],
                ':name'             => $newHost['name'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // edit an existing host
    public function editHost($platform_id, $updatedHost) {
        try {
            $sql = 'UPDATE hosts SET
                        address = :address,
                        port = :port,
                        name = :name,
                    WHERE
                        id = :id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':address'  => $updatedHost['address'],
                ':port'     => $updatedHost['port'],
                ':name'     => $updatedHost['name'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    // delete a host
    public function deleteHost($host_id) {
        try {
            $sql = 'DELETE FROM hosts
                    WHERE
                    id = :host_id';

            $query = $this->db->prepare($sql);
            $query->bindParam(':host_id', $host_id);

            $query->execute();
            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

}

?>
