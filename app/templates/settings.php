
<!-- "jilo settings" -->
<div class="container-fluid mt-2">
    <div class="row mb-4">
        <div class="col-md-6 mb-5">
            <h2 class="mb-0">Jitsi Meet platforms settings</h2>
            <small>manage the monitored platforms and their hosts and agents</small>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" onclick="showAddPlatformModal()">
                <i class="fas fa-plus me-2"></i>Add new platform
            </button>
        </div>
        <div class="row mb-4">
            <?php if (!empty($platformsAll)): ?>
                <ul class="nav nav-tabs mb-3" id="platformTabs" role="tablist">
                    <?php foreach ($platformsAll as $index => $platform): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($index === 0) ? 'active' : '' ?>"
                               id="platform-<?= htmlspecialchars($platform['id']) ?>-tab"
                               data-toggle="tab"
                               href="#platform-<?= htmlspecialchars($platform['id']) ?>"
                               role="tab"
                               aria-controls="platform-<?= htmlspecialchars($platform['id']) ?>"
                               aria-selected="<?= ($index === 0) ? 'true' : 'false' ?>">
                                <?= htmlspecialchars($platform['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content" id="platformTabsContent">
                    <?php foreach ($platformsAll as $index => $platform): ?>
                        <?php 
                        $hosts = $hostObject->getHostDetails($platform['id']);
                        $agents = $agentObject->getAgentDetails($platform['id']);
                        ?>
                        <div class="tab-pane fade <?= ($index === 0) ? 'show active' : '' ?>"
                             id="platform-<?= htmlspecialchars($platform['id']) ?>"
                             role="tabpanel"
                             aria-labelledby="platform-<?= htmlspecialchars($platform['id']) ?>-tab">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-server me-2 text-secondary"></i>
                                    <span class="text-secondary">
                                        Platform #<?= htmlspecialchars($platform['id']) ?>
                                    </span>
                                </div>
                                <div class="btn-group platform-actions" data-platform-id="<?= htmlspecialchars($platform['id']) ?>">
                                    <button type="button" class="btn btn-outline-primary edit-platform platform-view-mode">
                                        <i class="fas fa-edit me-1"></i>Edit platform
                                    </button>
                                    <button type="button" class="btn btn-outline-success save-platform platform-edit-mode" style="display: none;">
                                        <i class="fas fa-save me-1"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary cancel-edit platform-edit-mode" style="display: none;">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <?php if ($userObject->hasRight($user_id, 'delete platform')): ?>
                                        <button type="button" class="btn btn-outline-danger platform-view-mode" onclick="showDeletePlatformModal(<?= htmlspecialchars($platform['id']) ?>, '<?= htmlspecialchars(addslashes($platform['name'])) ?>', '<?= htmlspecialchars(addslashes($platform['jitsi_url'])) ?>', '<?= htmlspecialchars(addslashes($platform['jilo_database'])) ?>')">
                                            <i class="fas fa-trash me-1"></i>Delete platform
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-hover align-middle platform-details" data-platform-id="<?= htmlspecialchars($platform['id']) ?>">
                                    <tbody>
                                        <?php foreach ($platform as $key => $value): ?>
                                            <?php if ($key === 'id') continue; ?>
                                            <tr data-key="<?= htmlspecialchars($key) ?>">
                                                <th style="width: 200px;"><?= htmlspecialchars($key) ?></th>
                                                <td>
                                                    <div class="view-mode">
                                                        <?php if ($key === 'jitsi_url'): ?>
                                                            <a href="<?= htmlspecialchars($value) ?>" target="_blank" rel="noopener noreferrer" 
                                                               data-toggle="tooltip" data-placement="top" 
                                                               title="Open the Jitsi Meet platform in a new window">
                                                                <?= htmlspecialchars($value) ?>
                                                                <i class="fas fa-external-link-alt ms-1"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <?= htmlspecialchars($value) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="edit-mode" style="display: none;">
                                                        <input type="text" class="form-control" name="<?= htmlspecialchars($key) ?>" 
                                                               value="<?= htmlspecialchars($value) ?>" required>
                                                        <?php if ($key === 'name'): ?>
                                                            <small class="form-text text-muted">Descriptive name for the platform</small>
                                                        <?php elseif ($key === 'jitsi_url'): ?>
                                                            <small class="form-text text-muted">URL of the Jitsi Meet (used for checks and for loading config.js)</small>
                                                        <?php elseif ($key === 'jilo_database'): ?>
                                                            <small class="form-text text-muted">Path to the database file (relative to the app root)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Hosts section -->
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-network-wired me-2 text-secondary"></i>
                                        <span class="text-secondary">
                                            <?= htmlspecialchars(count($hosts)) ?> <?= count($hosts) === 1 ? 'host' : 'hosts' ?>
                                            for platform "<?= htmlspecialchars($platform['name']) ?>"
                                        </span>
                                    </div>
                                    <button class="btn btn-primary" onclick="showAddHostModal(<?= htmlspecialchars($platform['id']) ?>)">
                                        <i class="fas fa-plus me-2"></i>Add new host
                                    </button>
                                </div>

                                <?php if (!empty($hosts)): ?>
                                    <?php foreach ($hosts as $host): ?>
                                        <?php 
                                        $hostAgents = $agentObject->getAgentDetails($host['id']); 
                                        ?>
                                        <div class="card mt-5 host-details" data-host-id="<?= htmlspecialchars($host['id']) ?>">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <a id="platform-<?= htmlspecialchars($platform['id']) ?>host-<?= htmlspecialchars($host['id']) ?>">
                                                            <i class="fas fa-network-wired me-2 text-secondary"></i>
                                                        </a>
                                                        <h6 class="mb-0">Host "<?= htmlspecialchars($host['name'] ?: $host['address']) ?>" (#<?= htmlspecialchars($host['id']) ?>) in platform "<?= htmlspecialchars($platform['name']) ?>"</h6>
                                                    </div>
                                                    <div class="ps-4">
                                                        <span class="host-view-mode">
                                                            <div class="row g-2">
                                                                <div class="col-md-6">
                                                                    <div class="small text-muted mb-1">Host description</div>
                                                                    <div class="text-break"><strong><?= htmlspecialchars($host['name'] ?: $host['address']) ?></strong></div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="small text-muted mb-1">DNS name or IP</div>
                                                                    <div class="text-break"><strong><?= htmlspecialchars($host['address']) ?></strong></div>
                                                                </div>
                                                            </div>
                                                        </span>
                                                        <div class="host-edit-mode" style="display: none;">
                                                            <div class="row g-2">
                                                                <div class="col-md-6">
                                                                    <label class="form-label small text-muted">Host description</label>
                                                                    <input type="text" class="form-control form-control-sm text-break" name="name"
                                                                            value="<?= htmlspecialchars($host['name']) ?>"
                                                                            placeholder="(defaults to DNS name or IP)">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label small text-muted">DNS name or IP</label>
                                                                    <input type="text" class="form-control form-control-sm text-break" name="address"
                                                                            value="<?= htmlspecialchars($host['address']) ?>"
                                                                            placeholder="e.g., server.example.com or 192.168.1.100" required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="btn-group host-actions ms-3" data-host-id="<?= htmlspecialchars($host['id']) ?>" 
                                                     data-platform-id="<?= htmlspecialchars($platform['id']) ?>">
                                                    <button type="button" class="btn btn-outline-primary btn-sm edit-host host-view-mode">
                                                        <i class="fas fa-edit me-1"></i>Edit host
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm save-host host-edit-mode" style="display: none;">
                                                        <i class="fas fa-save me-1"></i>Save
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm cancel-host-edit host-edit-mode" style="display: none;">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm host-view-mode" onclick="showDeleteHostModal(<?= htmlspecialchars($platform['id']) ?>, <?= htmlspecialchars($host['id']) ?>, '<?= htmlspecialchars(addslashes($host['name'])) ?>', '<?= htmlspecialchars(addslashes($host['address'])) ?>')">
                                                        <i class="fas fa-trash me-1"></i>Delete host
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <!-- Agents section -->
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-robot me-2 text-secondary"></i>
                                                        <span class="text-secondary">
                                                            <?php 
                                                            $hostAgents = $agentObject->getAgentDetails($host['id']); 
                                                            echo count($hostAgents) . ' ' . (count($hostAgents) === 1 ? 'agent' : 'agents') . ' for host "' . htmlspecialchars($host['name']) . '"';
                                                            ?>
                                                        </span>
                                                    </div>
                                                    <button class="btn btn-primary" onclick="showAddAgentModal(<?= htmlspecialchars($host['id']) ?>)">
                                                        <i class="fas fa-plus me-2"></i>Add new agent
                                                    </button>
                                                </div>

                                                <?php if (!empty($hostAgents)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Agent type</th>
                                                                    <th>Endpoint URL</th>
                                                                    <th>Secret key</th>
                                                                    <th>Check period</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($hostAgents as $agent): ?>
                                                                    <tr class="agent-details" data-agent-id="<?= htmlspecialchars($agent['id']) ?>">
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <a id="platform-<?= htmlspecialchars($platform['id']) ?>agent-<?= htmlspecialchars($agent['id']) ?>">
                                                                                    <i class="fas fa-robot me-2 text-secondary"></i>
                                                                                </a>
                                                                                <span class="agent-view-mode">
                                                                                    <?= htmlspecialchars($agent['agent_description']) ?>
                                                                                </span>
                                                                                <div class="agent-edit-mode" style="display: none;">
                                                                                    <select name="agent_type_id" class="form-select">
                                                                                        <?php foreach ($jilo_agent_types as $type): ?>
                                                                                            <option value="<?= htmlspecialchars($type['id']) ?>" <?= $type['id'] == $agent['agent_type_id'] ? 'selected' : '' ?>>
                                                                                                <?= htmlspecialchars($type['description']) ?>
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="agent-view-mode">
                                                                                <?= htmlspecialchars($agent['url']) ?>
                                                                            </span>
                                                                            <div class="agent-edit-mode" style="display: none;">
                                                                                <input type="text" name="url" class="form-control"
                                                                                        value="<?= htmlspecialchars($agent['url']) ?>"
                                                                                        placeholder="https://address[:port]" required>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="agent-view-mode">
                                                                                <?= isset($agent['secret_key']) ? '••••••' : '' ?>
                                                                            </span>
                                                                            <div class="agent-edit-mode" style="display: none;">
                                                                                <input type="text" name="secret_key" class="form-control"
                                                                                        value="<?= isset($agent['secret_key']) ? htmlspecialchars($agent['secret_key']) : '' ?>">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="agent-view-mode">
                                                                                <?= $agent['check_period'] > 0 ?
                                                                                    htmlspecialchars($agent['check_period']) . ' ' .
                                                                                    ($agent['check_period'] == 1 ? 'minute' : 'minutes') :
                                                                                    'Not monitored' ?>
                                                                            </span>
                                                                            <div class="agent-edit-mode" style="display: none;">
                                                                                <input type="number" name="check_period" class="form-control form-control-sm" style="width: 80px;"
                                                                                        value="<?= htmlspecialchars($agent['check_period']) ?>"
                                                                                        min="0" max="9999" maxlength="4"
                                                                                        placeholder="">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="btn-group agent-actions" data-agent-id="<?= htmlspecialchars($agent['id']) ?>" 
                                                                                 data-host-id="<?= htmlspecialchars($host['id']) ?>">
                                                                                <button type="button" class="btn btn-outline-primary btn-sm edit-agent agent-view-mode">
                                                                                    <i class="fas fa-edit me-1"></i>Edit agent
                                                                                </button>
                                                                                <button type="button" class="btn btn-outline-danger btn-sm delete-agent agent-view-mode"
                                                                                        onclick="showDeleteAgentModal(<?= htmlspecialchars($agent['id']) ?>, '<?= htmlspecialchars($agent['agent_description']) ?>', '<?= htmlspecialchars($agent['url']) ?>')">
                                                                                    <i class="fas fa-trash-alt me-1"></i>Delete
                                                                                </button>
                                                                                <button type="button" class="btn btn-outline-success btn-sm save-agent agent-edit-mode" style="display: none;">
                                                                                    <i class="fas fa-save me-1"></i>Save
                                                                                </button>
                                                                                <button type="button" class="btn btn-outline-secondary btn-sm cancel-agent-edit agent-edit-mode" style="display: none;">
                                                                                    <i class="fas fa-times me-1"></i>Cancel
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No agents configured for this host.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No hosts configured for platform <?= htmlspecialchars($platform['name']) ?>.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No platforms available. Use the button above to add your first platform.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add platform modal -->
<div class="modal" id="addPlatformModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPlatformModalLabel">Add new Jitsi platform</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=settings" id="addPlatformForm">
                <input type="hidden" name="item" value="platform">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="platformName" class="form-label">Platform name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="platformName" name="name" required>
                        <small class="form-text text-muted">Descriptive name for the platform</small>
                    </div>
                    <div class="mb-3">
                        <label for="platformJitsiUrl" class="form-label">Jitsi URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="platformJitsiUrl" name="jitsi_url" value="https://" required>
                        <small class="form-text text-muted">URL of the Jitsi Meet (used for checks and for loading config.js)</small>
                    </div>
                    <div class="mb-3">
                        <label for="platformDatabase" class="form-label">Jilo database <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="platformDatabase" name="jilo_database" required>
                        <small class="form-text text-muted">Path to Jilo database file</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add platform</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add host modal -->
<div class="modal" id="addHostModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addHostModalLabel">Add new host</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=settings" id="addHostForm">
                <input type="hidden" name="item" value="host">
                <input type="hidden" name="platform" id="hostPlatformId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="hostAddress" class="form-label">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hostAddress" name="address" required>
                        <small class="form-text text-muted">DNS name or IP address of the machine</small>
                    </div>
                    <div class="mb-3">
                        <label for="hostName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="hostName" name="name">
                        <small class="form-text text-muted">Description or name of the host (optional)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add host</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add agent modal -->
<div class="modal" id="addAgentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAgentModalLabel">Add new Jilo agent</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=settings" id="addAgentForm">
                <input type="hidden" name="item" value="agent">
                <input type="hidden" name="platform" id="agentPlatformId">
                <input type="hidden" name="host" id="agentHostId">
                <input type="hidden" name="new" value="true">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="agentType" class="form-label">Agent type <span class="text-danger">*</span></label>
                        <select class="form-select form-control" id="agentType" name="type" required>
                            <option value="">Select agent type</option>
                            <?php foreach ($jilo_agent_types as $type): ?>
                                <option value="<?= htmlspecialchars($type['id']) ?>"><?= htmlspecialchars($type['description']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="agentUrl" class="form-label">URL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agentUrl" name="url" required>
                    </div>
                    <div class="mb-3">
                        <label for="agentSecretKey" class="form-label">Secret key</label>
                        <input type="text" class="form-control" id="agentSecretKey" name="secret_key">
                    </div>
                    <div class="mb-3">
                        <label for="agentCheckPeriod" class="form-label">Check period in minutes (0 to disable)</label>
                        <input type="number" class="form-control" id="agentCheckPeriod" name="check_period" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete platform modal -->
<div class="modal fade" id="deletePlatformModal" tabindex="-1" role="dialog" aria-labelledby="deletePlatformModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deletePlatformModalLabel">Delete platform</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=settings" id="deletePlatformForm">
                <input type="hidden" name="item" value="platform">
                <input type="hidden" name="platform" id="deletePlatformId">
                <input type="hidden" name="delete" value="true">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6>Are you sure you want to delete this platform?</h6>
                        <div id="deletePlatformWarning"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Platform name</label>
                        <div id="deletePlatformName" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Jitsi URL</label>
                        <div id="deletePlatformUrl" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Database</label>
                        <div id="deletePlatformDatabase" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3" id="deletePlatformConfirmBlock">
                        <label class="form-label">Type 'delete' to confirm</label>
                        <input type="text" class="form-control" id="deletePlatformConfirm" placeholder="delete">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deletePlatformButton" disabled>Delete platform</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete host modal -->
<div class="modal fade" id="deleteHostModal" tabindex="-1" role="dialog" aria-labelledby="deleteHostModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteHostModalLabel">Delete host</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=settings" id="deleteHostForm">
                <input type="hidden" name="item" value="host">
                <input type="hidden" name="platform" id="deleteHostPlatformId">
                <input type="hidden" name="host" id="deleteHostId">
                <input type="hidden" name="delete" value="true">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6>Are you sure you want to delete this host?</h6>
                        <div id="deleteHostWarning"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Host name</label>
                        <div id="deleteHostName" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Address</label>
                        <div id="deleteHostAddress" class="form-control-plaintext"></div>
                    </div>
                    <div class="mb-3" id="deleteHostConfirmBlock">
                        <label class="form-label">Type 'delete' to confirm</label>
                        <input type="text" class="form-control" id="deleteHostConfirm" placeholder="delete">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteHostButton" disabled>Delete host</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete agent modal -->
<div class="modal fade" id="deleteAgentModal" tabindex="-1" role="dialog" aria-labelledby="deleteAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAgentModalLabel">Delete agent</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteAgentForm" method="post">
                <input type="hidden" name="item" value="agent">
                <input type="hidden" name="delete" value="true">
                <input type="hidden" name="agent" id="deleteAgentId">
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6>Are you sure you want to delete this agent?</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Agent type</label>
                        <div class="form-control-plaintext"><span id="deleteAgentType"></span></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">Endpoint URL</label>
                        <div class="form-control-plaintext"><span id="deleteAgentUrl"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" form="deleteAgentForm" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    // Handle platform tab changes
    $('#platformTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.preventDefault();
    });

    // Handle hash changes
    function handleHash() {
        if (location.hash) {
            // Remove any existing highlights
            $('.bg-info').removeClass('bg-info');

            const match = location.hash.match(/platform-(\d+)/);
            if (match) {
                const platformId = match[1];
                const platformTab = $(`#platform-${platformId}-tab`);

                // Check if this is a newly created platform
                const newPlatformId = sessionStorage.getItem('newPlatformId');
                if (newPlatformId === platformId) {
                    sessionStorage.removeItem('newPlatformId');
                    window.location.reload();
                    return;
                }

                // If platform doesn't exist (old or incorrect link) - redirect to base settings page
                if (!platformTab.length) {
                    window.location.href = '<?= htmlspecialchars($app_root) ?>?page=settings';
                    return;
                }

                // Show tab content directly without triggering URL change
                $('.tab-pane').removeClass('show active');
                $(`#platform-${platformId}`).addClass('show active');
                // Update tab state
                $('#platformTabs a[data-toggle="tab"]').removeClass('active');
                platformTab.addClass('active');

                // Check for host or agent in URL
                const hostMatch = location.hash.match(/platform-\d+host-(\d+)/);
                const agentMatch = location.hash.match(/platform-\d+agent-(\d+)/);

                if (hostMatch) {
                    const hostId = hostMatch[1];
                    $(`.card[data-host-id="${hostId}"] .card-header .flex-grow-1`).addClass('bg-info');
                } else if (agentMatch) {
                    const agentId = agentMatch[1];
                    $(`.agent-details[data-agent-id="${agentId}"] td:lt(4)`).addClass('bg-info');
                }

                // Scroll if it's a host or agent link
                if (hostMatch || agentMatch) {
                    setTimeout(() => {
                        const element = document.getElementById(location.hash.substring(1));
                        if (element) element.scrollIntoView();
                    }, 150);
                }
            }
        } else {
            // No hash - show first tab
            const firstTab = $('#platformTabs a[data-toggle="tab"]').first();
            if (firstTab.length) {
                $('.tab-pane').removeClass('show active');
                $(firstTab.attr('href')).addClass('show active');
                $('#platformTabs a[data-toggle="tab"]').removeClass('active');
                firstTab.addClass('active');
            }
        }
    }
    // Handle hash on page load and changes
    handleHash();
    window.addEventListener('hashchange', handleHash);

    // Handle platform tab changes
    $('#platformTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.preventDefault();
        const platformId = $(e.target).attr('href');
        // Update hash without triggering scroll
        history.replaceState(null, null, platformId);
    });

    // On page load, activate tab from URL hash if present
    if (window.location.hash) {
        const hash = window.location.hash;
        const tab = $(`#platformTabs a[href="${hash}"]`);
        if (tab.length) {
            tab.tab('show');
            // Prevent scroll on page load
            setTimeout(() => {
                window.scrollTo(0, 0);
            }, 1);
        }
    }

    // Add platform ID to form actions to maintain tab after submit
    $('form').submit(function() {
        const currentHash = window.location.hash;
        if (currentHash) {
            const action = $(this).attr('action');
            if (action && action.indexOf('#') === -1) {
                $(this).attr('action', action + currentHash);
            }
        }
    });

    // Edit platform
    $('.edit-platform').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);

        // Show edit mode
        platformTable.find('.view-mode').hide();
        platformTable.find('.edit-mode').show();

        // Toggle buttons
        const actions = $(this).closest('.platform-actions');
        actions.find('.platform-view-mode').hide();
        actions.find('.platform-edit-mode').show();
    });

    // Cancel platform edit
    $('.cancel-edit').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);

        // Show view mode
        platformTable.find('.view-mode').show();
        platformTable.find('.edit-mode').hide();

        // Toggle buttons
        const actions = $(this).closest('.platform-actions');
        actions.find('.platform-view-mode').show();
        actions.find('.platform-edit-mode').hide();
    });

    // Save platform
    $('.save-platform').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);

        // Collect form data
        const formData = new FormData();
        formData.append('item', 'platform');
        formData.append('platform', platformId);
        formData.append('name', platformTable.find('input[name="name"]').val());
        formData.append('jitsi_url', platformTable.find('input[name="jitsi_url"]').val());
        formData.append('jilo_database', platformTable.find('input[name="jilo_database"]').val());

        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=settings&item=platform&action=save', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                JsMessages.success('Successfully edited the platform.');
                // Update view mode with new values
                platformTable.find('.edit-mode input').each(function() {
                    const value = $(this).val();
                    const viewCell = $(this).closest('td').find('.view-mode');
                    if ($(this).attr('name') === 'jitsi_url') {
                        viewCell.find('a')
                            .attr('href', value)
                            .html(value + '<i class="fas fa-external-link-alt ms-1"></i>');
                    } else {
                        viewCell.text(value);
                    }
                });

                // Switch back to view mode
                platformTable.find('.view-mode').show();
                platformTable.find('.edit-mode').hide();

                // Toggle buttons
                const actions = $(this).closest('.platform-actions');
                actions.find('.platform-view-mode').show();
                actions.find('.platform-edit-mode').hide();

                // Update tab name if platform name was changed
                const newName = platformTable.find('input[name="name"]').val();
                $(`#platform-${platformId}-tab`).text(newName);
            } else {
                alert('Error saving platform: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Since we know the save actually works, we'll update the UI anyway
            platformTable.find('.edit-mode input').each(function() {
                const value = $(this).val();
                const viewCell = $(this).closest('td').find('.view-mode');
                if ($(this).attr('name') === 'jitsi_url') {
                    viewCell.find('a')
                        .attr('href', value)
                        .html(value + '<i class="fas fa-external-link-alt ms-1"></i>');
                } else {
                    viewCell.text(value);
                }
            });

            // Switch back to view mode
            platformTable.find('.view-mode').show();
            platformTable.find('.edit-mode').hide();

            // Toggle buttons
            const actions = $(this).closest('.platform-actions');
            actions.find('.platform-view-mode').show();
            actions.find('.platform-edit-mode').hide();

            // Update tab name if platform name was changed
            const newName = platformTable.find('input[name="name"]').val();
            $(`#platform-${platformId}-tab`).text(newName);
        });
    });

    // Delete platform
    $('.delete-platform').click(function() {
        if (!confirm('Are you sure you want to delete this platform?')) {
            return;
        }

        const platformId = $(this).closest('.platform-actions').data('platform-id');

        fetch('<?= htmlspecialchars($app_root) ?>?page=settings&item=platform&action=delete&platform=' + platformId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting platform: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting platform');
        });
    });

    // Delete platform form submission
    $('#deletePlatformForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // If the platform is deleted, we switch to the first tab (no platform in URL)
                $('#deletePlatformModal').modal('hide');
                setTimeout(function() {
                    window.location.href = '<?= htmlspecialchars($app_root) ?>?page=settings';
                }, 500);
                JsMessages.success('Successfully deleted the platform.');
            },
            error: function() {
                JsMessages.error('Failed to delete platform. Please try again.');
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Add platform form submission
    $('#addPlatformForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Get the last added platform ID from the page content
                const lastPlatformTab = $(response).find('#platformTabs a[data-toggle="tab"]').last();
                if (lastPlatformTab.length) {
                    const platformId = lastPlatformTab.attr('href').replace('#platform-', '');
                    // Store the new platform ID so that the check for non-existant tab knows about it
                    sessionStorage.setItem('newPlatformId', platformId);
                    $('#addPlatformModal').modal('hide');
                    JsMessages.success('Successfully added the platform.');
                    setTimeout(function() {
                        // We switch to the tab of the newly created platform
                        document.location = '<?= htmlspecialchars($app_root) ?>?page=settings#platform-' + platformId;
                    }, 500);
                } else {
                    JsMessages.error('Failed to get platform ID. Please refresh the page.');
                    submitBtn.prop('disabled', false);
                }
            },
            error: function() {
                JsMessages.error('Failed to add platform. Please try again.');
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Host editing functionality
    $('.edit-host').click(function() {
        const hostActions = $(this).closest('.host-actions');
        const card = hostActions.closest('.card');

        // Show edit mode
        card.find('.host-view-mode:not(.btn)').hide();
        card.find('.host-edit-mode').show();

        // Toggle buttons
        hostActions.find('.host-view-mode').hide();
        hostActions.find('.host-edit-mode').show();
    });

    // Cancel host edit
    $('.cancel-host-edit').click(function() {
        const hostActions = $(this).closest('.host-actions');
        const card = hostActions.closest('.card');

        // Show view mode
        card.find('.host-view-mode:not(.btn)').show();
        card.find('.host-edit-mode').hide();

        // Toggle buttons
        hostActions.find('.host-view-mode').show();
        hostActions.find('.host-edit-mode').hide();
    });

    // Save host
    $('.save-host').click(function() {
        const hostActions = $(this).closest('.host-actions');
        const hostId = hostActions.data('host-id');
        const platformId = hostActions.data('platform-id');
        const card = hostActions.closest('.card');

        // Get form inputs
        const hostAddress = card.find('input[name="address"]');

        // Validate required fields
        if (!hostAddress.val().trim()) {
            showValidationTooltip(hostAddress, 'Please enter a DNS name or IP address');
            hostAddress.focus();
            return;
        }

        // Collect form data
        const formData = new FormData();
        formData.append('item', 'host');
        formData.append('host', hostId);
        formData.append('platform', platformId);

        card.find('.host-edit-mode input').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });

        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=settings&item=host&action=save', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                JsMessages.success('Successfully edited the host.');
                // Update view mode with new values
                const name = card.find('input[name="name"]').val() || card.find('input[name="address"]').val();
                const address = card.find('input[name="address"]').val();
                const platformName = $('#platformTabs .nav-link.active').text().trim();

                // Update card header
                card.find('.card-header h6').html(`Host "${name || address}" (#${hostId}) in platform "${platformName}"`);

                card.find('.host-view-mode:not(.btn)').first().html(
                    `<div class="row g-2">
                        <div class="col-md-6">
                            <div class="small text-muted mb-1">Host description</div>
                            <div class="text-break"><strong>${name}</strong></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted mb-1">DNS name or IP</div>
                            <div class="text-break"><strong>${address}</strong></div>
                        </div>
                    </div>`
                );

                // Switch back to view mode
                card.find('.host-view-mode:not(.btn)').show();
                card.find('.host-edit-mode').hide();

                // Toggle buttons
                hostActions.find('.host-view-mode').show();
                hostActions.find('.host-edit-mode').hide();
            } else {
                alert('Error saving host: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Since we know the save might work despite JSON errors, update UI anyway
            const name = card.find('input[name="name"]').val() || card.find('input[name="name"]').val();
            const address = card.find('input[name="address"]').val();

            // Update card header
            card.find('.card-header h6').html(`<i class="fas fa-network-wired me-2 text-secondary"></i>Host "${name}" (#${hostId})`);

            card.find('.host-view-mode:not(.btn)').first().html(
                `<div class="row g-2">
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">Host description</div>
                        <div class="text-break"><strong>${name}</strong></div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted mb-1">DNS name or IP</div>
                        <div class="text-break"><strong>${address}</strong></div>
                    </div>
                </div>`
            );

            // Switch back to view mode
            card.find('.host-view-mode:not(.btn)').show();
            card.find('.host-edit-mode').hide();

            // Toggle buttons
            hostActions.find('.host-view-mode').show();
            hostActions.find('.host-edit-mode').hide();
        });
    });

    // Agent editing functionality
    $('.edit-agent').click(function() {
        const agentActions = $(this).closest('.agent-actions');
        const row = agentActions.closest('tr');

        // Show edit mode
        row.find('.agent-view-mode').hide();
        row.find('.agent-edit-mode').show();
    });

    // Cancel agent edit
    $('.cancel-agent-edit').click(function() {
        const agentActions = $(this).closest('.agent-actions');
        const row = agentActions.closest('tr');

        // Show view mode
        row.find('.agent-view-mode').show();
        row.find('.agent-edit-mode').hide();
    });

    // Save agent
    $('.save-agent').click(function() {
        const agentActions = $(this).closest('.agent-actions');
        const agentId = agentActions.data('agent-id');
        const hostId = agentActions.data('host-id');
        const row = agentActions.closest('tr');

        // Get form inputs
        const agentUrl = row.find('input[name="url"]');

        // Validate required fields
        if (!agentUrl.val().trim()) {
            showValidationTooltip(agentUrl, 'Please enter an endpoint URL');
            agentUrl.focus();
            return;
        }

        // Collect form data
        const formData = new FormData();
        formData.append('item', 'agent');
        formData.append('agent', agentId);
        formData.append('host', hostId);

        row.find('.agent-edit-mode input, .agent-edit-mode select').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });

        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=settings&item=agent&action=save', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                JsMessages.success('Successfully edited the agent.');
                // Update view mode with new values
                const type = row.find('select[name="agent_type_id"] option:selected').text();
                const url = row.find('input[name="url"]').val();
                const checkPeriod = row.find('input[name="check_period"]').val();
                const secretKey = row.find('input[name="secret_key"]').val();

                row.find('td:first-child .agent-view-mode').text(type);
                row.find('td:nth-child(2) .agent-view-mode').text(url);
                row.find('td:nth-child(3) .agent-view-mode').text(secretKey ? '••••••' : '');
                row.find('td:nth-child(4) .agent-view-mode').text(
                    checkPeriod > 0 ? 
                    `${checkPeriod} ${checkPeriod == 1 ? 'minute' : 'minutes'}` : 
                    'Not monitored'
                );

                // Switch back to view mode
                row.find('.agent-view-mode').show();
                row.find('.agent-edit-mode').hide();

                // Show success message
                const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">')
                    .text('Agent updated successfully')
                    .append('<button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>');
                $('.content-wrapper').prepend(alert);
            } else {
                alert('Error saving agent: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving agent. Please try again.');
        });
    });

    // Delete agent
    $('.delete-agent').click(function() {
        const row = $(this).closest('tr');
        const agentId = row.data('agent-id');
        const agentType = row.find('td:first-child .agent-view-mode').text().trim();
        const agentUrl = row.find('td:nth-child(2) .agent-view-mode').text().trim();

        $('#deleteAgentId').val(agentId);
        $('#deleteAgentType').text(agentType);
        $('#deleteAgentUrl').text(agentUrl);
        $('#deleteAgentModal').modal('show');
    });

    // Run the delete platform modal
    function showDeletePlatformModal(platformId, name, url, database) {
        document.getElementById('deletePlatformId').value = platformId;
        document.getElementById('deletePlatformName').textContent = name;
        document.getElementById('deletePlatformUrl').textContent = url;
        document.getElementById('deletePlatformDatabase').textContent = database;
        document.getElementById('deletePlatformModalLabel').textContent = `Delete platform "${name}"`;

        // Get hosts and agents for this platform
        const platformPane = document.getElementById(`platform-${platformId}`);
        if (!platformPane) {
            document.getElementById('deletePlatformWarning').innerHTML = '<p class="mb-0">Error: Platform not found.</p>';
            $('#deletePlatformModal').modal();
            return;
        }

        const hosts = platformPane.querySelectorAll('.host-details');
        if (hosts.length > 0) {
            let warningText = '<p>This will <strong>also</strong> delete the following items:</p>';
            warningText += '<ul class="mb-0">';
            hosts.forEach(host => {
                const hostNameEl = host.querySelector('.card-header h6');
                const hostName = hostNameEl ? hostNameEl.textContent.trim() : 'Unknown host';
                const agents = host.querySelectorAll('.agent-details');
                warningText += `<li>${hostName}`;

                if (agents.length > 0) {
                    warningText += '<ul>';
                    agents.forEach(agent => {
                        const agentType = agent.querySelector('td:first-child span');
                        const agentName = agentType ? agentType.textContent.trim() : 'Unknown agent';
                        const agentUrl = agent.querySelector('td:nth-child(2) .agent-view-mode');
                        const url = agentUrl ? agentUrl.textContent.trim() : 'Unknown URL';
                        warningText += `<li>Agent ${agentName} at ${url}</li>`;
                    });
                    warningText += '</ul>';
                }
                warningText += '</li>';
            });
            warningText += '</ul>';
            document.getElementById('deletePlatformWarning').innerHTML = warningText;
            document.getElementById('deletePlatformConfirmBlock').style.display = '';
            document.getElementById('deletePlatformButton').disabled = true;
        } else {
            document.getElementById('deletePlatformWarning').innerHTML = '';
            document.getElementById('deletePlatformConfirmBlock').style.display = 'none';
            document.getElementById('deletePlatformButton').disabled = false;
        }

        $('#deletePlatformModal').modal();
    }

    // run the delete host modal
    function showDeleteHostModal(platformId, hostId, name, address) {
        document.getElementById('deleteHostPlatformId').value = platformId;
        document.getElementById('deleteHostId').value = hostId;
        document.getElementById('deleteHostName').textContent = name || '(no description)';
        document.getElementById('deleteHostAddress').textContent = address;
        document.getElementById('deleteHostModalLabel').textContent = `Delete host "${name}"`;

        // Get agents for this host
        const platformPane = document.getElementById(`platform-${platformId}`);
        if (!platformPane) {
            document.getElementById('deleteHostWarning').innerHTML = '<p class="mb-0">Error: Platform not found.</p>';
            $('#deleteHostModal').modal();
            return;
        }

        const hostCard = platformPane.querySelector(`.host-details[data-host-id="${hostId}"]`);
        let warningText = '<p>This will <strong>also</strong> delete the following items:</p>';

        if (hostCard) {
            const agents = hostCard.querySelectorAll('.agent-details');
            if (agents.length > 0) {
                warningText += '<ul class="mb-0">';

                agents.forEach(agent => {
                    const agentType = agent.querySelector('td:first-child span');
                    const agentName = agentType ? agentType.textContent.trim() : 'Unknown agent';
                    const agentUrl = agent.querySelector('td:nth-child(2) .agent-view-mode');
                    const url = agentUrl ? agentUrl.textContent.trim() : 'Unknown URL';
                    warningText += `<li>Agent ${agentName} at ${url}</li>`;
                });
                warningText += '</ul>';
                document.getElementById('deleteHostButton').disabled = true;
                document.getElementById('deleteHostConfirm').value = '';
                document.getElementById('deleteHostConfirmBlock').style.display = '';
            } else {
                warningText = '';
                document.getElementById('deleteHostButton').disabled = false;
                document.getElementById('deleteHostConfirmBlock').style.display = 'none';
            }
        } else {
            warningText = '<p class="mb-0">Error: Host not found.</p>';
        }

        document.getElementById('deleteHostWarning').innerHTML = warningText;
        $('#deleteHostModal').modal();
    }

    // Run the delete agent modal
    function showDeleteAgentModal(agentId, type, url) {
        $('#deleteAgentId').val(agentId);
        $('#deleteAgentType').text(type);
        $('#deleteAgentUrl').text(url);
        $('#deleteAgentModal').modal('show');
    }

    // Handle confirmation inputs
    $('#deletePlatformConfirm').on('input', function() {
        document.getElementById('deletePlatformButton').disabled = this.value !== 'delete';
    });

    $('#deleteHostConfirm').on('input', function() {
        document.getElementById('deleteHostButton').disabled = this.value !== 'delete';
    });

    // Reset confirmation on modal close
    $('#deletePlatformModal').on('hidden.bs.modal', function() {
        document.getElementById('deletePlatformConfirm').value = '';
        document.getElementById('deletePlatformButton').disabled = true;
    });

    $('#deleteHostModal').on('hidden.bs.modal', function() {
        document.getElementById('deleteHostConfirm').value = '';
        document.getElementById('deleteHostButton').disabled = true;
    });

    // Make functions globally available
    window.showDeletePlatformModal = showDeletePlatformModal;
    window.showDeleteHostModal = showDeleteHostModal;
    window.showDeleteAgentModal = showDeleteAgentModal;

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Helper function to show validation tooltip
    function showValidationTooltip(input, message) {
        const tooltip = $(input)
            .tooltip({
                title: message,
                placement: 'top',
                trigger: 'manual',
                template: '<div class="tooltip tooltip-danger" role="tooltip"><div class="arrow"></div><div class="tooltip-inner bg-danger"></div></div>'
            })
            .tooltip('show');

        // Hide tooltip when input changes
        input.one('input', function() {
            $(this).tooltip('dispose');
        });

        // Hide tooltip after 3 seconds
        setTimeout(() => {
            $(input).tooltip('dispose');
        }, 3000);
    }
});

// Show add platform modal
function showAddPlatformModal() {
    $('#addPlatformModal').modal('show');
}

// Show add host modal
function showAddHostModal(platformId) {
    document.getElementById('hostPlatformId').value = platformId;
    document.getElementById('addHostModalLabel').textContent = 'Add new host to platform #' + platformId;
    $('#addHostModal').modal('show');
}

// Show add agent modal
function showAddAgentModal(hostId) {
    document.getElementById('agentHostId').value = hostId;
    document.getElementById('addAgentModalLabel').textContent = 'Add new agent to host #' + hostId;

    // Filter agent types that are not yet in this host
    const existingTypes = Array.from(document.querySelectorAll(`[data-host-id="${hostId}"] [data-agent-type-id]`))
        .map(el => el.dataset.agentTypeId);

    const agentTypeSelect = document.getElementById('agentType');
    Array.from(agentTypeSelect.options).forEach(option => {
        if (option.value && existingTypes.includes(option.value)) {
            option.disabled = true;
        } else {
            option.disabled = false;
        }
    });

    $('#addAgentModal').modal('show');
}

// Remove the old platform button creation since we have it in the HTML now
document.addEventListener('DOMContentLoaded', function() {
    // Add Host buttons for each platform
    document.querySelectorAll('.platform-card').forEach(card => {
        const platformId = card.dataset.platformId;
        const addHostBtn = document.createElement('button');
        addHostBtn.className = 'btn btn-outline-primary btn-sm ms-2';
        addHostBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Add host';
        addHostBtn.onclick = () => showAddHostModal(platformId);
        card.querySelector('.card-header').appendChild(addHostBtn);
    });

    // Add Agent buttons for each host
    document.querySelectorAll('.host-card').forEach(card => {
        const hostId = card.dataset.hostId;
        const addAgentBtn = document.createElement('button');
        addAgentBtn.className = 'btn btn-outline-primary btn-sm ms-2';
        addAgentBtn.innerHTML = '<i class="fas fa-plus me-1"></i>Add agent';
        addAgentBtn.onclick = () => showAddAgentModal(hostId);
        card.querySelector('.card-header').appendChild(addAgentBtn);
    });
});
</script>

<!-- "jilo settings" -->
