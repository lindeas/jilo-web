<?php

require '../app/classes/user.php';

$userObject = new User($dbWeb);

$userDetails = $userObject->getUserDetails($user);

include '../app/templates/profile.php';


?>
