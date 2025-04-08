<?php

/**
 * Handles password reset functionality including token generation and validation
 */
class PasswordReset {
    private $db;
    private const TOKEN_LENGTH = 32;
    private const TOKEN_EXPIRY = 3600; // 1 hour

    public function __construct($database) {
        if ($database instanceof PDO) {
            $this->db = $database;
        } else {
            $this->db = $database->getConnection();
        }
    }

    /**
     * Creates a password reset request and sends email to user
     *
     * @param string $email User's email address
     * @return array Status of the reset request
     */
    public function requestReset($email) {
        // Check if email exists
        $query = $this->db->prepare("
            SELECT u.id, um.email
            FROM users u
            JOIN users_meta um ON u.id = um.user_id
            WHERE um.email = :email"
        );
        $query->bindParam(':email', $email);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return ['success' => false, 'message' => 'If this email exists in our system, you will receive reset instructions.'];
        }

        // Generate unique token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH / 2));
        $expires = time() + self::TOKEN_EXPIRY;

        // Store token in database
        $query = $this->db->prepare("
            INSERT INTO user_password_reset (user_id, token, expires)
            VALUES (:user_id, :token, :expires)"
        );
        $query->bindParam(':user_id', $user['id']);
        $query->bindParam(':token', $token);
        $query->bindParam(':expires', $expires);

        if (!$query->execute()) {
            return ['success' => false, 'message' => 'Failed to process reset request'];
        }

        // We need the config for the email details
        global $config;

        // Prepare the reset link
        $scheme = $_SERVER['REQUEST_SCHEME'];
        $domain = trim($config['domain'], '/');
        $folder = trim($config['folder'], '/');
        $folderPath = $folder !== '' ? "/$folder" : '';
        $resetLink = "{$scheme}://{$domain}{$folderPath}/index.php?page=login&action=reset&token=" . urlencode($token);

        // Send email with reset link
        $to = $user['email'];
        $subject = "{$config['site_name']} - Password reset request";
        $message = "Dear user,\n\n";
        $message .= "We received a request to reset your password for your {$config['site_name']} account.\n\n";
        $message .= "To set a new password, please click the link below:\n\n";
        $message .= $resetLink . "\n\n";
        $message .= "This link will expire in 1 hour for security reasons.\n\n";
        $message .= "If you did not request this password reset, please ignore this email. Your account remains secure.\n\n";
        if (!empty($config['site_name'])) {
            $message .= "Best regards,\n";
            $message .= "The {$config['site_name']} team\n";
            if (!empty($config['site_slogan'])) {
                $message .= ":: {$config['site_slogan']} ::";
            }
        }

        $headers = [
            'From' => "noreply@{$config['domain']}",
            'Reply-To' => "noreply@{$config['domain']}",
            'X-Mailer' => 'PHP/' . phpversion()
        ];

        if (!mail($to, $subject, $message, $headers)) {
            return ['success' => false, 'message' => 'Failed to send reset email'];
        }

        return ['success' => true, 'message' => 'If this email exists in our system, you will receive reset instructions.'];
    }

    /**
     * Validates a reset token and returns associated user ID if valid
     *
     * @param string $token Reset token
     * @return array Validation result with user ID if successful
     */
    public function validateToken($token) {
        $now = time();

        $query = $this->db->prepare("
            SELECT user_id
            FROM user_password_reset
            WHERE token = :token
            AND expires > :now
            AND used = 0
        ");

        $query->bindParam(':token', $token);
        $query->bindParam(':now', $now);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['valid' => false];
        }

        return ['valid' => true, 'user_id' => $result['user_id']];
    }

    /**
     * Completes the password reset process
     *
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return bool Whether reset was successful
     */
    public function resetPassword($token, $newPassword) {
        $validation = $this->validateToken($token);
        if (!$validation['valid']) {
            return false;
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = $this->db->prepare(
                "UPDATE user
                SET password = :password
                WHERE id = :user_id"
            );
            $query->bindParam(':password', $hashedPassword);
            $query->bindParam(':user_id', $validation['user_id']);
            $query->execute();

            // Mark token as used
            $query = $this->db->prepare(
                "UPDATE user_password_reset
                SET used = 1
                WHERE token = :token"
            );
            $query->bindParam(':token', $token);
            $query->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
