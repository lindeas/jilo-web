<?php

/**
 * class Register
 *
 * Handles user registration.
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
        require_once dirname(__FILE__, 4) . '/app/classes/ratelimiter.php';
        require_once dirname(__FILE__, 4) . '/app/classes/twoFactorAuth.php';

        $this->rateLimiter = new RateLimiter($database);
        $this->twoFactorAuth = new TwoFactorAuthentication($database);
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
