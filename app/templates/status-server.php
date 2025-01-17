<!-- jilo status -->
<div class="container-fluid mt-2">
    <div class="row mb-5">
        <div class="col">
            <h2>Jilo status</h2>

            <!-- jilo status -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4>Jilo server</h4>
                    <small class="text-muted">Responsible for periodic checks of remote agents and storing received data.</small>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="d-flex align-items-center me-4">
                            <span class="me-2">Jilo server:</span>
                            <span class="badge <?= $server_status ? 'bg-success' : 'bg-danger' ?>">
                                <?= $server_status ? 'Running' : 'Not running' ?>
                            </span>
                        </div>
                        <span class="me-4">Host: <strong><?= htmlspecialchars($server_host) ?></strong></span>
                        <span class="me-4">Port: <strong><?= htmlspecialchars($server_port) ?></strong></span>
                        <span>Endpoint: <strong><?= htmlspecialchars($server_endpoint) ?></strong></span>
                    </div>
                </div>
            </div>
