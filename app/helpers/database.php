<?php

// connect to database
function connectDB($config, $database = '') {

    if ($database === 'jilo') {
        try {
            $db = new Database([
                'type'		=> 'sqlite',
                'dbFile'	=> $config['jilo_database'],
            ]);
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
            include '../app/templates/block-message.php';
            exit();
        }

    } else {

        // sqlite database file
        if ($config['db']['db_type'] === 'sqlite') {
            try {
                $db = new Database([
                    'type'	=> $config['db']['db_type'],
                    'dbFile'	=> $config['db']['sqlite_file'],
                ]);
                $pdo = $db->getConnection();
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
                include '../app/templates/block-message.php';
                exit();
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
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
                include '../app/templates/block-message.php';
                exit();
            }
        // unknown database
        } else {
            $error = "Error: unknow database type \"{$config['db']['db_type']}\"";
            include '../app/templates/block-message.php';
            exit();
        }

    }

    return $db;
}
?>
