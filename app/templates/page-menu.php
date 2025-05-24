
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
                    version&nbsp;<?= htmlspecialchars($config['version'] ?? '1.0.0') ?>
                </li>

<?php if (Session::isValidSession()) { ?>

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
<?php if (Session::isValidSession()) { ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fas fa-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h6 class="dropdown-header"><?= htmlspecialchars($currentUser) ?></h6>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=theme">
                            <i class="fas fa-paint-brush"></i>Change theme
                        </a>
                        <div class="dropdown-divider"></div>
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
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fas fa-cog"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h6 class="dropdown-header">system</h6>
<?php if ($userObject->hasRight($userId, 'superuser') ||
          $userObject->hasRight($userId, 'view config file')) {?>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=config">
                            <i class="fas fa-wrench"></i>Configuration
                        </a>
<?php   } ?>
<?php   if ($userObject->hasRight($userId, 'superuser') ||
          $userObject->hasRight($userId, 'view config file') ||
          $userObject->hasRight($userId, 'edit config file') ||
          $userObject->hasRight($userId, 'edit whitelist') ||
          $userObject->hasRight($userId, 'edit blacklist') ||
          $userObject->hasRight($userId, 'edit ratelimiting')) { ?>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=security">
                            <i class="fas fa-shield-alt"></i>Security
                        </a>
<?php   } ?>
<?php   if ($userObject->hasRight($userId, 'view app logs')) {?>
<?php do_hook('main_menu', ['app_root' => $app_root, 'section' => 'main', 'position' => 100]); ?>
<?php   } ?>
                    </div>
                </li>
<?php } else { ?>
                <li><a href="<?= htmlspecialchars($app_root) ?>?page=login">login</a></li>
<?php do_hook('main_public_menu', ['app_root' => $app_root, 'section' => 'main', 'position' => 100]); ?>
<?php } ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="fas fa-info-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <h6 class="dropdown-header">resources</h6>
                        <a class="dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=help">
                            <i class="fas fa-question-circle"></i>Help
                        </a>
                    </div>
                </li>

            </ul>
        </div>
        <!-- /Menu -->
