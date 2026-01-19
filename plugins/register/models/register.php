<?php

use App\App;

/**
 * class Register
 *
 * Handles user registration using the App API pattern.
 */
class Register {
    /**
     * @var PDO|null $db The database connection instance.
     */
    private $db;
    private $rateLimiter;
    private $twoFactorAuth;

    /**
     * Register constructor.
     * Initializes the database connection using App API.
     *
     * @param PDO|null $database The database connection (optional, will use App::db() if not provided).
     */
    public function __construct($database = null) {
        $this->db = $database instanceof PDO ? $database : App::db();

        require_once APP_PATH . 'classes/ratelimiter.php';
        require_once APP_PATH . 'classes/twoFactorAuth.php';

        $this->rateLimiter = new RateLimiter($this->db);
        $this->twoFactorAuth = new TwoFactorAuthentication($this->db);
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

            // insert into user table
            $sql = 'INSERT
                        INTO user (username, password)
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

            // insert the last user id into user_meta table
            $sql2 = 'INSERT
                        INTO user_meta (user_id)
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

}
