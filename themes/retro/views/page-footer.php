    <!-- Retro Theme Footer -->
    <footer class="footer mt-5 py-3 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <span class="text-muted">
                        &copy; 2024-<?= date('Y') ?> <?= htmlspecialchars($config['site_name'] ?? '') ?> v<?= htmlspecialchars($config['version'] ?? '') ?>
                    </span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted">
                        <?= htmlspecialchars($themeName ?? 'Modern Theme') ?>
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Theme-specific JavaScript -->
    <script src="<?= \App\Helpers\Theme::asset('js/theme.js') ?>"></script>

    <!-- Global site scripts -->
    <script src="<?= htmlspecialchars($app_root) ?>static/js/messages.js"></script>

    <?php if ($page === 'graphs'): ?>
    <script src="<?= htmlspecialchars($app_root) ?>static/js/graphs.js"></script>
    <?php endif; ?>

    <?php do_hook('footer_scripts'); ?>
</body>
</html>
