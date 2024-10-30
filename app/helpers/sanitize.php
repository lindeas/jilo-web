<?php

// sanitize all input vars that may end up in URLs or forms

$platform_id = htmlspecialchars($_REQUEST['platform']);
if (isset($_REQUEST['page'])) {
    $page = htmlspecialchars($_REQUEST['page']);
} else {
    $page = 'dashboard';
}
if (isset($_REQUEST['item'])) {
    $item = htmlspecialchars($_REQUEST['item']);
} else {
    $item = '';
}

if (isset($_REQUEST['from_time'])) {
    $from_time = htmlspecialchars($_REQUEST['from_time']);
}
if (isset($_REQUEST['until_time'])) {
    $until_time = htmlspecialchars($_REQUEST['until_time']);
}

if (isset($_SESSION['notice'])) {
    $notice = htmlspecialchars($_SESSION['notice']); // 'notice' for all non-critical messages
}
if (isset($_SESSION['error'])) {
    $error = htmlspecialchars($_SESSION['error']); // 'error' for errors
}

// hosts
if (isset($_POST['address'])) {
    $address = htmlspecialchars($_POST['address']);
}
if (isset($_POST['port'])) {
    $port = htmlspecialchars($_POST['port']);
}
if (isset($_POST['name'])) {
    $name = htmlspecialchars($_POST['name']);
}

// agents
if (isset($_POST['type'])) {
    $type = htmlspecialchars($_POST['type']);
}
if (isset($_POST['url'])) {
    $url = htmlspecialchars($_POST['url']);
}
if (isset($_POST['secret_key'])) {
    $secret_key = htmlspecialchars($_POST['secret_key']);
}
if (isset($_POST['check_period'])) {
    $check_period = htmlspecialchars($_POST['check_period']);
}

// platforms
if (isset($_POST['name'])) {
    $name = htmlspecialchars($_POST['name']);
}


?>
