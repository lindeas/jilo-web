<?php

class RateLimiter {
    private $db;
    private $maxAttempts = 5;        // Maximum login attempts
    private $decayMinutes = 15;      // Time window in minutes
    private $tableName = 'login_attempts';
    private $whitelistedIps = [];       // Whitelisted IPs
    private $whitelistedNetworks = [];  // Whitelisted CIDR ranges

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->createTableIfNotExists();
        $this->loadWhitelist();
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

    private function loadWhitelist() {
        // Load from database or config
        $this->whitelistedIps = [
            '127.0.0.1',        // localhost
            '::1'               // localhost IPv6
        ];

        $this->whitelistedNetworks = [
            '10.0.0.0/8',      // Private network
            '172.16.0.0/12',   // Private network
            '192.168.0.0/16'   // Private network
        ];
    }

    private function isIpWhitelisted($ip) {
        // Check exact IP match
        if (in_array($ip, $this->whitelistedIps)) {
            return true;
        }

        // Check CIDR ranges
        foreach ($this->whitelistedNetworks as $network) {
            if ($this->ipInRange($ip, $network)) {
                return true;
            }
        }

        return false;
    }

    private function ipInRange($ip, $cidr) {
        list($subnet, $bits) = explode('/', $cidr);

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;

        return ($ip & $mask) == $subnet;
    }

    public function addToWhitelist($ip, $isNetwork = false) {
        if ($isNetwork) {
            if (!in_array($ip, $this->whitelistedNetworks)) {
                $this->whitelistedNetworks[] = $ip;
            }
        } else {
            if (!in_array($ip, $this->whitelistedIps)) {
                $this->whitelistedIps[] = $ip;
            }
        }
    }

    public function removeFromWhitelist($ip) {
        $indexIp = array_search($ip, $this->whitelistedIps);
        if ($indexIp !== false) {
            unset($this->whitelistedIps[$indexIp]);
        }

        $indexNetwork = array_search($ip, $this->whitelistedNetworks);
        if ($indexNetwork !== false) {
            unset($this->whitelistedNetworks[$indexNetwork]);
        }
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
