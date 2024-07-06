<?php

require_once 'classes/database.php';
require 'classes/user.php';
unset($error);

try {
    $db = new Database($config['database']);
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

include 'templates/message.php';
include 'templates/form-register.php';

?>
