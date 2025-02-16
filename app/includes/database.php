<?php

// connect to database
function connectDB($config, $database = '', $dbFile = '', $platformId = '') {

    // connecting ti a jilo sqlite database
    if ($database === 'jilo') {
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

    // connecting to a jilo-web database of the web app
    } else {

        // sqlite database file
        if ($config['db']['db_type'] === 'sqlite') {
            try {
                $db = new Database([
                    'type'	=> $config['db']['db_type'],
                    'dbFile'	=> $config['db']['sqlite_file'],
                ]);
                $pdo = $db->getConnection();
                return ['db' => $db, 'error' => null];
            } catch (Exception $e) {
                return ['db' => null, 'error' => getError('Error connecting to DB.', $e->getMessage())];
            }
        // mysql/mariadb database
        } elseif ($config['db']['db_type'] === 'mysql' || $config['db']['db_type'] === 'mariadb') {
            try {
                $db = new Database([
                    'type'	=> $config['db']['db_type'],
                    'host'	=> $config['db']['sql_host'] ?? 'localhost',
                    'port'	=> $config['db']['sql_port'] ?? '3306',
                    'dbname'	=> $config['db']['sql_database'],
                    'user'	=> $config['db']['sql_username'],
                    'password'	=> $config['db']['sql_password'],
                ]);
                $pdo = $db->getConnection();
                return ['db' => $db, 'error' => null];
            } catch (Exception $e) {
                return ['db' => null, 'error' => getError('Error connecting to DB.', $e->getMessage())];
            }
        // unknown database
        } else {
            $error = "Error: unknow database type \"{$config['db']['db_type']}\"";
            Feedback::flash('ERROR', 'DEFAULT', $error);
            exit();
        }

    }

}
?>
