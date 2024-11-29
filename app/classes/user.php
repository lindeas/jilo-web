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

    /**
     * User constructor.
     *
     * @param object $database Database instance to retrieve a connection.
     */
    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    /**
     * Registers a new user.
     *
     * @param string $username The username of the new user.
     * @param string $password The password for the new user.
     *
     * @return bool|string True if registration is successful, error message otherwise.
     */
    public function register($username, $password) {
        try {
            // we have two inserts, start a transaction
            $this->db->beginTransaction();

            // hash the password, don't store it plain
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // insert into users table
            $sql = 'INSERT
                        INTO users (username, password)
                        VALUES (:username, :password)';
            $query = $this->db->prepare($sql);
            $query->bindValue(':username', $username);
            $query->bindValue(':password', $hashedPassword);

            // execute the first query
            if (!$query->execute()) {
                // rollback on error
                $this->db->rollBack();
                return false;
            }

            // insert the last user id into users_meta table
            $sql2 = 'INSERT
                        INTO users_meta (user_id)
                        VALUES (:user_id)';
            $query2 = $this->db->prepare($sql2);
            $query2->bindValue(':user_id', $this->db->lastInsertId());

            // execute the second query
            if (!$query2->execute()) {
                // rollback on error
                $this->db->rollBack();
                return false;
            }

            // if all is OK, commit the transaction
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            // rollback on any error
            $this->db->rollBack();
            return $e->getMessage();
        }
    }

    /**
     * Logs in a user by verifying credentials.
     *
     * @param string $username The username of the user.
     * @param string $password The password of the user.
     *
     * @return bool True if login is successful, false otherwise.
     */
    public function login($username, $password) {
        $query = $this->db->prepare("SELECT * FROM  users WHERE username = :username");
        $query->bindParam(':username', $username);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        if ( $user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        } else {
            return false;
        }
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
     * @param int $user_id The user ID.
     *
     * @return array|null User details or null if not found.
     */
    public function getUserDetails($user_id) {
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
            ':user_id'		=> $user_id,
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    /**
     * Grants a user a specific right.
     *
     * @param int $user_id The user ID.
     * @param int $right_id The right ID to grant.
     *
     * @return void
     */
    public function addUserRight($user_id, $right_id) {
        $sql = 'INSERT INTO users_rights
                    (user_id, right_id)
                VALUES
                    (:user_id, :right_id)';
        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $user_id,
            ':right_id'		=> $right_id,
        ]);
    }

    /**
     * Revokes a specific right from a user.
     *
     * @param int $user_id The user ID.
     * @param int $right_id The right ID to revoke.
     *
     * @return void
     */
    public function removeUserRight($user_id, $right_id) {
        $sql = 'DELETE FROM users_rights
                WHERE
                    user_id = :user_id
                AND
                    right_id = :right_id';
        $query = $this->db->prepare($sql);
        $query->execute([
            ':user_id'		=> $user_id,
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
     * @param int $user_id The user ID.
     *
     * @return array List of user rights.
     */
    public function getUserRights($user_id) {
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
            ':user_id'		=> $user_id,
        ]);

        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        // ensure specific entries are included in the result
        $specialEntries = [];

        // user 1 is always superuser
        if ($user_id == 1) {
            $specialEntries = [
                [
                    'user_id' => 1,
                    'right_id' => 1,
                    'right_name' => 'superuser'
                ]
            ];

        // user 2 is always demo
        } elseif ($user_id == 2) {
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
     * @param int $user_id The user ID.
     * @param string $right_name The human-readable name of the user right.
     *
     * @return bool True if the user has the right, false otherwise.
     */
    function hasRight($user_id, $right_name) {
        $userRights = $this->getUserRights($user_id);
        $userHasRight = false;

        // superuser always has all the rights
        if ($user_id === 1) {
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
     * @param int $user_id The ID of the user to update.
     * @param array $updatedUser An associative array containing updated user data:
     *  - 'name' (string): The updated name of the user.
     *  - 'email' (string): The updated email of the user.
     *  - 'timezone' (string): The updated timezone of the user.
     *  - 'bio' (string): The updated biography of the user.
     *
     * @return bool|string Returns true if the update is successful, or an error message if an exception occurs.
     */
    public function editUser($user_id, $updatedUser) {
        try {
            $sql = 'UPDATE users_meta SET
                        name = :name,
                        email = :email,
                        timezone = :timezone,
                        bio = :bio
                    WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'	=> $user_id,
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
     * @param int $user_id The ID of the user whose avatar is being removed.
     * @param string $old_avatar Optional. The file path of the current avatar to delete. Default is an empty string.
     *
     * @return bool|string Returns true if the avatar is successfully removed, or an error message if an exception occurs.
     */
    public function removeAvatar($user_id, $old_avatar = '') {
        try {
            // remove from database
            $sql = 'UPDATE users_meta SET
                        avatar = NULL
                    WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'	=> $user_id,
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
     * @param int $user_id The ID of the user whose avatar is being updated.
     * @param array $avatar_file The uploaded avatar file from the $_FILES array.
     *                           Should include 'tmp_name', 'name', 'error', etc.
     * @param string $avatars_path The directory path where avatar files should be saved.
     *
     * @return bool|string Returns true if the avatar is successfully updated, or an error message if an exception occurs.
     */
    public function changeAvatar($user_id, $avatar_file, $avatars_path) {
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
                                ':user_id' => $user_id
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

}

?>
