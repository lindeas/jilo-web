<?php

// connect to database
function connectDB($config) {
    // sqlite database file
    if ($config['db_type'] === 'sqlite') {
        try {
            $dbFile = $config['sqlite']['sqlite_file'] ?? null;
            if (!$dbFile || !file_exists($dbFile)) {
                throw new Exception(getError("Database file \"{$dbFile}\"not found."));
            }
            $db = new Database([
                'type'		=> $config['db_type'],
                'dbFile'	=> $dbFile,
            ]);
            $pdo = $db->getConnection();
        } catch (Exception $e) {
            Feedback::flash('ERROR', 'DEFAULT', getError('Error connecting to DB.', $e->getMessage()));
            return false;
        }
        return $db;

    // mysql/mariadb database
    } elseif ($config['db_type'] === 'mysql' || $config['db_type'] === 'mariadb') {
        $db = new Database([
            'type'		=> $config['db_type'],
            'host'		=> $config['sql']['sql_host'] ?? 'localhost',
            'port'		=> $config['sql']['sql_port'] ?? '3306',
            'dbname'		=> $config['sql']['sql_database'],
            'user'		=> $config['sql']['sql_username'],
            'password'		=> $config['sql']['sql_password'],
        ]);
        try {
            $pdo = $db->getConnection();
        } catch (Exception $e) {
            Feedback::flash('ERROR', 'DEFAULT', getError('Error connecting to DB.', $e->getMessage()));
            return false;
        }
        return $db;

    // unknown database
    } else {
        Feedback::flash('ERROR', 'DEFAULT', getError("Error: unknown database type \"{$config['db_type']}\""));
        return false;
    }

}

// connect to Jilo database
function connectJiloDB($config, $dbFile = '', $platformId = '') {
    try {
        if (!$dbFile || !file_exists($dbFile)) {
            throw new Exception(getError("Invalid platform ID \"{$platformId}\", database file \"{$dbFile}\" not found."));
        }
        $db = new Database([
            'type'		=> 'sqlite',
            'dbFile'	=> $dbFile,
        ]);
        return ['db' => $db, 'error' => null];
    } catch (Exception $e) {
        return ['db' => null, 'error' => getError('Error connecting to DB.', $e->getMessage())];
    }
}
