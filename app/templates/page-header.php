<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/bootstrap-5.3.3.min.css">
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/jquery/jquery.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/popper.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/bootstrap/bootstrap-4.0.0.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/main.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/messages.css">
    <script src="<?= htmlspecialchars($app_root) ?>static/js/messages.js"></script>
    <script>
    // restore sidebar state before the page is rendered
        (function () {
            var savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
<?php if ($page === 'logs') { ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/logs.css">
<?php } ?>
<?php if ($page === 'profile') { ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/profile.css">
<?php } ?>
<?php if ($page === 'agents') { ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/js/agents.js"></script>
<?php } ?>
<?php if ($page === 'graphs') { ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chart.umd.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/moment.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chartjs-adapter-moment.min.js"></script>
    <script src="<?= htmlspecialchars($app_root) ?>static/libs/chartjs/chartjs-plugin-zoom.min.js"></script>
<?php } ?>
    <title>Jilo Web</title>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($app_root) ?>static/favicon.ico">
</head>

<body>
    <div id="messages-container" class="container-fluid mt-2"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <?php if (isset($messages) && is_array($messages)): ?>
                    <?php foreach ($messages as $msg): ?>
                        <?= Messages::render($msg['category'], $msg['key'], $msg['custom_message'] ?? null) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
