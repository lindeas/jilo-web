
    <!-- jitsi platform status -->
    <div class="card mt-3 mb-3">
        <div class="card-header">
            <h4>
                <a href="<?= htmlspecialchars($app_root) ?>?page=config#platform<?= htmlspecialchars($platform['id']) ?>" class="text-decoration-none">
                    Jitsi platform "<?= htmlspecialchars($platform['name']) ?>"
                </a>
            </h4>
            <small class="text-muted">Remote Jitsi Meet installation with its database and agents here.</small>
        </div>
        <div class="card-body p-0">
            <div class="card-body">
                <div class="d-flex align-items-center flex-wrap">
                    <span class="me-4">Jilo database: <strong><?= htmlspecialchars($platform['jilo_database']) ?></strong></span>
                    <div class="d-flex align-items-center">
                        <span class="me-2">Status:</span>
                        <span class="badge <?= $jilo_database_status === 'OK' ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars($jilo_database_status) ?></span>
                    </div>
                </div>
            </div>
