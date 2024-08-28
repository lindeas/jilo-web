<?php

$action = $_REQUEST['action'] ?? '';
require_once '../app/classes/config.php';
require '../app/helpers/errors.php';
require '../app/helpers/config.php';

$configure = new Config();

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // load the config file and initialize a copy
    $content = file_get_contents($config_file);
    $updatedContent = $content;

    // new platform adding
    if (isset($_POST['new']) && $_POST['new'] === 'true') {
        $newPlatform = [
            'name'		=> $_POST['name'],
            'jitsi_url'		=> $_POST['jitsi_url'],
            'jilo_database'	=> $_POST['jilo_database'],
        ];

        // Determine the next available index for the new platform
        $nextIndex = count($config['platforms']);

        // Add the new platform to the platforms array
        $config['platforms'][$nextIndex] = $newPlatform;

        // Rebuild the PHP array syntax for the platforms
        $platformsArray = formatArray($config['platforms']);

        // Replace the platforms section in the config file
        $updatedContent = preg_replace(
            '/\'platforms\'\s*=>\s*\[[\s\S]+?\],/s',
            "'platforms' => {$platformsArray}",
            $content
        );
        $updatedContent = preg_replace('/\s*\]\n/s', "\n", $updatedContent);

    // deleting a platform
    } elseif (isset($_POST['delete']) && $_POST['delete'] === 'true') {
        $platform = $_POST['platform'];

        $config['platforms'][$platform]['name'] = $_POST['name'];
        $config['platforms'][$platform]['jitsi_url'] = $_POST['jitsi_url'];
        $config['platforms'][$platform]['jilo_database'] = $_POST['jilo_database'];

        $platformsArray = formatArray($config['platforms'][$platform], 3);

        $updatedContent = preg_replace(
            "/\s*'$platform'\s*=>\s*\[\s*'name'\s*=>\s*'[^']*',\s*'jitsi_url'\s*=>\s*'[^']*,\s*'jilo_database'\s*=>\s*'[^']*',\s*\],/s",
            "",
            $content
        );


    // an update to an existing platform
    } else {

        $platform = $_POST['platform'];

        $config['platforms'][$platform]['name'] = $_POST['name'];
        $config['platforms'][$platform]['jitsi_url'] = $_POST['jitsi_url'];
        $config['platforms'][$platform]['jilo_database'] = $_POST['jilo_database'];

        $platformsArray = formatArray($config['platforms'][$platform], 3);

        $updatedContent = preg_replace(
            "/\s*'$platform'\s*=>\s*\[\s*'name'\s*=>\s*'[^']*',\s*'jitsi_url'\s*=>\s*'[^']*',\s*'jilo_database'\s*=>\s*'[^']*',\s*\],/s",
            "\n        '{$platform}' => {$platformsArray},",
            $content
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
    switch ($item) {
        case 'configjs':
            $platformDetails = $configure->getPlatformDetails($config, $platform_id);
            $platformConfigjs = $configure->getPlatformConfigjs($platformDetails);
            include('../app/templates/config-list-configjs.php');
            break;
        case 'interfaceconfigjs':
            $platformDetails = $configure->getPlatformDetails($config, $platform_id);
            $platformInterfaceConfigjs = $configure->getPlatformInterfaceConfigjs($platformDetails);
            include('../app/templates/config-list-interfaceconfigjs.php');
            break;

        default:
            switch ($action) {
                case 'add':
                    include('../app/templates/config-add-platform.php');
                    break;
                case 'edit':
                    include('../app/templates/config-edit-platform.php');
                    break;
                case 'delete':
                    include('../app/templates/config-delete-platform.php');
                    break;
                default:
                    include('../app/templates/config-list.php');
            }
    }
}

?>
