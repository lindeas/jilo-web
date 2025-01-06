<?php

class RateLimiter {
    public $db;
    private $log;
    public $maxAttempts = 5;           // Maximum login attempts
    public $decayMinutes = 15;         // Time window in minutes
    public $autoBlacklistThreshold = 10; // Attempts before auto-blacklist
    public $autoBlacklistDuration = 24;  // Hours to blacklist for
    public $ratelimitTable = 'login_attempts';
    public $whitelistTable = 'ip_whitelist';
    public $blacklistTable = 'ip_blacklist';

    public function __construct($database) {
        $this->db = $database->getConnection();
        $this->log = new Log($database);
        $this->createTablesIfNotExist();
    }

    // Database preparation
    private function createTablesIfNotExist() {
        // Login attempts table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->ratelimitTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL UNIQUE,
            username TEXT NOT NULL,
            attempted_at TEXT DEFAULT (DATETIME('now'))
        )";
        $this->db->exec($sql);

        // IP whitelist table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->whitelistTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL UNIQUE,
            is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
            description TEXT,
            created_at TEXT DEFAULT (DATETIME('now')),
            created_by TEXT
        )";
        $this->db->exec($sql);

        // IP blacklist table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->blacklistTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL UNIQUE,
            is_network BOOLEAN DEFAULT 0 CHECK(is_network IN (0,1)),
            reason TEXT,
            expiry_time TEXT  NULL,
            created_at TEXT DEFAULT (DATETIME('now')),
            created_by TEXT
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
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO {$this->whitelistTable}
            (ip_address, is_network, description, created_by)
            VALUES (?, ?, ?, 'system')");
        foreach ($defaultIps as $ip) {
            $stmt->execute([$ip[0], $ip[1], $ip[2]]);
        }

        // Insert known malicious networks
        $defaultBlacklist = [
            ['0.0.0.0/8', true, 'Reserved address space - RFC 1122'],
            ['100.64.0.0/10', true, 'Carrier-grade NAT space - RFC 6598'],
            ['192.0.2.0/24', true, 'TEST-NET-1 Documentation space - RFC 5737'],
            ['198.51.100.0/24', true, 'TEST-NET-2 Documentation space - RFC 5737'],
            ['203.0.113.0/24', true, 'TEST-NET-3 Documentation space - RFC 5737']
        ];

        $stmt = $this->db->prepare("INSERT OR IGNORE INTO {$this->blacklistTable}
            (ip_address, is_network, reason, created_by)
            VALUES (?, ?, ?, 'system')");

        foreach ($defaultBlacklist as $ip) {
            $stmt->execute([$ip[0], $ip[1], $ip[2]]);
        }

    }

    /**
     * Get number of recent login attempts for an IP
     */
    public function getRecentAttempts($ip) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as attempts FROM {$this->ratelimitTable} 
            WHERE ip_address = ? AND attempted_at > datetime('now', '-' || :minutes || ' minutes')");
        $stmt->execute([$ip, $this->decayMinutes]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['attempts']);
    }

    /**
     * Check if an IP is blacklisted
     */
    public function isIpBlacklisted($ip) {
        // First check if IP is explicitly blacklisted or in a blacklisted range
        $stmt = $this->db->prepare("SELECT ip_address, is_network, expiry_time FROM {$this->blacklistTable}");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Skip expired entries
            if ($row['expiry_time'] !== null && strtotime($row['expiry_time']) < time()) {
                continue;
            }

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

    /**
     * Check if an IP is whitelisted
     */
    public function isIpWhitelisted($ip) {
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
    public function addToWhitelist($ip, $isNetwork = false, $description = '', $createdBy = 'system', $userId = null) {
        try {
            // Check if IP is blacklisted first
            if ($this->isIpBlacklisted($ip)) {
                $message = "Cannot whitelist {$ip} - IP is currently blacklisted";
                if ($userId) {
                    $this->log->insertLog($userId, "IP Whitelist: {$message}", 'system');
                    Messages::flash('ERROR', 'DEFAULT', $message);
                }
                return false;
            }

            $stmt = $this->db->prepare("INSERT OR REPLACE INTO {$this->whitelistTable}
                (ip_address, is_network, description, created_by)
                VALUES (?, ?, ?, ?)");

                $result = $stmt->execute([$ip, $isNetwork, $description, $createdBy]);

                if ($result) {
                    $logMessage = sprintf(
                        'IP Whitelist: Added %s "%s" by %s. Description: %s',
                        $isNetwork ? 'network' : 'IP',
                        $ip,
                        $createdBy,
                        $description
                    );
                    $this->log->insertLog($userId ?? 0, $logMessage, 'system');
                }

            return $result;

        } catch (Exception $e) {
            if ($userId) {
                $this->log->insertLog($userId, "IP Whitelist: Failed to add {$ip}: " . $e->getMessage(), 'system');
                Messages::flash('ERROR', 'DEFAULT', "IP Whitelist: Failed to add {$ip}: " . $e->getMessage());
            }
            return false;
        }
    }

    // Remove from whitelist
    public function removeFromWhitelist($ip, $removedBy = 'system', $userId = null) {
        try {
            // Get IP details before removal for logging
            $stmt = $this->db->prepare("SELECT * FROM {$this->whitelistTable} WHERE ip_address = ?");
            $stmt->execute([$ip]);
            $ipDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Remove the IP
            $stmt = $this->db->prepare("DELETE FROM {$this->whitelistTable} WHERE ip_address = ?");

            $result = $stmt->execute([$ip]);

            if ($result && $ipDetails) {
                $logMessage = sprintf(
                    'IP Whitelist: Removed %s "%s" by %s. Was added by: %s',
                    $ipDetails['is_network'] ? 'network' : 'IP',
                    $ip,
                    $removedBy,
                    $ipDetails['created_by']
                );
                $this->log->insertLog($userId ?? 0, $logMessage, 'system');
            }

            return $result;

        } catch (Exception $e) {
            if ($userId) {
                $this->log->insertLog($userId, "IP Whitelist: Failed to remove {$ip}: " . $e->getMessage(), 'system');
                Messages::flash('ERROR', 'DEFAULT', "IP Whitelist: Failed to remove {$ip}: " . $e->getMessage());
            }
            return false;
        }
    }

    public function addToBlacklist($ip, $isNetwork = false, $reason = '', $createdBy = 'system', $userId = null, $expiryHours = null) {
        try {
            // Check if IP is whitelisted first
            if ($this->isIpWhitelisted($ip)) {
                $message = "Cannot blacklist {$ip} - IP is currently whitelisted";
                if ($userId) {
                    $this->log->insertLog($userId, "IP Blacklist: {$message}", 'system');
                    Messages::flash('ERROR', 'DEFAULT', $message);
                }
                return false;
            }

            $expiryTime = $expiryHours ? date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours")) : null;

            $stmt = $this->db->prepare("INSERT OR REPLACE INTO {$this->blacklistTable}
                (ip_address, is_network, reason, expiry_time, created_by)
                VALUES (?, ?, ?, ?, ?)");

            $result = $stmt->execute([$ip, $isNetwork, $reason, $expiryTime, $createdBy]);

            if ($result) {
                $logMessage = sprintf(
                    'IP Blacklist: Added %s "%s" by %s. Reason: %s. Expires: %s',
                    $isNetwork ? 'network' : 'IP',
                    $ip,
                    $createdBy,
                    $reason,
                    $expiryTime ?? 'never'
                );
                $this->log->insertLog($userId ?? 0, $logMessage, 'system');
            }

            return $result;
        } catch (Exception $e) {
            if ($userId) {
                $this->log->insertLog($userId, "IP Blacklist: Failed to add {$ip}: " . $e->getMessage(), 'system');
                Messages::flash('ERROR', 'DEFAULT', "IP Blacklist: Failed to add {$ip}: " . $e->getMessage());
            }
            return false;
        }
    }

    public function removeFromBlacklist($ip, $removedBy = 'system', $userId = null) {
        try {
            // Get IP details before removal for logging
            $stmt = $this->db->prepare("SELECT * FROM {$this->blacklistTable} WHERE ip_address = ?");
            $stmt->execute([$ip]);
            $ipDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Remove the IP
            $stmt = $this->db->prepare("DELETE FROM {$this->blacklistTable} WHERE ip_address = ?");
            $result = $stmt->execute([$ip]);

            if ($result && $ipDetails) {
                $logMessage = sprintf(
                    'IP Blacklist: Removed %s "%s" by %s. Was added by: %s. Reason was: %s',
                    $ipDetails['is_network'] ? 'network' : 'IP',
                    $ip,
                    $removedBy,
                    $ipDetails['created_by'],
                    $ipDetails['reason']
                );
                $this->log->insertLog($userId ?? 0, $logMessage, 'system');
            }

            return $result;
        } catch (Exception $e) {
            if ($userId) {
                $this->log->insertLog($userId, "IP Blacklist: Failed to remove {$ip}: " . $e->getMessage(), 'system');
                Messages::flash('ERROR', 'DEFAULT', "IP Blacklist: Failed to remove {$ip}: " . $e->getMessage());
            }
            return false;
        }
    }

    public function getWhitelistedIps() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->whitelistTable} ORDER BY created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBlacklistedIps() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->blacklistTable} ORDER BY created_at DESC");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cleanupExpiredEntries() {
        try {
            // Remove expired blacklist entries
            $stmt = $this->db->prepare("DELETE FROM {$this->blacklistTable}
                WHERE expiry_time IS NOT NULL AND expiry_time < datetime('now')");
            $stmt->execute();

            // Clean old login attempts
            $stmt = $this->db->prepare("DELETE FROM {$this->ratelimitTable}
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')");
            $stmt->execute([':minutes' => $this->decayMinutes]);

            return true;
        } catch (Exception $e) {
            $this->log->insertLog(0, "Failed to cleanup expired entries: " . $e->getMessage(), 'system');
            Messages::flash('ERROR', 'DEFAULT', "Failed to cleanup expired entries: " . $e->getMessage());
            return false;
        }
    }

    public function isAllowed($username, $ipAddress) {
        // First check if IP is blacklisted
        if ($this->isIpBlacklisted($ipAddress)) {
            return false;
        }

        // Then check if IP is whitelisted
        if ($this->isIpWhitelisted($ipAddress)) {
            return true;
        }

        // Clean old attempts
        $this->clearOldAttempts();

        // Check if we've hit the rate limit
        if ($this->tooManyAttempts($username, $ipAddress)) {
            return false;
        }

        // Check total attempts across all usernames from this IP
        $sql = "SELECT COUNT(*) as total_attempts
                FROM {$this->ratelimitTable}
                WHERE ip_address = :ip
                AND attempted_at > datetime('now', '-' || :minutes || ' minutes')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'      => $ipAddress,
            ':minutes' => $this->decayMinutes
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if we would hit auto-blacklist threshold
        return $result['total_attempts'] < $this->autoBlacklistThreshold;
    }

    public function attempt($username, $ipAddress) {
        // Record this attempt
        $sql = "INSERT INTO {$this->ratelimitTable} (ip_address, username) VALUES (:ip, :username)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'           => $ipAddress,
            ':username'     => $username
        ]);

        // Auto-blacklist if too many attempts
        if (!$this->isAllowed($username, $ipAddress)) {
            $this->addToBlacklist(
                $ipAddress,
                false,
                'Auto-blacklisted due to excessive login attempts',
                'system',
                null,
                $this->autoBlacklistDuration
            );
            return false;
        }

        return true;
    }

    public function tooManyAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts
                FROM {$this->ratelimitTable}
                WHERE ip_address = :ip
                AND username = :username
                AND attempted_at > datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip'           => $ipAddress,
            ':username'     => $username,
            ':minutes'      => $this->decayMinutes
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['attempts'] >= $this->maxAttempts;
    }

    public function clearOldAttempts() {
        $sql = "DELETE FROM {$this->ratelimitTable}
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':minutes'      => $this->decayMinutes
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
            ':ip'           => $ipAddress,
            ':username'     => $username,
            ':minutes'      => $this->decayMinutes
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return max(0, $this->maxAttempts - $result['attempts']);
    }

    public function getDecayMinutes() {
        return $this->decayMinutes;
    }
}
