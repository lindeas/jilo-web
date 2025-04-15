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
$item = $_REQUEST['item'] ?? '';

// if a form is submitted, it's from the edit page
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate CSRF token
    require_once '../app/helpers/security.php';
    $security = SecurityHelper::getInstance();
    if (!$security->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        Feedback::flash('ERROR', 'DEFAULT', 'Invalid security token. Please try again.');
        header("Location: $app_root?page=profile");
        exit();
    }

    require_once '../app/classes/validator.php';

    // Apply rate limiting for profile operations
    require_once '../app/includes/rate_limit_middleware.php';
    checkRateLimit($dbWeb, 'profile', $userId);

    // avatar removal
    if ($item === 'avatar' && $action === 'remove') {
        $validator = new Validator(['user_id' => $userId]);
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

        $result = $userObject->removeAvatar($userId, $config['avatars_path'].$userDetails[0]['avatar']);
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
    $result = $userObject->editUser($userId, $updatedUser);
    if ($result === true) {
        Feedback::flash('NOTICE', 'DEFAULT', "User details for \"{$userDetails[0]['username']}\" are edited.");
    } else {
        Feedback::flash('ERROR', 'DEFAULT', "Editing the user details failed. Error: $result");
    }

    // update the rights
    // Get current rights IDs
    $userRightsIds = array_column($userRights, 'right_id');

    // If no rights are selected, remove all rights
    if (!isset($_POST['rights'])) {
        $_POST['rights'] = [];
    }

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
    // what rights we need to add
    $rightsToAdd = array_diff($newRights, $userRightsIds);
    if (!empty($rightsToAdd)) {
        foreach ($rightsToAdd as $rightId) {
            $userObject->addUserRight($userId, $rightId);
        }
    }
    // what rights we need to remove
    $rightsToRemove = array_diff($userRightsIds, $newRights);
    if (!empty($rightsToRemove)) {
        foreach ($rightsToRemove as $rightId) {
            $userObject->removeUserRight($userId, $rightId);
        }
    }

    // update the avatar
    if (!empty($_FILES['avatar_file']['tmp_name'])) {
        $result = $userObject->changeAvatar($userId, $_FILES['avatar_file'], $config['avatars_path']);
    }

    header("Location: $app_root?page=profile");
    exit();

// no form submitted, show the templates
} else {
    $avatar = !empty($userDetails[0]['avatar']) ? $config['avatars_path'] . $userDetails[0]['avatar'] : $config['default_avatar'];
    $default_avatar = empty($userDetails[0]['avatar']) ? true : false;

    // Generate CSRF token if not exists
    require_once '../app/helpers/security.php';
    $security = SecurityHelper::getInstance();
    $security->generateCsrfToken();

    switch ($action) {
        case 'edit':
            $allRights = $userObject->getAllRights();
            $allTimezones = timezone_identifiers_list();
            // if timezone is already set, we pass a flag for JS to not autodetect browser timezone
            $isTimezoneSet = !empty($userDetails[0]['timezone']);

            // Get any new feedback messages
            include '../app/helpers/feedback.php';

            // Load the template
            include '../app/templates/profile-edit.php';
            break;

        default:
            // Get any new feedback messages
            include '../app/helpers/feedback.php';

            // Load the template
            include '../app/templates/profile.php';
    }
}
