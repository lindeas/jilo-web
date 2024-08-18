
        <!-- Menu -->
        <div class="menu-container">
            <ul class="menu-left">
                <div class="container">
                    <div class="row">
                        <a href="<?= $app_root ?>" class="logo-link"><div class="col-4"><img class="logo" src="<?= $app_root ?>static/jilo-logo.png" alt="JILO"/></div></a>
                    </div>
                </div>

<li class="font-weight-light text-uppercase" style="font-size: 0.5em; color: whitesmoke; margin-right: 70px; align-content: center;">version&nbsp;<?php echo $config['version']; ?></li>

<?php if ( isset($_SESSION['username']) ) { ?>
                <!--li><a href="?page=config">config</a></li>
                <li><a href="?page=conferences">conferences</a></li>
                <li><a href="?page=participants">participants</a></li>
                <li><a href="?page=components">components</a></li-->

                <li style="margin-right: 0px;"><a style="background-color: #111;" href="?platform=0&page=front">meet.example.com</a></li>
                <li style="margin: 0px; padding: 0px;">
                    <a style="background-color: #555; padding-left: 3px; padding-right: 3px;" href="?platform=0&page=config&action=edit">
                        <i class="fas fa-wrench" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="configuration"></i>
                    </a>
                </li>

<?php } ?>
            </ul>

            <ul class="menu-right">
<?php if ( isset($_SESSION['username']) ) { ?>
                <li><a href="<?= $app_root ?>?page=profile"><?= $user ?></a></li>
                <li><a href="<?= $app_root ?>?page=logout">logout</a></li>
<?php } else { ?>
                <li><a href="<?= $app_root ?>?page=login">login</a></li>
                <li><a href="<?= $app_root ?>?page=register">register</a></li>
<?php } ?>
            </ul>
        </div>
        <!-- /Menu -->
