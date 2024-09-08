<?php

$action = $_REQUEST['action'] ?? '';
require '../app/classes/user.php';

$userObject = new User($dbWeb);

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $userObject->getUserId($user)[0]['id'];

    // update the profile
    $updatedUser = [
            'name'		=> $_POST['name'] ?? '',
            'email'		=> $_POST['email'] ?? '',
//            'avatar'		=> ,
            'bio'		=> $_POST['bio'] ?? '',
        ];
        $result = $userObject->editUser($user_id, $updatedUser);
        if ($result === true) {
            $_SESSION['notice'] = "User details for \"{$updatedUser['name']}\" are edited.";
        } else {
            $_SESSION['error'] = "Editing the user details failed. Error: $result";
        }

    header("Location: $app_root?page=profile");
    exit();

// no form submitted, show the templates
} else {
    $userDetails = $userObject->getUserDetails($user);

    switch ($action) {

        case 'edit':
            include '../app/templates/profile-edit.php';
            break;

        default:
            include '../app/templates/profile.php';
    }
}

?>
