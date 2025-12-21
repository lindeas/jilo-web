
    <div class="container-fluid p-0">

        <!-- Modern Menu -->
        <div class="menu-container">
            <div class="modern-header-content">
                <div class="logo-section">
                    <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>" class="modern-logo-link">
                        <div class="modern-logo">
                            <img src="<?= htmlspecialchars($app_root) ?>static/jilo-logo.png" alt="<?= htmlspecialchars($config['site_name']); ?>"/>
                        </div>
                        <div class="brand-info">
                            <h1 class="brand-name"><?= htmlspecialchars($config['site_name']); ?></h1>
                            <?php if (!empty($config['site_slogan'])): ?>
                                <div class="brand-slogan"><?= htmlspecialchars($config['site_slogan']); ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>

<?php if (Session::isValidSession()) { ?>

<?php foreach ($platformsAll as $platform) {
    $platform_switch_url = switchPlatform($platform['id']);
?>
                <div>
<?php if ((isset($_REQUEST['platform']) || empty($_SERVER['QUERY_STRING'])) && $platform['id'] == $platform_id) { ?>
                    Jitsi platforms: 
                    <button class="btn modern-header-btn" type="button" aria-expanded="false">
                        <?= htmlspecialchars($platform['name']) ?>
                    </button>
<?php     } else { ?>
                    <a href="<?= htmlspecialchars($platform_switch_url) ?>">
                        <?= htmlspecialchars($platform['name']) ?>
                    </a>
<?php     } ?>
                </div>
<?php   } ?>

<?php } ?>

                <div class="header-actions">
<?php if (Session::isValidSession()) { ?>
                    <div class="dropdown">
                        <button class="btn modern-header-btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($currentUser) ?>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right modern-dropdown">
                            <h6 class="dropdown-header modern-dropdown-header"><?= htmlspecialchars($currentUser) ?></h6>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=theme">
                                <i class="fas fa-paint-brush"></i>Change theme
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=profile">
                                <i class="fas fa-id-card"></i>Profile details
                            </a>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=credentials">
                                <i class="fas fa-shield-alt"></i>Login credentials
                            </a>
<?php do_hook('account_menu', ['app_root' => $app_root]); ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=logout">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button class="btn modern-header-btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right modern-dropdown">
                            <h6 class="dropdown-header modern-dropdown-header">settings</h6>
<?php if ($userObject->hasRight($userId, 'superuser')) {?>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=admin-tools">
                                <i class="fas fa-toolbox"></i>Admin tools
                            </a>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=admin">
                                <i class="fas fa-toolbox"></i>Admin
                            </a>
<?php } ?>
<?php if ($userObject->hasRight($userId, 'superuser') ||
          $userObject->hasRight($userId, 'view config file')) {?>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=config">
                                <i class="fas fa-wrench"></i>Configuration
                            </a>
<?php } ?>
<?php if ($userObject->hasRight($userId, 'superuser') ||
          $userObject->hasRight($userId, 'view config file') ||
          $userObject->hasRight($userId, 'edit config file') ||
          $userObject->hasRight($userId, 'edit whitelist') ||
          $userObject->hasRight($userId, 'edit blacklist') ||
          $userObject->hasRight($userId, 'edit ratelimiting')) { ?>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=security">
                                <i class="fas fa-shield-alt"></i>Security
                            </a>
<?php do_hook('main_menu', ['app_root' => $app_root, 'section' => 'main', 'position' => 100]); ?>
                        </div>
                    </div>
<?php } ?>
<?php } else { ?>
                    <button class="btn modern-header-btn" onclick="window.location.href='<?= htmlspecialchars($app_root) ?>?page=login'">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
<?php do_hook('main_public_menu', ['app_root' => $app_root, 'section' => 'main', 'position' => 100]); ?>
<?php } ?>

                    <div class="dropdown">
                        <button class="btn modern-header-btn dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right modern-dropdown">
                            <h6 class="dropdown-header modern-dropdown-header">resources</h6>
                            <a class="dropdown-item modern-dropdown-item" href="<?= htmlspecialchars($app_root) ?>?page=help">
                                <i class="fas fa-question-circle"></i>Help
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modern Menu -->
