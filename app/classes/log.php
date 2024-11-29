<?php

/**
 * class Log
 *
 * Handles logging events into a database and reading log entries.
 */
class Log {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;

    /**
     * Logs constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Insert a log event into the database.
     *
     * @param int    $user_id The ID of the user associated with the log event.
     * @param string $message The log message to insert.
     * @param string $scope   The scope of the log event (e.g., 'user', 'system'). Default is 'user'.
     *
     * @return bool|string True on success, or an error message on failure.
     */
    public function insertLog($user_id, $message, $scope='user') {
        try {
            $sql = 'INSERT INTO logs
                        (user_id, scope, message)
                    VALUES
                        (:user_id, :scope, :message)';

            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'		=> $user_id,
                ':scope'		=> $scope,
                ':message'		=> $message,
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Retrieve log entries from the database.
     *
     * @param int    $user_id        The ID of the user whose logs are being retrieved.
     * @param string $scope          The scope of the logs ('user' or 'system').
     * @param int    $offset         The offset for pagination. Default is 0.
     * @param int    $items_per_page The number of log entries to retrieve per page. Default is no limit.
     *
     * @return array An array of log entries.
     */
    public function readLog($user_id, $scope, $offset=0, $items_per_page='') {
        if ($scope === 'user') {
            $sql = 'SELECT * FROM logs WHERE user_id = :user_id ORDER BY time DESC';
            if ($items_per_page) {
                $items_per_page = (int)$items_per_page;
                $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
            }

            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'		=> $user_id,
            ]);
        }
        if ($scope === 'system') {
            $sql = 'SELECT * FROM logs ORDER BY time DESC';
            if ($items_per_page) {
                $items_per_page = (int)$items_per_page;
                $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
            }

            $query = $this->db->prepare($sql);
            $query->execute();
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
