<?php

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'edit':
        include('../app/templates/config-edit-platform.php');
        break;

    default:
        include('../app/templates/config-list.php');
}

?>
