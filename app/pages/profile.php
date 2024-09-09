<?php

$action = $_REQUEST['action'] ?? '';
require '../app/classes/user.php';

$userObject = new User($dbWeb);

$userDetails = $userObject->getUserDetails($user);

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $user_id = $userObject->getUserId($user)[0]['id'];

    $item = $_REQUEST['item'] ?? '';

    // avatar editing
    if ($item === 'avatar') {
        switch ($action) {
            case 'remove':
                $result = $userObject->removeAvatar($user_id, $config['avatars_path'].$userDetails[0]['avatar']);
                if ($result === true) {
                    $_SESSION['notice'] = "Avatar for user \"{$user}\" is removed.";
                } else {
                    $_SESSION['error'] = "Removing the avatar failed. Error: $result";
                }
                break;
            case 'edit':
                $result = $userObject->changeAvatar($user_id, $_FILES['avatar_file'], $config['avatars_path']);
                break;
            default:
                $_SESSION['error'] = "Unspecified avatar editing action.";
        }

        header("Location: $app_root?page=profile");
        exit();
    }

    // update the profile
    $updatedUser = [
            'name'		=> $_POST['name'] ?? '',
            'email'		=> $_POST['email'] ?? '',
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
    $avatar = !empty($userDetails[0]['avatar']) ? $config['avatars_path'] . $userDetails[0]['avatar'] : $config['default_avatar'];

    switch ($action) {

        case 'edit':
            include '../app/templates/profile-edit.php';
            break;

        default:
            include '../app/templates/profile.php';
    }
}

?>
