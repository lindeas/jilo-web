<?php

class Database {
    private $pdo;

    public function __construct($dbFile) {
        // pdo and pdo_sqlite needed
        if ( !extension_loaded('pdo_sqlite') ) {
            throw new Exception('PDO extension for SQLite not loaded.');
        }

        // connect to database
        // FIXME: add mysql/mariadb option
        try {
            $this->pdo = new PDO("sqlite:" . $dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('DB connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

}

?>
