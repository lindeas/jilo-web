<?php

/**
 * User profile management
 *
 * This page ("profile") handles user profile actions such as updating user details,
 * avatar management, and assigning or removing user rights.
 * It supports both form submissions and displaying profile templates.
 *
 * Actions handled:
 * - `remove`: Remove a user's avatar.
 * - `edit`: Edit user profile details, rights, or avatar.
 */

// Get any new messages
include '../app/includes/messages.php';
include '../app/includes/messages-show.php';

$action = $_REQUEST['action'] ?? '';

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $item = $_REQUEST['item'] ?? '';

    // avatar removal
    if ($item === 'avatar' && $action === 'remove') {
        $result = $userObject->removeAvatar($user_id, $config['avatars_path'].$userDetails[0]['avatar']);
        if ($result === true) {
            $_SESSION['notice'] .= "Avatar for user \"{$userDetails[0]['username']}\" is removed. ";
        } else {
            $_SESSION['error'] .= "Removing the avatar failed. Error: $result ";
        }

        header("Location: $app_root?page=profile");
        exit();
    }

    // update the profile
    $updatedUser = [
            'name'		=> $_POST['name'] ?? '',
            'email'		=> $_POST['email'] ?? '',
            'timezone'		=> $_POST['timezone'] ?? '',
            'bio'		=> $_POST['bio'] ?? '',
        ];
    $result = $userObject->editUser($user_id, $updatedUser);
    if ($result === true) {
        $_SESSION['notice'] .= "User details for \"{$updatedUser['name']}\" are edited. ";
    } else {
        $_SESSION['error'] .= "Editing the user details failed. Error: $result ";
    }

    // update the rights
    $newRights = $_POST['rights'] ?? array();
    // extract the new right_ids
    $userRightsIds = array_column($userRights, 'right_id');
    // what rights we need to add
    $rightsToAdd = array_diff($newRights, $userRightsIds);
    if (!empty($rightsToAdd)) {
        foreach ($rightsToAdd as $rightId) {
            $userObject->addUserRight($user_id, $rightId);
        }
    }
    // what rights we need to remove
    $rightsToRemove = array_diff($userRightsIds, $newRights);
    if (!empty($rightsToRemove)) {
        foreach ($rightsToRemove as $rightId) {
            $userObject->removeUserRight($user_id, $rightId);
        }
    }

    // update the avatar
    if (!empty($_FILES['avatar_file']['tmp_name'])) {
        $result = $userObject->changeAvatar($user_id, $_FILES['avatar_file'], $config['avatars_path']);
    }

    header("Location: $app_root?page=profile");
    exit();

// no form submitted, show the templates
} else {
    $avatar = !empty($userDetails[0]['avatar']) ? $config['avatars_path'] . $userDetails[0]['avatar'] : $config['default_avatar'];
    $default_avatar = empty($userDetails[0]['avatar']) ? true : false;

    switch ($action) {

        case 'edit':
            $allRights = $userObject->getAllRights();
            $allTimezones = timezone_identifiers_list();
            // if timezone is already set, we pass a flag for JS to not autodetect browser timezone
            $isTimezoneSet = !empty($userDetails[0]['timezone']);
            include '../app/templates/profile-edit.php';
            break;

        default:
            include '../app/templates/profile.php';
    }
}

?>
