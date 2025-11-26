<?php

/**
 * Class TwoFactorAuthentication
 *
 * Handles two-factor authentication functionality using TOTP (Time-based One-Time Password).
 * Internal implementation without external dependencies.
 */
class TwoFactorAuthentication {
    private $db;
    private $secretLength = 20; // 160 bits for SHA1
    private $period = 30;       // Time step in seconds (T0)
    private $digits = 6;        // Number of digits in TOTP code
    private $algorithm = 'sha1'; // HMAC algorithm
    private $issuer = 'Jilo';
    private $window = 1;        // Time window of 1 step before/after

    /**
     * Constructor
     *
     * @param PDO $database Database connection
     */
    public function __construct($database) {
        if ($database instanceof PDO) {
            $this->db = $database;
        } else {
            $this->db = $database->getConnection();
        }
    }

    /**
     * Enable 2FA for a user
     *
     * @param int $userId User ID
     * @param string $secret Secret key (base32 encoded)
     * @param string $code Verification code
     * @return bool True if enabled successfully
     */
    public function enable($userId, $secret = null, $code = null) {
        try {
            // Check if 2FA is already enabled
            $stmt = $this->db->prepare('SELECT enabled FROM user_2fa WHERE user_id = ?');
            $stmt->execute([$userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing && $existing['enabled']) {
                return false;
            }

            // If no secret provided, generate one and return setup data
            if ($secret === null) {
                // Generate secret key
                $secret = $this->generateSecret();

                // Get user's username for the QR code
                $stmt = $this->db->prepare('SELECT username FROM user WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Generate backup codes
                $backupCodes = $this->generateBackupCodes();

                // Store in database without enabling yet
                $this->db->beginTransaction();

                $stmt = $this->db->prepare('
                    INSERT INTO user_2fa (user_id, secret_key, backup_codes, enabled, created_at)
                    VALUES (?, ?, ?, 0, NOW())
                    ON DUPLICATE KEY UPDATE
                        secret_key = VALUES(secret_key),
                        backup_codes = VALUES(backup_codes),
                        enabled = VALUES(enabled),
                        created_at = VALUES(created_at)
                ');

                $stmt->execute([
                    $userId,
                    $secret,
                    json_encode($backupCodes)
                ]);

                $this->db->commit();

                // Generate otpauth URL for QR code
                $otpauthUrl = $this->generateOtpauthUrl($user['username'], $secret);

                return [
                    'success' => true,
                    'data' => [
                        'secret' => $secret,
                        'otpauthUrl' => $otpauthUrl,
                        'backupCodes' => $backupCodes
                    ]
                ];
            }

            // If secret and code provided, verify the code and enable 2FA
            if ($code !== null) {
                // Verify the setup code
                if (!$this->verify($userId, $code)) {
                    error_log("Code verification failed");
                    return false;
                }

                // Enable 2FA
                $stmt = $this->db->prepare('
                    UPDATE user_2fa
                    SET enabled = 1
                    WHERE user_id = ? AND secret_key = ?
                ');
                return $stmt->execute([$userId, $secret]);
            }

            return false;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('2FA enable error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify a 2FA code
     *
     * @param int $userId User ID
     * @param string $code The verification code
     * @return bool True if verified, false otherwise
     */
    public function verify($userId, $code) {
        try {
            // Get user's 2FA settings
            $settings = $this->getUserSettings($userId);
            if (!$settings) {
                return false;
            }

            // Check if code matches a backup code
            if ($this->verifyBackupCode($userId, $code)) {
                return true;
            }

            // Get current Unix timestamp
            $currentTime = time();

            // Check time window
            for ($timeSlot = -$this->window; $timeSlot <= $this->window; $timeSlot++) {
                $checkTime = $currentTime + ($timeSlot * $this->period);
                $generatedCode = $this->generateCode($settings['secret_key'], $checkTime);
                if (hash_equals($generatedCode, $code)) {
                    return true;
                }
            }

            return false;

        } catch (Exception $e) {
            error_log('2FA verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a random secret key
     *
     * @return string Base32 encoded secret
     */
    private function generateSecret() {
        // Generate random bytes (160 bits for SHA1)
        $random = random_bytes($this->secretLength);
        return $this->base32Encode($random);
    }

    /**
     * Base32 encode data
     *
     * @param string $data Data to encode
     * @return string Base32 encoded string
     */
    private function base32Encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        $encoded = '';

        // Convert to binary
        for ($i = 0; $i < strlen($data); $i++) {
            $binary .= str_pad(decbin(ord($data[$i])), 8, '0', STR_PAD_LEFT);
        }

        // Process 5 bits at a time
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $encoded .= $alphabet[bindec($chunk)];
        }

        // Add padding
        $padding = strlen($encoded) % 8;
        if ($padding > 0) {
            $encoded .= str_repeat('=', 8 - $padding);
        }

        return $encoded;
    }

    /**
     * Base32 decode data
     *
     * @param string $data Base32 encoded string
     * @return string Decoded data
     */
    private function base32Decode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

        // Remove padding and uppercase
        $data = rtrim(strtoupper($data), '=');

        $binary = '';

        // Convert to binary
        for ($i = 0; $i < strlen($data); $i++) {
            $position = strpos($alphabet, $data[$i]);
            if ($position === false) {
                continue;
            }
            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        // Process 8 bits at a time
        for ($i = 0; $i + 7 < strlen($binary); $i += 8) {
            $chunk = substr($binary, $i, 8);
            $decoded .= chr(bindec($chunk));
        }

        return $decoded;
    }

    /**
     * Generate a TOTP code for a given secret and time
     * RFC 6238 compliant implementation
     */
    private function generateCode($secret, $time) {
        // Calculate number of time steps since Unix epoch
        $timeStep = (int)floor($time / $this->period);

        // Pack time into 8 bytes (64-bit big-endian)
        $timeBin = pack('J', $timeStep);

        // Clean secret of any padding
        $secret = rtrim($secret, '=');

        // Get binary secret
        $secretBin = $this->base32Decode($secret);

        // Calculate HMAC
        $hash = hash_hmac($this->algorithm, $timeBin, $secretBin, true);

        // Get dynamic truncation offset
        $offset = ord($hash[strlen($hash) - 1]) & 0xF;

        // Generate 31-bit number
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, $this->digits);

        $code = str_pad($code, $this->digits, '0', STR_PAD_LEFT);

        return $code;
    }

    /**
     * Generate otpauth URL for QR codes
     * Format: otpauth://totp/ISSUER:ACCOUNT?secret=SECRET&issuer=ISSUER&algorithm=ALGORITHM&digits=DIGITS&period=PERIOD
     */
    private function generateOtpauthUrl($username, $secret) {
        $params = [
            'secret' => $secret,
            'issuer' => $this->issuer,
            'algorithm' => strtoupper($this->algorithm),
            'digits' => $this->digits,
            'period' => $this->period
        ];

        return sprintf(
            'otpauth://totp/%s:%s?%s',
            rawurlencode($this->issuer),
            rawurlencode($username),
            http_build_query($params)
        );
    }

    /**
     * Generate backup codes
     *
     * @param int $count Number of backup codes to generate
     * @return array Array of backup codes
     */
    private function generateBackupCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        return $codes;
    }

    /**
     * Verify a backup code
     *
     * @param int $userId User ID
     * @param string $code The backup code to verify
     * @return bool True if verified, false otherwise
     */
    private function verifyBackupCode($userId, $code) {
        try {
            $stmt = $this->db->prepare('SELECT backup_codes FROM user_2fa WHERE user_id = ?');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return false;
            }

            $backupCodes = json_decode($result['backup_codes'], true);

            // Check if the code exists and hasn't been used
            $codeIndex = array_search($code, $backupCodes);
            if ($codeIndex !== false) {
                // Remove the used code
                unset($backupCodes[$codeIndex]);
                $backupCodes = array_values($backupCodes);

                // Update backup codes in database
                $stmt = $this->db->prepare('
                    UPDATE user_2fa
                    SET backup_codes = ?
                    WHERE user_id = ?
                ');
                $stmt->execute([json_encode($backupCodes), $userId]);

                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Backup code verification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable 2FA for a user
     *
     * @param int $userId User ID
     * @return bool True if disabled successfully
     */
    public function disable($userId) {
        try {
            // First check if user has 2FA settings
            $settings = $this->getUserSettings($userId);
            if (!$settings) {
                return false;
            }

            // Delete the 2FA settings entirely instead of just disabling
            $stmt = $this->db->prepare('
                DELETE FROM user_2fa
                WHERE user_id = ?
            ');
            return $stmt->execute([$userId]);

        } catch (Exception $e) {
            error_log('2FA disable error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if 2FA is enabled for a user
     *
     * @param int $userId User ID
     * @return bool True if enabled
     */
    public function isEnabled($userId) {
        try {
            $stmt = $this->db->prepare('SELECT enabled FROM user_2fa WHERE user_id = ?');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['enabled'];

        } catch (Exception $e) {
            error_log('2FA status check error: ' . $e->getMessage());
            return false;
        }
    }

    private function getUserSettings($userId) {
        try {
            $stmt = $this->db->prepare('
                SELECT secret_key, backup_codes, enabled
                FROM user_2fa
                WHERE user_id = ?
            ');
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Failed to get user 2FA settings: ' . $e->getMessage());
            return null;
        }
    }
}
