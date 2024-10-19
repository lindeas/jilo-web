
                <!-- jilo agents -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo platform status</p>
                    <div class="card-body">
                        <p class="card-text text-left" style="text-align: left;">
                            Jilo Server:
<?php if ($server_status) { ?>
                                <strong><span class="text-success">running</span></strong>
<?php } else { ?>
                                <strong><span class="text-danger">not running</span></strong>
<?php } ?>
                                <br />
                                host: <strong><?= htmlspecialchars($server_host) ?></strong>,
                                port: <strong><?= htmlspecialchars($server_port) ?></strong>,
                                endpoint: <strong><?= htmlspecialchars($server_endpoint) ?></strong>
                        </p>
                    </div>
                </div>
