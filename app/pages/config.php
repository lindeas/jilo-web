<?php

$action = $_REQUEST['action'] ?? '';
require '../app/classes/config.php';

$configObject = new Config();

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

// FIXME - if editing the flat file is no more needed, remove this
//    // load the config file and initialize a copy
//    $content = file_get_contents($config_file);
//    $updatedContent = $content;

    // new platform adding
    if (isset($_POST['new']) && $_POST['new'] === 'true') {
        $newPlatform = [
            'name'		=> $_POST['name'],
            'jitsi_url'		=> $_POST['jitsi_url'],
            'jilo_database'	=> $_POST['jilo_database'],
        ];
        $platformObject->addPlatform($newPlatform);

    // deleting a platform
    } elseif (isset($_POST['delete']) && $_POST['delete'] === 'true') {
        $platform = $_POST['platform'];
        $platformObject->deletePlatform($platform);

    // an update to an existing platform
    } else {
        $platform = $_POST['platform'];
        $updatedPlatform = [
            'name'		=> $_POST['name'],
            'jitsi_url'		=> $_POST['jitsi_url'],
            'jilo_database'	=> $_POST['jilo_database'],
        ];
        $platformObject->editPlatform($platform, $updatedPlatform);

    }

// FIXME - if this is not needed for editing the flat file, remove it
//    // check if file is writable
//    if (!is_writable($config_file)) {
//        $_SESSION['error'] = getError('Configuration file is not writable.');
//        header("Location: $app_root?platform=$platform_id&page=config");
//        exit();
//    }
//
//    // try to update the config file
//    if (file_put_contents($config_file, $updatedContent) !== false) {
//        // update successful
//        $_SESSION['notice'] = "Configuration for {$_POST['name']} is updated.";
//    } else {
//        // unsuccessful
//        $error = error_get_last();
//        $_SESSION['error'] = getError('Error updating the config: ' . ($error['message'] ?? 'unknown error'));
//    }

// FIXME the new file is not loaded on first page load
    unset($config);
    header("Location: $app_root?platform=$platform_id&page=config");
    exit();

// no form submitted, show the templates
} else {

    // $item - config.js and interface_config.js are special case; remote loaded files
    switch ($item) {
        case 'configjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformConfigjs = $configObject->getPlatformConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/config-list-configjs.php';
            break;
        case 'interfaceconfigjs':
            $mode = $_REQUEST['mode'] ?? '';
            $raw = ($mode === 'raw');
            $platformInterfaceConfigjs = $configObject->getPlatformInterfaceConfigjs($platformDetails[0]['jitsi_url'], $raw);
            include '../app/templates/config-list-interfaceconfigjs.php';
            break;

    // if there is no $item, we work on the local config file
        default:
            switch ($action) {
                case 'add':
                    include '../app/templates/config-add-platform.php';
                    break;
                case 'edit':
                    include '../app/templates/config-edit-platform.php';
                    break;
                case 'delete':
                    include '../app/templates/config-delete-platform.php';
                    break;
                default:
                    if ($userObject->hasRight($user_id, 'view config file')) {
                        require '../app/classes/agent.php';
                        $agentObject = new Agent($dbWeb);
                        include '../app/templates/config-list.php';
                    } else {
                        include '../app/templates/unauthorized.php';
                    }
            }
    }
}

?>
