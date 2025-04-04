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
                ':user_id'      => $user_id,
                ':scope'        => $scope,
                ':message'      => $message,
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
     * @param array  $filters        Optional array of filters (from_time, until_time, message, id)
     *
     * @return array An array of log entries.
     */
    public function readLog($user_id, $scope, $offset=0, $items_per_page='', $filters=[]) {
        $params = [];
        $where_clauses = [];

        // Base query with user join
        $base_sql = 'SELECT l.*, u.username 
                    FROM logs l 
                    LEFT JOIN users u ON l.user_id = u.id';

        // Add scope condition
        if ($scope === 'user') {
            $where_clauses[] = 'l.user_id = :user_id';
            $params[':user_id'] = $user_id;
        }

        // Add time range filters if specified
        if (!empty($filters['from_time'])) {
            $where_clauses[] = 'l.time >= :from_time';
            $params[':from_time'] = $filters['from_time'] . ' 00:00:00';
        }
        if (!empty($filters['until_time'])) {
            $where_clauses[] = 'l.time <= :until_time';
            $params[':until_time'] = $filters['until_time'] . ' 23:59:59';
        }

        // Add message search if specified
        if (!empty($filters['message'])) {
            $where_clauses[] = 'l.message LIKE :message';
            $params[':message'] = '%' . $filters['message'] . '%';
        }

        // Add user ID search if specified
        if (!empty($filters['id'])) {
            $where_clauses[] = 'l.user_id = :search_user_id';
            $params[':search_user_id'] = $filters['id'];
        }

        // Combine WHERE clauses
        $sql = $base_sql;
        if (!empty($where_clauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
        }

        // Add ordering
        $sql .= ' ORDER BY l.time DESC';

        // Add pagination
        if ($items_per_page) {
            $items_per_page = (int)$items_per_page;
            $sql .= ' LIMIT ' . $offset . ',' . $items_per_page;
        }

        $query = $this->db->prepare($sql);
        $query->execute($params);

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
