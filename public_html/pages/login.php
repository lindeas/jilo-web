<?php

require_once 'classes/database.php';
require 'classes/user.php';
unset($error);

try {
    $db = new Database('./jilo-web.db');
    $user = new User($db);

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ( $user->login($username, $password) ) {
            header('Location: index.php');
            exit();
        } else {
            echo "Login failed.";
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include 'templates/form-login.php';

?>
