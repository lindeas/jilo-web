<?php

/**
 * class Database
 *
 * Manages database connections for SQLite and MySQL (or MariaDB).
 */
class Database {
    /**
     * @var PDO|null $pdo The database connection instance.
     */
    private $pdo;

    /**
     * Database constructor.
     * Initializes the database connection based on provided options.
     *
     * @param array $options An associative array with database connection options:
     *                       - type: The database type ('sqlite', 'mysql', or 'mariadb').
     *                       - dbFile: The path to the SQLite database file (required for SQLite).
     *                       - host: The database host (required for MySQL).
     *                       - port: The port for MySQL (optional, default: 3306).
     *                       - dbname: The name of the MySQL database (required for MySQL).
     *                       - user: The username for MySQL (required for MySQL).
     *                       - password: The password for MySQL (optional).
     *
     * @throws Exception If required extensions are not loaded or options are invalid.
     */
    public function __construct($options) {
        // check if PDO extension is loaded
        if (!extension_loaded('pdo')) {
            throw new Exception('PDO extension not loaded.');
        }

        // options check
        if (empty($options['type'])) {
            throw new Exception('Database type is not set.');
        }

        // connect based on database type
        switch ($options['type']) {
            case 'sqlite':
                $this->connectSqlite($options);
                break;
            case 'mysql':
            case 'mariadb':
                $this->connectMysql($options);
                break;
            default:
                $this->pdo = null;
        }
    }

    /**
     * Establishes a connection to a SQLite database.
     *
     * @param array $options An associative array with SQLite connection options:
     *                       - dbFile: The path to the SQLite database file.
     *
     * @throws Exception If the SQLite PDO extension is not loaded or the database file is missing.
     */
    private function connectSqlite($options) {
        // pdo_sqlite extension is needed
        if (!extension_loaded('pdo_sqlite')) {
            throw new Exception('PDO extension for SQLite not loaded.');
        }

        // SQLite options
        if (empty($options['dbFile'])) {
            throw new Exception('SQLite database file path is missing.');
        }

        // For in-memory database (especially for the tests), skip file check
        if ($options['dbFile'] !== ':memory:' && !file_exists($options['dbFile'])) {
            throw new Exception("SQLite database file \"{$options['dbFile']}\" not found.");
        }

        // connect to SQLite
        try {
            $this->pdo = new PDO("sqlite:" . $options['dbFile']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // enable foreign key constraints (not ON by default in SQLite3)
            $this->pdo->exec('PRAGMA foreign_keys = ON;');
        } catch (PDOException $e) {
            throw new Exception('SQLite connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Establishes a connection to a MySQL (or MariaDB) database.
     *
     * @param array $options An associative array with MySQL connection options:
     *                       - host: The database host.
     *                       - port: The database port (default: 3306).
     *                       - dbname: The name of the database.
     *                       - user: The database username.
     *                       - password: The database password (optional).
     *
     * @throws Exception If the MySQL PDO extension is not loaded or required options are missing.
     */
    private function connectMysql($options) {
        // pdo_mysql extension is needed
        if (!extension_loaded('pdo_mysql')) {
            throw new Exception('PDO extension for MySQL not loaded.');
        }

        // MySQL options
        if (empty($options['host']) || empty($options['dbname']) || empty($options['user'])) {
            throw new Exception('MySQL connection data is missing.');
        }

        // Connect to MySQL
        try {
            $port = $options['port'] ?? 3306;
            $dsn = "mysql:host={$options['host']};port={$port};dbname={$options['dbname']};charset=utf8";
            $this->pdo = new PDO($dsn, $options['user'], $options['password'] ?? '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->pdo = null;
        }
    }

    /**
     * Retrieves the current PDO connection instance.
     *
     * @return PDO|null The PDO instance or null if no connection is established.
     */
    public function getConnection() {
        return $this->pdo;
    }

}

?>
