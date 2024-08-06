
        <!-- Menu -->
        <div class="menu-container">
            <ul class="menu-left">
                <div class="container">
                    <div class="row">
                        <a href="index.php" class="logo-link"><div class="col-4"><img class="logo" src="static/jilo-logo.png" alt="JILO"/></div></a>
                    </div>
                </div>

<?php if ( isset($_SESSION['username']) ) { ?>
                <li><a href="?page=config">config</a></li>
                <li><a href="?page=conferences">conferences</a></li>
                <li><a href="?page=participants">participants</a></li>
                <li><a href="?page=components">components</a></li>
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
        <!-- /Menu -->
