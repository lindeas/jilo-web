<?php

class RateLimiter {
    public $db;
    private $log;
    public $maxAttempts = 5;           // Maximum login attempts
    public $decayMinutes = 15;         // Time window in minutes
    public $autoBlacklistThreshold = 10; // Attempts before auto-blacklist
    public $autoBlacklistDuration = 24;  // Hours to blacklist for
    public $authRatelimitTable = 'login_attempts';  // For username/password attempts
    public $pagesRatelimitTable = 'pages_rate_limits';  // For page requests
    public $whitelistTable = 'ip_whitelist';
    public $blacklistTable = 'ip_blacklist';
    private $pageLimits = [
        // Default rate limits per minute
        'default' => 60,
        'admin' => 120,
        'message' => 20,
        'contact' => 30,
        'call' => 30,
        'register' => 5,
        'config' => 10
    ];

    public function __construct($database) {
        if ($database instanceof PDO) {
            $this->db = $database;
        } else {
            $this->db = $database->getConnection();
        }
        $this->log = new Log($database);
        $this->createTablesIfNotExist();
    }

    // Database preparation
    private function createTablesIfNotExist() {
        // Authentication attempts table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->authRatelimitTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            username TEXT NOT NULL,
            attempted_at TEXT DEFAULT (DATETIME('now'))
        )";
        $this->db->exec($sql);

        // Pages rate limits table
        $sql = "CREATE TABLE IF NOT EXISTS {$this->pagesRatelimitTable} (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip_address TEXT NOT NULL,
            endpoint TEXT NOT NULL,
            request_time DATETIME DEFAULT CURRENT_TIMESTAMP
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
        $stmt = $this->db->prepare("SELECT COUNT(*) as attempts FROM {$this->authRatelimitTable} 
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
        $stmt = $this->db->prepare("SELECT ip_address, is_network, expiry_time FROM {$this->blacklistTable} WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Skip expired entries
            if ($row['expiry_time'] !== null && strtotime($row['expiry_time']) < time()) {
                return false;
            }
            return true;
        }

        // Check network ranges
        $stmt = $this->db->prepare("SELECT ip_address, expiry_time FROM {$this->blacklistTable} WHERE is_network = 1");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Skip expired entries
            if ($row['expiry_time'] !== null && strtotime($row['expiry_time']) < time()) {
                continue;
            }

            if ($this->ipInRange($ip, $row['ip_address'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP is whitelisted
     */
    public function isIpWhitelisted($ip) {
        // Check exact IP match first
        $stmt = $this->db->prepare("SELECT ip_address FROM {$this->whitelistTable} WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return true;
        }

        // Only check ranges for IPv4 addresses
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Check network ranges
            $stmt = $this->db->prepare("SELECT ip_address FROM {$this->whitelistTable} WHERE is_network = 1");
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($this->ipInRange($ip, $row['ip_address'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function ipInRange($ip, $cidr) {
        // Only work with IPv4 addresses
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        list($subnet, $bits) = explode('/', $cidr);

        // Make sure subnet is IPv4
        if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

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
                    Feedback::flash('ERROR', 'DEFAULT', $message);
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
                Feedback::flash('ERROR', 'DEFAULT', "IP Whitelist: Failed to add {$ip}: " . $e->getMessage());
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
                Feedback::flash('ERROR', 'DEFAULT', "IP Whitelist: Failed to remove {$ip}: " . $e->getMessage());
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
                    Feedback::flash('ERROR', 'DEFAULT', $message);
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
                Feedback::flash('ERROR', 'DEFAULT', "IP Blacklist: Failed to add {$ip}: " . $e->getMessage());
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
                Feedback::flash('ERROR', 'DEFAULT', "IP Blacklist: Failed to remove {$ip}: " . $e->getMessage());
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
            $stmt = $this->db->prepare("DELETE FROM {$this->authRatelimitTable}
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')");
            $stmt->execute([':minutes' => $this->decayMinutes]);

            return true;
        } catch (Exception $e) {
            $this->log->insertLog(0, "Failed to cleanup expired entries: " . $e->getMessage(), 'system');
            Feedback::flash('ERROR', 'DEFAULT', "Failed to cleanup expired entries: " . $e->getMessage());
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
                FROM {$this->authRatelimitTable}
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

    public function attempt($username, $ipAddress, $failed = true) {
        // Only record failed attempts
        if (!$failed) {
            return true;
        }

        // Record this attempt
        $sql = "INSERT INTO {$this->authRatelimitTable} (ip_address, username) VALUES (:ip, :username)";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                ':ip'           => $ipAddress,
                ':username'     => $username
            ]);
        } catch (PDOException $e) {
            return false;
        }

        return true;
    }

    public function tooManyAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts
                FROM {$this->authRatelimitTable}
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

        // Also check what's in the table
        $sql = "SELECT * FROM {$this->authRatelimitTable} WHERE ip_address = :ip";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ip' => $ipAddress]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tooMany = $result['attempts'] >= $this->maxAttempts;

        // Auto-blacklist if too many attempts
        if ($tooMany) {
            $this->addToBlacklist(
                $ipAddress,
                false,
                'Auto-blacklisted due to excessive login attempts',
                'system',
                null,
                $this->autoBlacklistDuration
            );
        }

        return $tooMany;
    }

    public function clearOldAttempts() {
        $sql = "DELETE FROM {$this->authRatelimitTable}
                WHERE attempted_at < datetime('now', '-' || :minutes || ' minutes')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':minutes'      => $this->decayMinutes
        ]);
    }

    public function getRemainingAttempts($username, $ipAddress) {
        $sql = "SELECT COUNT(*) as attempts
                FROM {$this->authRatelimitTable}
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

    /**
     * Check if a page request is allowed
     */
    public function isPageRequestAllowed($ipAddress, $endpoint, $userId = null) {
        // First check if IP is blacklisted
        if ($this->isIpBlacklisted($ipAddress)) {
            return false;
        }

        // Then check if IP is whitelisted
        if ($this->isIpWhitelisted($ipAddress)) {
            return true;
        }

        // Clean old requests
        $this->cleanOldPageRequests();

        // Get limit based on endpoint type and user role
        $limit = $this->getPageLimitForEndpoint($endpoint, $userId);

        // Count recent requests, including this one
        $sql = "SELECT COUNT(*) as request_count
                FROM {$this->pagesRatelimitTable}
                WHERE ip_address = :ip
                AND endpoint = :endpoint
                AND request_time >= DATETIME('now', '-1 minute')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip' => $ipAddress,
            ':endpoint' => $endpoint
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['request_count'] < $limit;
    }

    /**
     * Record a page request
     */
    public function recordPageRequest($ipAddress, $endpoint) {
        $sql = "INSERT INTO {$this->pagesRatelimitTable} (ip_address, endpoint)
                VALUES (:ip, :endpoint)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':ip' => $ipAddress,
            ':endpoint' => $endpoint
        ]);
    }

    /**
     * Clean old page requests
     */
    private function cleanOldPageRequests() {
        $sql = "DELETE FROM {$this->pagesRatelimitTable} 
                WHERE request_time < DATETIME('now', '-1 minute')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get page rate limit for endpoint
     */
    private function getPageLimitForEndpoint($endpoint, $userId = null) {
        // Admin users get higher limits
        if ($userId) {
            $userObj = new User($this->db);
            if ($userObj->hasRight($userId, 'admin')) {
                return $this->pageLimits['admin'];
            }
        }

        // Get endpoint type from the endpoint path
        $endpointType = $this->getEndpointType($endpoint);

        // Return specific limit if exists, otherwise default
        return isset($this->pageLimits[$endpointType])
            ? $this->pageLimits[$endpointType]
            : $this->pageLimits['default'];
    }

    /**
     * Get endpoint type from path
     */
    private function getEndpointType($endpoint) {
        if (strpos($endpoint, 'message') !== false) return 'message';
        if (strpos($endpoint, 'contact') !== false) return 'contact';
        if (strpos($endpoint, 'call') !== false) return 'call';
        if (strpos($endpoint, 'register') !== false) return 'register';
        if (strpos($endpoint, 'config') !== false) return 'config';
        return 'default';
    }

    /**
     * Get remaining page requests
     */
    public function getRemainingPageRequests($ipAddress, $endpoint, $userId = null) {
        $limit = $this->getPageLimitForEndpoint($endpoint, $userId);

        $sql = "SELECT COUNT(*) as request_count
                FROM {$this->pagesRatelimitTable}
                WHERE ip_address = :ip
                AND endpoint = :endpoint
                AND request_time > DATETIME('now', '-1 minute')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ip' => $ipAddress,
            ':endpoint' => $endpoint
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return max(0, $limit - $result['request_count']);
    }
}
