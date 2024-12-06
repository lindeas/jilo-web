<?php

class RateLimiter {
    private $db;
    private $maxAttempts = 5;        // Maximum login attempts
    private $decayMinutes = 15;      // Time window in minutes
    private $tableName = 'login_attempts';

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(255) NOT NULL,
            attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_username (ip_address, username)
        )";

        $this->db->exec($sql);
    }

    public function attempt($username, $ipAddress) {
        // Clean old attempts
        $this->clearOldAttempts();

        // Record this attempt
        $sql = "INSERT INTO {$this->tableName} (ip_address, username) VALUES (:ip, :username)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'		=> $ipAddress,
            ':username'		=> $username
        ]);

        // Check if too many attempts
        return !$this->tooManyAttempts($username, $ipAddress);
    }

    public function tooManyAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts 
                FROM {$this->tableName} 
                WHERE ip_address = :ip 
                AND username = :username 
                AND attempted_at > datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'		=> $ipAddress,
            ':username'		=> $username,
            ':minutes'		=> $this->decayMinutes
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['attempts'] >= $this->maxAttempts;
    }

    public function clearOldAttempts() {
        $sql = "DELETE FROM {$this->tableName} 
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':minutes'		=> $this->decayMinutes
        ]);
    }

    public function getRemainingAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts 
                FROM {$this->tableName} 
                WHERE ip_address = :ip 
                AND username = :username 
                AND attempted_at > datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'		=> $ipAddress,
            ':username'		=> $username,
            ':minutes'		=> $this->decayMinutes
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return max(0, $this->maxAttempts - $result['attempts']);
    }

    public function getDecayMinutes() {
        return $this->decayMinutes;
    }
}
