
<div class="menu-container">
    <ul class="menu-left">
        <li><a href="index.php">home</a></li>
<?php if ( isset($_SESSION['username']) ) { ?>
        <li><a href="?page=config">config</a></li>
        <li><a href="?page=conferences">conferences</a></li>
<?php } ?>
    </ul>

    <ul class="menu-right">
<?php if ( isset($_SESSION['username']) ) { ?>
        <li><a href="?page=profile"><?= $user ?></a></li>
        <li><a href="?page=logout">logout</a></li>
<?php } else { ?>
        <li><a href="?page=login">login</a></li>
        <li><a href="?page=register">register</a></li>
<?php } ?>
    </ul>
</div>
