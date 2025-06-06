<?php

/**
 * class Platform
 *
 * Handles platform management in the database, including retrieving, adding, editing, and deleting platforms.
 */
class Platform {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Platform constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }


    /**
     * Retrieve details of a specific platform or all platforms.
     *
     * @param string $platform_id The ID of the platform to retrieve details for (optional).
     *
     * @return array An associative array containing platform details.
     */
    public function getPlatformDetails($platform_id = '') {
        $sql = 'SELECT * FROM platform';
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


    /**
     * Add a new platform to the database.
     *
     * @param array $newPlatform An associative array containing the details of the new platform:
     *                           - `name` (string): The name of the platform.
     *                           - `jitsi_url` (string): The URL for the Jitsi integration.
     *                           - `jilo_database` (string): The database name for Jilo integration.
     *
     * @return bool|string True if the platform was added successfully, or an error message on failure.
     */
    public function addPlatform($newPlatform) {
        try {
            $sql = 'INSERT INTO platform
                    (name, jitsi_url, jilo_database)
                    VALUES
                    (:name, :jitsi_url, :jilo_database)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':name'			=> $newPlatform['name'],
                ':jitsi_url'		=> $newPlatform['jitsi_url'],
                ':jilo_database'	=> $newPlatform['jilo_database'],
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Edit an existing platform in the database.
     *
     * @param int $platform_id The ID of the platform to update.
     * @param array $updatedPlatform An associative array containing the updated platform details:
     *                               - `name` (string): The updated name of the platform.
     *                               - `jitsi_url` (string): The updated Jitsi URL.
     *                               - `jilo_database` (string): The updated Jilo database name.
     *
     * @return bool|string True if the platform was updated successfully, or an error message on failure.
     */
    public function editPlatform($platform_id, $updatedPlatform) {
        try {
            $sql = 'UPDATE platform SET
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

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Delete a platform from the database.
     *
     * @param int $platform_id The ID of the platform to delete.
     *
     * @return bool|string True if the platform was deleted successfully, or an error message on failure.
     */
    public function deletePlatform($platform_id) {
        try {
            $this->db->beginTransaction();

            // First, get all hosts in this platform
            $sql = 'SELECT id FROM host WHERE platform_id = :platform_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);
            $query->execute();
            $hosts = $query->fetchAll(PDO::FETCH_ASSOC);

            // Delete all agents for each host
            foreach ($hosts as $host) {
                $sql = 'DELETE FROM jilo_agent WHERE host_id = :host_id';
                $query = $this->db->prepare($sql);
                $query->bindParam(':host_id', $host['id']);
                $query->execute();
            }

            // Delete all hosts in this platform
            $sql = 'DELETE FROM host WHERE platform_id = :platform_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);
            $query->execute();

            // Finally, delete the platform
            $sql = 'DELETE FROM platform WHERE id = :platform_id';
            $query = $this->db->prepare($sql);
            $query->bindParam(':platform_id', $platform_id);
            $query->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

}
