<?php

/**
 * class Host
 *
 * Manages the hosts in the database, providing methods to retrieve, add, edit, and delete host entries.
 */
class Host {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Host constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    /**
     * Get details of a specified host ID (or all hosts) in a specified platform ID.
     *
     * @param string $platform_id The platform ID to filter the hosts by (optional).
     * @param string $host_id The host ID to filter the details (optional).
     *
     * @return array The details of the host(s) in the form of an associative array.
     */
    public function getHostDetails($platform_id = '', $host_id = '') {
        $sql = 'SELECT
                    id,
                    address,
                    platform_id,
                    name
                FROM
                    host';

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


    /**
     * Add a new host to the database.
     *
     * @param array $newHost An associative array containing the details of the host to be added.
     *
     * @return bool True if the host was added successfully, otherwise false.
     */
    public function addHost($newHost) {
        try {
            $sql = 'INSERT INTO host
                    (address, platform_id, name)
                    VALUES
                    (:address, :platform_id, :name)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':address'          => $newHost['address'],
                ':platform_id'		=> $newHost['platform_id'],
                ':name'             => $newHost['name'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Edit an existing host in the database.
     *
     * @param string $platform_id The platform ID to which the host belongs.
     * @param array $updatedHost An associative array containing the updated details of the host.
     *
     * @return bool|string True if the host was updated successfully, otherwise error message.
     */
    public function editHost($platform_id, $updatedHost) {
        try {
            $sql = 'UPDATE host SET
                        address = :address,
                        name = :name
                    WHERE
                        id = :id AND platform_id = :platform_id';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':id'           => $updatedHost['id'],
                ':platform_id'  => $platform_id,
                ':address'      => $updatedHost['address'],
                ':name'         => $updatedHost['name']
            ]);

            if ($query->rowCount() === 0) {
                return "No host found with ID {$updatedHost['id']} in platform $platform_id";
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Delete a host from the database.
     *
     * @param int $host_id The ID of the host to be deleted.
     *
     * @return bool True if the host was deleted successfully, otherwise false.
     */
    public function deleteHost($host_id) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // First delete all agents associated with this host
            $sql = 'DELETE FROM jilo_agent WHERE host_id = :host_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':host_id', $host_id);
            $query->execute();

            // Then delete the host
            $sql = 'DELETE FROM host WHERE id = :host_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':host_id', $host_id);
            $query->execute();

            // Commit transaction
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

}
