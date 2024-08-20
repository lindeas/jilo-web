<?php

$action = $_REQUEST['action'] ?? '';
require '../app/helpers/errors.php';

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // load the config file and initialize a copy
    $content = file_get_contents($config_file);
    $updatedContent = $content;

    foreach ($_POST as $key => $value) {
        // Create a regex pattern to match the key-value pair for the specified platform ID
        $pattern = "/((?:'[^']+'\s*=>\s*'[^']+'\s*,?\s*)*)('{$key}'\s*=>\s*)'[^']*'/s";
        // Replace using a callback to handle the match and replacement
        $updatedContent = preg_replace_callback($pattern, function($matches) use ($value) {
                return $matches[1] . $matches[2] . "'{$value}'";
            }, $updatedContent
        );
    }

    // check if file is writable
    if (!is_writable($config_file)) {
        $_SESSION['error'] = getError('Configuration file is not writable.');
        header("Location: $app_root?platform=$platform_id&page=config");
        exit();
    }

    // try to update the config file
    if (file_put_contents($config_file, $updatedContent) !== false) {
        // update successful
        $_SESSION['notice'] = "Configuration for {$_POST['name']} is updated.";
    } else {
        // unsuccessful
        $error = error_get_last();
        $_SESSION['error'] = getError('Error updating the config: ' . ($error['message'] ?? 'unknown error'));
    }

// FIXME the new file is not loaded on first page load
    unset($config);
    header("Location: $app_root?platform=$platform_id&page=config");
    exit();

// no form submitted, show the templates
} else {
    switch ($action) {
        case 'edit':
            include('../app/templates/config-edit-platform.php');
            break;
        default:
            include('../app/templates/config-list.php');
    }
}

?>
