<?php

/**
 * class User
 *
 * Handles user-related functionalities such as registration, login, rights management, and profile updates.
 */
class User {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;
    private $rateLimiter;
    private $twoFactorAuth;

    /**
     * User constructor.
     * Initializes the database connection.
     *
     * @param object $database The database object to initialize the connection.
     */
    public function __construct($database) {
        if ($database instanceof PDO) {
            $this->db = $database;
        } else {
            $this->db = $database->getConnection();
        }
        require_once __DIR__ . '/ratelimiter.php';
        require_once __DIR__ . '/twoFactorAuth.php';

        $this->rateLimiter = new RateLimiter($database);
        $this->twoFactorAuth = new TwoFactorAuthentication($database);
    }


    /**
     * Logs in a user by verifying credentials.
     *
     * @param string $username       The username of the user.
     * @param string $password       The password of the user.
     * @param string $twoFactorCode  Optional. The 2FA code if 2FA is enabled.
     *
     * @return array Login result with status and any necessary data
     */
    public function login($username, $password, $twoFactorCode = null) {
        // Get user's IP address
        require_once __DIR__ . '/../helpers/logs.php';
        $ipAddress = getUserIP();

        // Check rate limiting first
        if (!$this->rateLimiter->isAllowed($username, $ipAddress)) {
            $remainingTime = $this->rateLimiter->getDecayMinutes();
            throw new Exception("Too many login attempts. Please try again in {$remainingTime} minutes.");
        }

        // Then check credentials
        $query = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            // Check if 2FA is enabled
            if ($this->twoFactorAuth->isEnabled($user['id'])) {
                if ($twoFactorCode === null) {
                    return [
                        'status' => 'requires_2fa',
                        'user_id' => $user['id'],
                        'username' => $user['username']
                    ];
                }

                // Verify 2FA code
                if (!$this->twoFactorAuth->verify($user['id'], $twoFactorCode)) {
                    return [
                        'status' => 'invalid_2fa',
                        'message' => 'Invalid 2FA code'
                    ];
                }
            }

            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['CREATED'] = time();
            $_SESSION['LAST_ACTIVITY'] = time();
            return [
                'status' => 'success',
                'user_id' => $user['id'],
                'username' => $user['username']
            ];
        }

        // Get remaining attempts AFTER this failed attempt
        $remainingAttempts = $this->rateLimiter->getRemainingAttempts($username, $ipAddress);
        throw new Exception("Invalid credentials. {$remainingAttempts} attempts remaining.");
    }


    /**
     * Retrieves a user ID based on the username.
     *
     * @param string $username The username to look up.
     *
     * @return array|null User ID details or null if not found.
     */
    // FIXME not used now?
    public function getUserId($username) {
        $sql = 'SELECT id FROM users WHERE username = :username';
        $query = $this->db->prepare($sql);
        $query->bindParam(':username', $username);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Fetches user details by user ID.
     *
     * @param int $userId The user ID.
     *
     * @return array|null User details or null if not found.
     */
    public function getUserDetails($userId) {
        $sql = 'SELECT
                    um.*,
                    u.username
                FROM
                    users_meta um
                LEFT JOIN users u
                    ON um.user_id = u.id
                WHERE
                    u.id = :user_id';

        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $userId,
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Grants a user a specific right.
     *
     * @param int $userId    The user ID.
     * @param int $right_id  The right ID to grant.
     *
     * @return void
     */
    public function addUserRight($userId, $right_id) {
        $sql = 'INSERT INTO users_rights
                    (user_id, right_id)
                VALUES
                    (:user_id, :right_id)';
        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $userId,
            ':right_id'		=> $right_id,
        ]);
    }


    /**
     * Revokes a specific right from a user.
     *
     * @param int $userId    The user ID.
     * @param int $right_id  The right ID to revoke.
     *
     * @return void
     */
    public function removeUserRight($userId, $right_id) {
        $sql = 'DELETE FROM users_rights
                WHERE
                    user_id = :user_id
                AND
                    right_id = :right_id';
        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $userId,
            ':right_id'		=> $right_id,
        ]);
    }


    /**
     * Retrieves all rights in the system.
     *
     * @return array List of rights.
     */
    public function getAllRights() {
        $sql = 'SELECT
                    id AS right_id,
                    name AS right_name
                FROM rights
                ORDER BY id ASC';
        $query = $this->db->prepare($sql);
        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Retrieves the rights assigned to a specific user.
     *
     * @param int $userId The user ID.
     *
     * @return array List of user rights.
     */
    public function getUserRights($userId) {
        $sql = 'SELECT
                    u.id AS user_id,
                    r.id AS right_id,
                    r.name AS right_name
                FROM
                    users u
                    LEFT JOIN users_rights ur
                        ON u.id = ur.user_id
                    LEFT JOIN rights r
                        ON ur.right_id = r.id
                WHERE
                    u.id = :user_id';

        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $userId,
        ]);

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        // ensure specific entries are included in the result
        $specialEntries = [];

        // user 1 is always superuser
        if ($userId == 1) {
            $specialEntries = [
                [
                    'user_id' => 1,
                    'right_id' => 1,
                    'right_name' => 'superuser'
                ]
            ];

        // user 2 is always demo
        } elseif ($userId == 2) {
            $specialEntries = [
                [
                    'user_id' => 2,
                    'right_id' => 100,
                    'right_name' => 'demo user'
                ]
            ];
        }

        // merge the special entries with the existing results
        $result = array_merge($specialEntries, $result);
        // remove duplicates if necessary
        $result = array_unique($result, SORT_REGULAR);

        // return the modified result
        return $result;

    }


    /**
     * Check if the user has a specific right.
     *
     * @param int    $userId      The user ID.
     * @param string $right_name  The human-readable name of the user right.
     *
     * @return bool True if the user has the right, false otherwise.
     */
    function hasRight($userId, $right_name) {
        $userRights = $this->getUserRights($userId);
        $userHasRight = false;

        // superuser always has all the rights
        if ($userId === 1) {
            $userHasRight = true;
        }

        foreach ($userRights as $right) {
            if ($right['right_name'] === $right_name) {
                $userHasRight = true;
                break;
            }
        }

        return $userHasRight;

    }


    /**
     * Updates a user's metadata in the database.
     *
     * @param int   $userId       The ID of the user to update.
     * @param array $updatedUser  An associative array containing updated user data:
     *  - 'name' (string): The updated name of the user.
     *  - 'email' (string): The updated email of the user.
     *  - 'timezone' (string): The updated timezone of the user.
     *  - 'bio' (string): The updated biography of the user.
     *
     * @return bool|string Returns true if the update is successful, or an error message if an exception occurs.
     */
    public function editUser($userId, $updatedUser) {
        try {
            $sql = 'UPDATE users_meta SET
                        name = :name,
                        email = :email,
                        timezone = :timezone,
                        bio = :bio
                    WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'	=> $userId,
                ':name'		=> $updatedUser['name'],
                ':email'	=> $updatedUser['email'],
                ':timezone'	=> $updatedUser['timezone'],
                ':bio'		=> $updatedUser['bio']
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }


    /**
     * Removes a user's avatar from the database and deletes the associated file.
     *
     * @param int    $userId      The ID of the user whose avatar is being removed.
     * @param string $old_avatar  Optional. The file path of the current avatar to delete. Default is an empty string.
     *
     * @return bool|string Returns true if the avatar is successfully removed, or an error message if an exception occurs.
     */
    public function removeAvatar($userId, $old_avatar = '') {
        try {
            // remove from database
            $sql = 'UPDATE users_meta SET
                        avatar = NULL
                    WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'	=> $userId,
            ]);

            // delete the old avatar file
            if ($old_avatar && file_exists($old_avatar)) {
                unlink($old_avatar);
            }

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }


    /**
     * Updates a user's avatar by uploading a new file and saving its path in the database.
     *
     * @param int    $userId        The ID of the user whose avatar is being updated.
     * @param array  $avatar_file   The uploaded avatar file from the $_FILES array.
     *                              Should include 'tmp_name', 'name', 'error', etc.
     * @param string $avatars_path  The directory path where avatar files should be saved.
     *
     * @return bool|string Returns true if the avatar is successfully updated, or an error message if an exception occurs.
     */
    public function changeAvatar($userId, $avatar_file, $avatars_path) {
        try {
            // check if the file was uploaded
            if (isset($avatar_file) && $avatar_file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $avatar_file['tmp_name'];
                $fileName = $avatar_file['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // validate file extension
                if (in_array($fileExtension, ['jpg', 'png', 'jpeg'])) {
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    $dest_path = $avatars_path . $newFileName;

                    // move the file to avatars folder
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        try {
                            // update user's avatar path in DB
                            $sql = 'UPDATE users_meta SET
                                        avatar = :avatar
                                    WHERE user_id = :user_id';
                            $query = $this->db->prepare($sql);
                            $query->execute([
                                ':avatar' => $newFileName,
                                ':user_id' => $userId
                            ]);
                            // all went OK
                            $_SESSION['notice'] .= 'Avatar updated successfully. ';
                            return true;
                        } catch (Exception $e) {
                            return $e->getMessage();
                        }
                    } else {
                        $_SESSION['error'] .= 'Error moving the uploaded file. ';
                    }
                } else {
                    $_SESSION['error'] .= 'Invalid avatar file type. ';
                }
            } else {
                $_SESSION['error'] .= 'Error uploading the avatar file. ';
            }

        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get all users for messaging
     *
     * @return array List of users with their IDs and usernames
     */
    public function getUsers() {
        $sql = "SELECT id, username
                FROM users
                ORDER BY username ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enable two-factor authentication for a user
     *
     * @param int    $userId  User ID
     * @param string $secret  Secret key to use
     * @param string $code    Verification code to validate
     * @return bool True if enabled successfully
     */
    public function enableTwoFactor($userId, $secret = null, $code = null) {
        return $this->twoFactorAuth->enable($userId, $secret, $code);
    }

    /**
     * Disable two-factor authentication for a user
     *
     * @param int $userId User ID
     * @return bool True if disabled successfully
     */
    public function disableTwoFactor($userId) {
        return $this->twoFactorAuth->disable($userId);
    }

    /**
     * Verify a two-factor authentication code
     *
     * @param int    $userId  User ID
     * @param string $code    The verification code
     * @return bool True if verified
     */
    public function verifyTwoFactor($userId, $code) {
        return $this->twoFactorAuth->verify($userId, $code);
    }

    /**
     * Check if two-factor authentication is enabled for a user
     *
     * @param int $userId User ID
     * @return bool True if enabled
     */
    public function isTwoFactorEnabled($userId) {
        return $this->twoFactorAuth->isEnabled($userId);
    }

    /**
     * Change a user's password
     *
     * @param int    $userId           User ID
     * @param string $currentPassword  Current password for verification
     * @param string $newPassword      New password to set
     * @return bool True if password was changed successfully
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // First verify the current password
            $sql = "SELECT password FROM users WHERE id = :user_id";
            $query = $this->db->prepare($sql);
            $query->execute([':user_id' => $userId]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password'])) {
                return false;
            }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password
            $sql = "UPDATE users SET password = :password WHERE id = :user_id";
            $query = $this->db->prepare($sql);
            return $query->execute([
                ':password' => $hashedPassword,
                ':user_id' => $userId
            ]);

        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }
}
