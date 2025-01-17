
        <!-- jilo agent status -->
        <div class="d-flex align-items-center flex-wrap border-top p-3">
            <div class="d-flex align-items-center me-4">
                <span class="me-2">Jilo agent 
                    <a href="<?= htmlspecialchars($app_root) ?>?page=config#platform<?= htmlspecialchars($platform['id']) ?>agent<?= htmlspecialchars($agent['id']) ?>" class="text-decoration-none">
                        <?= htmlspecialchars($agent['agent_description']) ?>
                    </a>:
                </span>
                <span class="badge <?= $agent_availability === 'running' ? 'bg-success' : 'bg-danger' ?>" title="<?= $agent_availability !== 'running' ? htmlspecialchars($agent_availability) : '' ?>" data-toggle="tooltip" data-placement="right" data-offset="30.0">
                    <?= $agent_availability === 'running' ? 'Running' : 'Error' ?>
                </span>
            </div>
            <div class="d-flex align-items-center me-4">
                <span class="me-4">Host: <strong><?= htmlspecialchars($agent_host) ?></strong></span>
                <span class="me-4">Port: <strong><?= htmlspecialchars($agent_port) ?></strong></span>
                <span>Endpoint: <strong><?= htmlspecialchars($agent['agent_endpoint']) ?></strong></span>
            </div>
        </div>
