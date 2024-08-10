<?php

// connect to database
function connectDB($config,$database) {

    if ($database === 'jilo') {
        try {
            $db = new Database([
                'type'		=> 'sqlite',
                'dbFile'	=> $config['jilo_database'],
            ]);
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
            include 'templates/block-message.php';
            exit();
        }

    } else {

        // sqlite database file
        if ($config['db_type'] === 'sqlite') {
            try {
                $db = new Database([
                    'type'	=> $config['db_type'],
                    'dbFile'	=> $config['sqlite_file'],
                ]);
                $pdo = $db->getConnection();
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
                include 'templates/block-message.php';
                exit();
            }
        // mysql/mariadb database
        } elseif ($config['db_type'] === 'mysql' || $config['db_type'] === 'mariadb') {
            try {
                $db = new Database([
                    'type'	=> $config['db_type'],
                    'host'	=> $config['sql_host'] ?? 'localhost',
                    'port'	=> $config['sql_port'] ?? '3306',
                    'dbname'	=> $config['sql_database'],
                    'user'	=> $config['sql_username'],
                    'password'	=> $config['sql_password'],
                ]);
                $pdo = $db->getConnection();
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
                include 'templates/block-message.php';
                exit();
            }
        // unknown database
        } else {
            $error = "Error: unknow database type \"{$config['db_type']}\"";
            include 'templates/block-message.php';
            exit();
        }

    }

    return $db;
}
?>
