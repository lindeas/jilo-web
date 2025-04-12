
    <div class="container-fluid">

        <!-- Menu -->
        <div class="menu-container">
            <ul class="menu-left">
                <div class="container">
                    <div class="row">
                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>" class="logo-link">
                            <div class="col-4">
                                <img class="logo" src="<?= htmlspecialchars($app_root) ?>static/jilo-logo.png" alt="JILO"/>
                            </div>
                        </a>
                    </div>
                </div>

                <li class="font-weight-light text-uppercase" style="font-size: 0.5em; color: whitesmoke; margin-right: 70px; align-content: center;">
                    version&nbsp;<?= htmlspecialchars($config['version']) ?>
                </li>

<?php if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) { ?>

<?php foreach ($platformsAll as $platform) {
    $platform_switch_url = switchPlatform($platform['id']);
?>
                <li style="margin-right: 3px;">
<?php if ((isset($_REQUEST['platform']) || empty($_SERVER['QUERY_STRING'])) && $platform['id'] == $platform_id) { ?>
                    <span style="background-color: #fff; border: 1px solid #111; color: #111; border-bottom-color: #fff; padding-bottom: 12px;">
                        <?= htmlspecialchars($platform['name']) ?>
                    </span>
<?php     } else { ?>
                    <a href="<?= htmlspecialchars($platform_switch_url) ?>">
                        <?= htmlspecialchars($platform['name']) ?>
                    </a>
<?php     } ?>
                </li>
<?php   } ?>

<?php } ?>
            </ul>

            <ul class="menu-right">
<?php if (isset($_SESSION['username']) && isset($_SESSION['user_id'])) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h6 class="dropdown-header"><?= htmlspecialchars($currentUser) ?></h6>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=profile">
                            <i class="fas fa-id-card"></i>Profile details
                        </a>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=credentials">
                            <i class="fas fa-shield-alt"></i>Login credentials
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=logout">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </div>
                </li>
<?php } else { ?>
                <li><a href="<?= htmlspecialchars($app_root) ?>?page=login">login</a></li>
                <li><a href="<?= htmlspecialchars($app_root) ?>?page=register">register</a></li>
<?php } ?>
            </ul>
        </div>
        <!-- /Menu -->
