<?php

class User {
    private $db;

    public function __construct($database) {
        $this->db = $database->getConnection();
    }

    // registration
    public function register($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $query = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $query->bindParam(':username', $username);
        $query->bindParam(':password', $hashedPassword);

        return $query->execute();
    }

    // login
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

    // get user ID from username
    // FIXME not used now?
    public function getUserId($username) {
        $sql = 'SELECT id FROM users WHERE username = :username';
        $query = $this->db->prepare($sql);
        $query->bindParam(':username', $username);

        $query->execute();

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    // get user details
    public function getUserDetails($username) {
        $sql = 'SELECT * FROM users_meta um
                    LEFT JOIN users u
                    ON um.user_id = u.id
                    WHERE u.username = :username';
        $query = $this->db->prepare($sql);
        $query->execute([
            ':username'		=> $username,
        ]);

        return $query->fetchAll(PDO::FETCH_ASSOC);

    }

    // update an existing user
    public function editUser($user_id, $updatedUser) {
        try {
            $sql = 'UPDATE users_meta SET
                        name = :name,
                        email = :email,
                        bio = :bio
                    WHERE user_id = :user_id';
            $query = $this->db->prepare($sql);
            $query->execute([
                ':user_id'	=> $user_id,
                ':name'		=> $updatedUser['name'],
                ':email'	=> $updatedUser['email'],
                ':bio'		=> $updatedUser['bio']
            ]);

            return true;

        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    // remove an avatar
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

    // change an avatar
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
