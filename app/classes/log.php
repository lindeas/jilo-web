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
    public function readLog($user_id, $scope='user') {
        $sql = 'SELECT * FROM logs';
        if ($scope === 'user') {
            $sql .= ' WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'		=> $user_id,
            ]);
        }
        if ($scope === 'system') {
            $query = $this->db->prepare($sql);
            $query->execute();
        }

        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

}

?>
