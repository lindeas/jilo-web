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
<?php  if (Session::getUsername()) { ?>
    <script>
    // restore sidebar state before the page is rendered
        (function () {
            var savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
<?php } ?>
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
<?php if ($page === 'admin-tools') {
    // Use local highlight.js assets if available
    $hlBaseFs  = __DIR__ . '/../../public_html/static/libs/highlightjs';
    $hlBaseUrl = htmlspecialchars($app_root) . 'static/libs/highlightjs/';
    $hlCss     = $hlBaseFs . '/styles/github.min.css';
    $hlJs      = $hlBaseFs . '/highlight.min.js';
    $hlSql     = $hlBaseFs . '/languages/sql.min.js';
    if (is_file($hlCss)) { echo '<link rel="stylesheet" href="' . $hlBaseUrl . 'styles/github.min.css">'; }
    if (is_file($hlJs))  { echo '<script src="' . $hlBaseUrl . 'highlight.min.js"></script>'; }
    if (is_file($hlSql)) { echo '<script src="' . $hlBaseUrl . 'languages/sql.min.js"></script>'; }
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.hljs) { hljs.highlightAll(); }
        });
    </script>
<?php } ?>
<?php
// hook for loading plugin assets (css, images, etc.)
do_hook('page_head_assets', ['page' => $page ?? null, 'action' => $_GET['action'] ?? null, 'app_root' => $app_root ?? '']);
?>
    <title><?= htmlspecialchars($config['site_name']) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($app_root) ?>static/favicon.ico">
</head>

<body>
    <div id="messages-container" class="container-fluid mt-2"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <?php if (isset($system_messages) && is_array($system_messages)): ?>
                    <?php foreach ($system_messages as $msg): ?>
                        <?= Feedback::render($msg['category'], $msg['key'], $msg['custom_message'] ?? null) ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
