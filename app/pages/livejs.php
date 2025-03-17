<?php

$mode = $_REQUEST['mode'] ?? '';
$raw = ($mode === 'raw');
$livejsFile = $_REQUEST['item'] ?? '';

require '../app/classes/settings.php';
$settingsObject = new Settings();

$livejsData = $settingsObject->getPlatformJsFile($platformDetails[0]['jitsi_url'], $item, $raw);

// Get any new feedback messages
include '../app/helpers/feedback.php';

// Load the template
include '../app/templates/livejs.php';
