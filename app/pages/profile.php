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

$action = $_REQUEST['action'] ?? '';

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require_once '../app/classes/validator.php';

    // Apply rate limiting for profile operations
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($db, 'profile', $user_id);

    $item = $_REQUEST['item'] ?? '';

    // avatar removal
    if ($item === 'avatar' && $action === 'remove') {
        $validator = new Validator(['user_id' => $user_id]);
        $rules = [
            'user_id' => [
                'required' => true,
                'numeric' => true
            ]
        ];

        if (!$validator->validate($rules)) {
            Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
            header("Location: $app_root?page=profile");
            exit();
        }

        $result = $userObject->removeAvatar($user_id, $config['avatars_path'].$userDetails[0]['avatar']);
        if ($result === true) {
            Feedback::flash('NOTICE', 'DEFAULT', "Avatar for user \"{$userDetails[0]['username']}\" is removed.");
        } else {
            Feedback::flash('ERROR', 'DEFAULT', "Removing the avatar failed. Error: $result");
        }

        header("Location: $app_root?page=profile");
        exit();
    }

    // update the profile
    $validator = new Validator($_POST);
    $rules = [
        'name' => [
            'max' => 100
        ],
        'email' => [
            'email' => true,
            'max' => 100
        ],
        'timezone' => [
            'max' => 50
        ],
        'bio' => [
            'max' => 1000
        ]
    ];

    if (!$validator->validate($rules)) {
        Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
        header("Location: $app_root?page=profile");
        exit();
    }

    $updatedUser = [
        'name' => htmlspecialchars($_POST['name'] ?? ''),
        'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
        'timezone' => htmlspecialchars($_POST['timezone'] ?? ''),
        'bio' => htmlspecialchars($_POST['bio'] ?? ''),
    ];
    $result = $userObject->editUser($user_id, $updatedUser);
    if ($result === true) {
        Feedback::flash('NOTICE', 'DEFAULT', "User details for \"{$updatedUser['name']}\" are edited.");
    } else {
        Feedback::flash('ERROR', 'DEFAULT', "Editing the user details failed. Error: $result");
    }

    // update the rights
    if (isset($_POST['rights'])) {
        $validator = new Validator(['rights' => $_POST['rights']]);
        $rules = [
            'rights' => [
                'array' => true
            ]
        ];

        if (!$validator->validate($rules)) {
            Feedback::flash('ERROR', 'DEFAULT', $validator->getFirstError());
            header("Location: $app_root?page=profile");
            exit();
        }

        $newRights = $_POST['rights'];
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

            // Get any new feedback messages
            include '../app/includes/feedback-get.php';
            include '../app/includes/feedback-show.php';

            // Load the template
            include '../app/templates/profile-edit.php';
            break;

        default:
            // Get any new feedback messages
            include '../app/includes/feedback-get.php';
            include '../app/includes/feedback-show.php';

            // Load the template
            include '../app/templates/profile.php';
    }
}

?>
