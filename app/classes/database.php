<?php

require '../app/helpers/errors.php';

class Database {
    private $pdo;

    public function __construct($options) {
        // pdo needed
        if ( !extension_loaded('pdo') ) {
            $error = getError('PDO extension not loaded.');
        }

        // options check
        if (empty($options['type'])) {
            $error = getError('Database type is not set.');
        }

        // database type
        switch ($options['type']) {
            case 'sqlite':
                $this->connectSqlite($options);
                break;
            case 'mysql' || 'mariadb':
                $this->connectMysql($options);
                break;
            default:
                $error = getError("Database type \"{$options['type']}\" is not supported.");
        }
    }

    private function connectSqlite($options) {
        // pdo_sqlite extension is needed
        if (!extension_loaded('pdo_sqlite')) {
            $error = getError('PDO extension for SQLite not loaded.');
        }

        // SQLite options
        if (empty($options['dbFile']) || !file_exists($options['dbFile'])) {
            $error = getError("SQLite database file \"{$dbFile}\" not found.");
        }

        // connect to SQLite
        try {
            $this->pdo = new PDO("sqlite:" . $options['dbFile']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $error = getError('SQLite connection failed: ', $e->getMessage());
        }
    }

    private function connectMysql($options) {
        // pdo_mysql extension is needed
        if (!extension_loaded('pdo_mysql')) {
            $error = getError('PDO extension for MySQL not loaded.');
        }

        // MySQL options
        if (empty($options['host']) || empty($options['dbname']) || empty($options['user'])) {
            $error = getError('MySQL connection data is missing.');
        }

        // Connect to MySQL
        try {
            $dsn = "mysql:host={$options['host']};port={$options['port']};dbname={$options['dbname']};charset=utf8";
            $this->pdo = new PDO($dsn, $options['user'], $options['password'] ?? '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $error = getError('MySQL connection failed: ', $config['environment'], $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

}

?>
