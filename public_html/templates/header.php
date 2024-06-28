<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="static/all.css">
    <title>Jilo Web</title>
</head>

<body>

<div class="menu-container">
    <ul class="menu-left">
        <li><a href="index.php">home</a></li>
<?php if ( isset($_SESSION['user_id']) ) { ?>
        <li><a href="?page=config">config</a></li>
<?php } ?>
    </ul>

    <ul class="menu-right">
<?php if ( isset($_SESSION['user_id']) ) { ?>
        <li><a href="?page=profile"><?= $user ?></a></li>
        <li><a href="?page=logout">logout</a></li>
<?php } else { ?>
        <li><a href="?page=login">login</a></li>
        <li><a href="?page=register">register</a></li>
<?php } ?>
    </ul>
</div>
