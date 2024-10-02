<?php

session_name('jilo');
session_start();

$agent = $_GET['agent'];

// Check if cached data exists in session
if (isset($_SESSION["agent{$agent}_cache"])) {

    // return status, the data, and caching time - in JSON
    echo json_encode([
        'status' => 'success',
        'data' => $_SESSION["agent{$agent}_cache"],
        // we store cache time in the session
        // FIXME may need to move to file cache
        'cache_time' => $_SESSION["agent{$agent}_cache_time"] ?? time()
    ]);
} else {
    // If no cached data exists
    echo json_encode(['status' => 'error', 'message' => 'No cached data found']);
}

?>
