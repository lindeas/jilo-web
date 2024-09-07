<?php

$action = $_REQUEST['action'] ?? '';
require '../app/classes/user.php';

$userObject = new User($dbWeb);

$userDetails = $userObject->getUserDetails($user);

switch ($action) {

    case 'edit':
        include '../app/templates/profile-edit.php';
        break;

    default:
        include '../app/templates/profile.php';
}

?>
