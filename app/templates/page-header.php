<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/main.css">
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/messages.css">
<?php if ($page === 'logs') { ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/logs.css">
<?php } ?>
<?php if ($page === 'profile') { ?>
    <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($app_root) ?>static/css/profile.css">
<?php } ?>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <script>
    // restore sidebar state before the page is rendered
        (function () {
            var savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
<?php if ($page === 'agents') { ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/agents.js"></script>
<?php } ?>
<?php if ($page === 'data' && $item === 'graphs') { ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js"></script>
<?php } ?>
    <title>Jilo Web</title>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($app_root) ?>static/favicon.ico">
</head>

<body>
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
