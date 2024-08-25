
        <!-- Menu -->
        <div class="menu-container">
            <ul class="menu-left">
                <div class="container">
                    <div class="row">
                        <a href="<?= $app_root ?>?platform=<?= $platform_id?>" class="logo-link"><div class="col-4"><img class="logo" src="<?= $app_root ?>static/jilo-logo.png" alt="JILO"/></div></a>
                    </div>
                </div>

<li class="font-weight-light text-uppercase" style="font-size: 0.5em; color: whitesmoke; margin-right: 70px; align-content: center;">version&nbsp;<?php echo $config['version']; ?></li>

<?php if ( isset($_SESSION['username']) ) { ?>

                <li style="margin-right: 3px;">
                    <a style="background-color: #111;" href="?platform=<?= htmlspecialchars(array_keys($config['platforms'])[$platform_id]) ?>&page=front">
                        <?= htmlspecialchars($config['platforms'][$platform_id]['name']) ?>
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
