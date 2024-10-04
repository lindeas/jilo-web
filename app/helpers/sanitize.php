<?php
// sanitize all vars that may end up in URLs or forms

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
if (isset($_SESSION['notice'])) {
    $notice = htmlspecialchars($_SESSION['notice']); // 'notice' for all non-critical messages
}
if (isset($_SESSION['error'])) {
    $error = htmlspecialchars($_SESSION['error']); // 'error' for errors
}
if (isset($_REQUEST['from_time'])) {
    $from_time = htmlspecialchars($_REQUEST['from_time']);
}
if (isset($_REQUEST['until_time'])) {
    $until_time = htmlspecialchars($_REQUEST['until_time']);
}


?>
