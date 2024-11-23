<?php

/**
 * Class Database
 * Manages database connections for SQLite and MySQL (or MariaDB).
 */
class Database {
    /**
     * @var PDO|null $pdo The PDO instance representing the database connection.
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
            // enable foreign key constraints (not ON by default in SQLite3)
            $this->pdo->exec('PRAGMA foreign_keys = ON;');
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
            $error = getError('MySQL connection failed: ', $e->getMessage(), $config['environment']);
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

}

?>
