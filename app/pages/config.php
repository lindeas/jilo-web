<?php

$action = $_REQUEST['action'] ?? '';

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // load the config file and initialize a copy
    $content = file_get_contents($config_file);
    $updatedContent = $content;

    foreach ($_POST as $key => $value) {

        $config['platforms'][$platform_id][$key] = $value;
        // search pattern for the old value
        $oldValue = "/('$platform_id'\s*=>\s*\[\s*'$key'\s*=>\s*)'[^']*'/";
        // new value
        $newValue = "$1'$value'";

        $updatedContent = preg_replace($oldValue, $newValue, $updatedContent);
    }

    if (file_put_contents($config_file, $updatedContent) !== false) {
        // update successful
        $_SESSION['notice'] = "Configuration for {$_POST['name']} is updated.";
    } else {
        // unsuccessful
        $_SESSION['error'] = 'Error updating the config';
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
