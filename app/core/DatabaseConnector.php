<?php

namespace App\Core;

use Exception;
use Feedback;

class DatabaseConnector
{
    /**
     * Connect to the database using given configuration and handle errors.
     *
     * @param array $config
     * @return mixed Database connection
     */
    public static function connect(array $config)
    {
        // Load DB classes
        require_once __DIR__ . '/../classes/database.php';
        require_once __DIR__ . '/../includes/database.php';

        try {
            $db = connectDB($config);
            if (!$db) {
                throw new Exception('Could not connect to database');
            }
            return $db;
        } catch (Exception $e) {
            // Show error and exit
            Feedback::flash('ERROR', 'DEFAULT', getError('Error connecting to the database.', $e->getMessage()));
            include __DIR__ . '/../templates/page-header.php';
            include __DIR__ . '/../helpers/feedback.php';
            include __DIR__ . '/../templates/page-footer.php';
            exit();
        }
    }
}
