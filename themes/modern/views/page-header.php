<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['site_name'] ?? '') ?> - <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/bootstrap-5.3.3.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/main.css">

    <!-- Theme-specific CSS -->
    <link rel="stylesheet" type="text/css" href="<?= \App\Helpers\Theme::asset('css/theme.css') ?>">

    <!-- jQuery -->
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/jquery/jquery.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/popper.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/bootstrap-4.0.0.min.js"></script>

    <?php if (Session::getUsername()): ?>
    <script>
    // Restore sidebar state before the page is rendered
    (function() {
        var savedState = localStorage.getItem('sidebarState');
        if (savedState === 'collapsed') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    })();
    </script>
    <?php endif; ?>

    <!-- Page-specific CSS -->
    <?php if ($page === 'logs'): ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/logs.css">
    <?php endif; ?>

    <?php if ($page === 'profile'): ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/profile.css">
    <?php endif; ?>

    <?php if ($page === 'agents'): ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/js/agents.js"></script>
    <?php endif; ?>

    <?php if ($page === 'graphs'): ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chart.umd.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/moment.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chartjs-adapter-moment.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chartjs-plugin-zoom.min.js"></script>
    <?php endif; ?>

    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($app_root) ?>static/favicon.ico">
</head>

<body class="modern-theme">
    <div id="messages-container" class="container-fluid mt-2"></div>

    <?php if (isset($system_messages) && is_array($system_messages)): ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <?php foreach ($system_messages as $msg): ?>
                    <?= Feedback::render($msg['category'], $msg['key'], $msg['custom_message'] ?? null) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
