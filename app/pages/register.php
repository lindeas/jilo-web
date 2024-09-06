<?php

// registration is allowed, go on
if ($config['registration_enabled'] === true) {

    require '../app/classes/user.php';
    unset($error);

    try {

        // connect to database
        $dbWeb = connectDB($config);

        $userObject = new User($dbWeb);

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // redirect to login
            if ( $userObject->register($username, $password) ) {
                $_SESSION['notice'] = "Registration successful.<br />You can log in now.";
                header('Location: index.php');
                exit();
            // registration fail, redirect to login
            } else {
                $_SESSION['error'] = "Registration failed.";
                header('Location: index.php');
                exit();
            }
        }
    } catch (Exception $e) {
        $error = getError('There was an unexpected error. Please try again.', $e->getMessage());
    }

    include '../app/templates/block-message.php';
    include '../app/templates/form-register.php';

// registration disabled
} else {
    $notice = 'Registration is disabled';
    include '../app/templates/block-message.php';
}

?>
