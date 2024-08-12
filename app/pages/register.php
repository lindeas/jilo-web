<?php

// registration is allowed, go on
if ($config['registration_enabled'] === true) {

    require_once '../app/classes/database.php';
    require '../app/classes/user.php';
    unset($error);

    try {

        // connect to database
        require '../app/helpers/database.php';
        $db = connectDB($config);

        $user = new User($db);

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // redirect to login
            if ( $user->register($username, $password) ) {
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
        $error = $e->getMessage();
    }

    include '../app/templates/block-message.php';
    include '../app/templates/form-register.php';

// registration disabled
} else {
    $notice = 'Registration is disabled';
    include '../app/templates/block-message.php';
}

?>
