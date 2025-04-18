
                <!-- jilo status -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-5">
                            <h2 class="mb-0">Jilo status</h2>
                            <small>status checks of the whole Jilo monitoring platform</small>
                        </div>
                        <div class="row mb-4">

                            <!-- jilo server status -->
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
