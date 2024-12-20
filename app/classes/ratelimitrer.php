<?php

class RateLimiter {
    private $db;
    private $log;
    private $maxAttempts = 5;        // Maximum login attempts
    private $decayMinutes = 15;      // Time window in minutes
    private $ratelimitTable = 'login_attempts';
    private $whitelistTable = 'ip_whitelist';

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->log = new Log($database);
        $this->createTablesIfNotExists();
    }

    // Database preparation
    private function createTablesIfNotExists() {
        // Login attempts table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->ratelimitTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(255) NOT NULL,
            attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ip_username (ip_address, username)
        )";
        $this->db->exec($sql);

        // IP whitelist table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->whitelistTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address VARCHAR(45) NOT NULL,
            is_network BOOLEAN DEFAULT FALSE,
            description VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_by VARCHAR(255),
            UNIQUE KEY unique_ip (ip_address)
        )";
        $this->db->exec($sql);

        // Default IPs to whitelist (local interface and private networks IPs)
        $defaultIps = [
            ['127.0.0.1', false, 'localhost IPv4'],
            ['::1', false, 'localhost IPv6'],
            ['10.0.0.0/8', true, 'Private network (Class A)'],
            ['172.16.0.0/12', true, 'Private network (Class B)'],
            ['192.168.0.0/16', true, 'Private network (Class C)']
        ];

        // Insert default whitelisted IPs if they don't exist
        $stmt = $this->db->prepare("INSERT IGNORE INTO {$this->whitelistTable} 
            (ip_address, is_network, description, created_by) 
            VALUES (?, ?, ?, 'system')");
        foreach ($defaultIps as $ip) {
            $stmt->execute([$ip[0], $ip[1], $ip[2]]);
        }
    }

    // Check if IP is whitelisted
    private function isIpWhitelisted($ip) {
        // Check exact IP match and CIDR ranges
         $stmt = $this->db->prepare("SELECT ip_address, is_network FROM {$this->whitelistTable}");
         $stmt->execute();

         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
             if ($row['is_network']) {
                 if ($this->ipInRange($ip, $row['ip_address'])) {
                     return true;
                 }
             } else {
                 if ($ip === $row['ip_address']) {
                     return true;
                 }
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

    // Add to whitelist
    public function addToWhitelist($ip, $isNetwork = false, $description = '', $createdBy = 'system') {
        $stmt = $this->db->prepare("INSERT INTO {$this->whitelistTable}
            (ip_address, is_network, description, created_by)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            is_network = VALUES(is_network),
            description = VALUES(description),
            created_by = VALUES(created_by)");

        return $stmt->execute([$ip, $isNetwork, $description, $createdBy]);
    }

    // Remove from whitelist
    public function removeFromWhitelist($ip) {
        $stmt = $this->db->prepare("DELETE FROM {$this->whitelistTable} WHERE ip_address = ?");

        return $stmt->execute([$ip]);
    }

    public function getWhitelistedIps() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->whitelistTable} ORDER BY created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function attempt($username, $ipAddress) {
        // Skip rate limiting for whitelisted IPs
        if ($this->isIpWhitelisted($ipAddress)) {
            return true;
        }

        // Clean old attempts
        $this->clearOldAttempts();

        // Record this attempt
        $sql = "INSERT INTO {$this->ratelimitTable} (ip_address, username) VALUES (:ip, :username)";
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
                FROM {$this->ratelimitTable}
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
        $sql = "DELETE FROM {$this->ratelimitTable}
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':minutes'		=> $this->decayMinutes
        ]);
    }

    public function getRemainingAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts
                FROM {$this->ratelimitTable}
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
