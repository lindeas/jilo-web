<?php

/**
 * Class TwoFactorAuthentication
 *
 * Handles two-factor authentication functionality using TOTP (Time-based One-Time Password).
 * Internal implementation without external dependencies.
 */
class TwoFactorAuthentication {
    private $db;
    private $secretLength = 32;
    private $period = 30; // Time step in seconds
    private $digits = 6;  // Number of digits in TOTP code
    private $algorithm = 'sha1';
    private $issuer = 'Jilo';

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
     * @return array Array containing success status and data (secret, QR code URL)
     */
    public function enable($userId) {
        try {
            // Check if 2FA is already enabled
            $stmt = $this->db->prepare('SELECT enabled FROM user_2fa WHERE user_id = ?');
            $stmt->execute([$userId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing && $existing['enabled']) {
                return ['success' => false, 'message' => '2FA is already enabled'];
            }

            // Generate secret key
            $secret = $this->generateSecret();

            // Get user's username for the QR code
            $stmt = $this->db->prepare('SELECT username FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate backup codes
            $backupCodes = $this->generateBackupCodes();

            // Store in database
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

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
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
            $stmt = $this->db->prepare('
                SELECT secret_key, backup_codes, enabled
                FROM user_2fa
                WHERE user_id = ?
            ');
            $stmt->execute([$userId]);
            $tfa = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tfa || !$tfa['enabled']) {
                return false;
            }

            // Check if it's a backup code
            if ($this->verifyBackupCode($userId, $code)) {
                return true;
            }

            // Verify TOTP code
            $currentTime = time();

            // Check current and adjacent time steps
            for ($timeStep = -1; $timeStep <= 1; $timeStep++) {
                $checkTime = $currentTime + ($timeStep * $this->period);
                if ($this->generateCode($tfa['secret_key'], $checkTime) === $code) {
                    // Update last used timestamp
                    $stmt = $this->db->prepare('
                        UPDATE user_2fa
                        SET last_used = NOW()
                        WHERE user_id = ?
                    ');
                    $stmt->execute([$userId]);
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
     * Generate a TOTP code for a given secret and time
     *
     * @param string $secret The secret key
     * @param int $time Current Unix timestamp
     * @return string Generated code
     */
    private function generateCode($secret, $time) {
        $timeStep = floor($time / $this->period);
        $timeHex = str_pad(dechex($timeStep), 16, '0', STR_PAD_LEFT);

        // Convert hex time to binary
        $timeBin = '';
        for ($i = 0; $i < strlen($timeHex); $i += 2) {
            $timeBin .= chr(hexdec(substr($timeHex, $i, 2)));
        }

        // Get binary secret
        $secretBin = $this->base32Decode($secret);

        // Calculate HMAC
        $hash = hash_hmac($this->algorithm, $timeBin, $secretBin, true);

        // Get offset
        $offset = ord($hash[strlen($hash) - 1]) & 0xF;

        // Generate 4-byte code
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % pow(10, $this->digits);

        return str_pad($code, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a random secret key
     *
     * @return string Base32 encoded secret
     */
    private function generateSecret() {
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
            $chunk = substr($binary . '0000', $i, 5);
            $encoded .= $alphabet[bindec($chunk)];
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
        $binary = '';
        $decoded = '';

        // Convert to binary
        for ($i = 0; $i < strlen($data); $i++) {
            $position = strpos($alphabet, $data[$i]);
            if ($position === false) continue;
            $binary .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        // Process 8 bits at a time
        for ($i = 0; $i + 7 < strlen($binary); $i += 8) {
            $chunk = substr($binary, $i, 8);
            $decoded .= chr(bindec($chunk));
        }

        return $decoded;
    }

    /**
     * Generate otpauth URL for QR codes
     *
     * @param string $username Username
     * @param string $secret Secret key
     * @return string otpauth URL
     */
    private function generateOtpauthUrl($username, $secret) {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            urlencode($this->issuer),
            urlencode($username),
            $secret,
            urlencode($this->issuer),
            strtoupper($this->algorithm),
            $this->digits,
            $this->period
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
            $stmt = $this->db->prepare('
                UPDATE user_2fa
                SET enabled = 0,
                    secret_key = NULL,
                    backup_codes = NULL
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
}
