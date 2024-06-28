<?php

unset($error);

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} elseif (isset($_POST['page'])) {
    $page = $_POST['page'];
} else {
    $page = 'front';
}

session_start();

if ( !isset($_SESSION['user_id']) && ($page !== 'login' && $page !== 'register') ) {
    header('Location: index.php?page=login');
    exit();
}

if ( isset($_SESSION['username']) ) {
    $user = htmlspecialchars($_SESSION['username']);
}

if (isset($error)) {
    echo "<p style='color: red;'>Error: $error</p>";
}

$allowed_urls = [
    'front',
    'login',
    'logout',
    'register',
    'profile',
    'config',
];

include 'templates/header.php';

if (in_array($page, $allowed_urls)) {
    include "pages/{$page}.php";
} else {
    include 'pages/front.php';
}

include 'templates/footer.php';

?>
