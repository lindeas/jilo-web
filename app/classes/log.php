<?php

class Log {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // insert log event
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

    // read logs
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
