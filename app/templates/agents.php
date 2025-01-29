
                <!-- agents live data -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-12 mb-4">
                            <h2 class="mb-0">Jilo Agents status</h2>
                            <small>manage and monitor agents on platform <strong><?= htmlspecialchars($platformDetails[0]['name']) ?></strong></small>
                        </div>
                    </div>

                    <!-- hosts and their agents -->
                    <div class="row">
                        <?php foreach ($agentsByHost as $hostId => $hostData): ?>
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">
                                            <i class="fas fa-network-wired me-2 text-secondary"></i>
                                            Host: <?= htmlspecialchars($hostData['host_name']) ?>
                                            <a href="<?= htmlspecialchars($app_root) ?>?page=settings#platform-<?= htmlspecialchars($platform_id) ?>host-<?= htmlspecialchars($hostId) ?>" class="text-decoration-none">
                                                <i class="fas fa-edit ms-2"></i>
                                            </a>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($hostData['agents'])): ?>
                                            <p class="text-muted">No agents on this host.</p>
                                        <?php else: ?>
                                            <?php foreach ($hostData['agents'] as $agent): ?>
                                                <div class="agent-item mb-4 pb-3 border-bottom">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="flex-grow-1">
                                                            <i class="fas fa-robot me-2 text-secondary"></i>
                                                            <strong>Agent ID:</strong> <?= htmlspecialchars($agent['id']) ?> |
                                                            <strong>Type:</strong> <?= htmlspecialchars($agent['agent_type_id']) ?> (<?= htmlspecialchars($agent['agent_description']) ?>) |
                                                            <strong>Endpoint:</strong> <?= htmlspecialchars($agent['url']) ?><?= htmlspecialchars($agent['agent_endpoint']) ?>
                                                            <a href="<?= htmlspecialchars($app_root) ?>?page=settings#platform-<?= htmlspecialchars($platform_id) ?>agent-<?= htmlspecialchars($agent['id']) ?>" class="text-decoration-none">
                                                                <i class="fas fa-edit ms-2"></i>
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <div class="btn-group" role="group">
                                                        <button id="agent<?= htmlspecialchars($agent['id']) ?>-status" 
                                                                class="btn btn-primary" 
                                                                data-toggle="tooltip" 
                                                                data-trigger="hover" 
                                                                data-placement="bottom" 
                                                                title="Get the agent status" 
                                                                onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '/status', '<?= htmlspecialchars($agentTokens[$agent['id']]) ?>', true)">
                                                            Get Status
                                                        </button>
                                                        <button id="agent<?= htmlspecialchars($agent['id']) ?>-fetch" 
                                                                class="btn btn-primary" 
                                                                data-toggle="tooltip" 
                                                                data-trigger="hover" 
                                                                data-placement="bottom" 
                                                                title="Get data from the agent" 
                                                                onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '<?= htmlspecialchars($agent['agent_endpoint']) ?>', '<?= htmlspecialchars($agentTokens[$agent['id']]) ?>', <?= isset($_SESSION["agent{$agent['id']}_cache"]) ? 'true' : 'false' ?>)">
                                                            Fetch Data
                                                        </button>
                                                        <button id="agent<?= htmlspecialchars($agent['id']) ?>-cache" 
                                                                <?= !isset($_SESSION["agent{$agent['id']}_cache"]) ? 'style="display:none;" ' : '' ?>
                                                                class="btn btn-secondary" 
                                                                data-toggle="tooltip" 
                                                                data-trigger="hover" 
                                                                data-placement="bottom" 
                                                                title="Load cache" 
                                                                onclick="loadCache('<?= htmlspecialchars($agent['id']) ?>')">
                                                            Load Cache
                                                        </button>
                                                        <button id="agent<?= htmlspecialchars($agent['id']) ?>-clear" 
                                                                <?= !isset($_SESSION["agent{$agent['id']}_cache"]) ? 'style="display:none;" ' : '' ?>
                                                                class="btn btn-danger" 
                                                                data-toggle="tooltip" 
                                                                data-trigger="hover" 
                                                                data-placement="bottom" 
                                                                title="Clear cache" 
                                                                onclick="clearCache('<?= htmlspecialchars($agent['id']) ?>')">
                                                            Clear Cache
                                                        </button>
                                                    </div>
                                                    <span id="cacheInfo<?= htmlspecialchars($agent['id']) ?>" class="ms-2 <?= isset($_SESSION["agent{$agent['id']}_cache"]) ? '' : 'd-none' ?>"></span>
                                                    <pre class="results mt-3" id="result<?= htmlspecialchars($agent['id']) ?>">Click a button to display data from the agent.</pre>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- agents live data -->
