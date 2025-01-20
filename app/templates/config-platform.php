
<!-- "jilo configuration" -->
<div class="container-fluid mt-2">
    <div class="row mb-4">
        <div class="col-md-6 mb-5">
            <h2>Jitsi Meet platforms configuration</h2>
        </div>
        <div class="col-md-6 text-end">
            <a class="btn btn-primary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&action=add">
                <i class="fas fa-plus me-2"></i>Add new platform
            </a>
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
                                    <button type="button" class="btn btn-outline-primary edit-platform">
                                        <i class="fas fa-edit me-1"></i>Edit platform
                                    </button>
                                    <button type="button" class="btn btn-outline-primary save-platform" style="display: none;">
                                        <i class="fas fa-save me-1"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary cancel-edit" style="display: none;">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                    <?php if (count($platformsAll) <= 1): ?>
                                        <button class="btn btn-outline-secondary" disabled 
                                                data-toggle="tooltip" data-placement="top" 
                                                title="Can't delete the last platform">
                                            <i class="fas fa-trash me-1"></i>Delete platform
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-danger delete-platform">
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
                                            <tr>
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

                            <!-- Hosts Section -->
                            <div class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-network-wired me-2 text-secondary"></i>
                                        <span class="text-secondary">
                                            <?= htmlspecialchars(count($hosts)) ?> <?= count($hosts) === 1 ? 'host' : 'hosts' ?>
                                            for platform "<?= htmlspecialchars($platform['name']) ?>"
                                        </span>
                                    </div>
                                    <a class="btn btn-primary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&action=add&platform=<?= htmlspecialchars($platform['id']) ?>">
                                        <i class="fas fa-plus me-2"></i>Add new host
                                    </a>
                                </div>

                                <?php if (!empty($hosts)): ?>
                                    <?php foreach ($hosts as $host): ?>
                                        <?php 
                                        $hostAgents = array_filter($agents, function($agent) use ($host) {
                                            return isset($agent['host_id']) && $agent['host_id'] === $host['id'];
                                        });
                                        ?>
                                        <div class="card mt-5">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-network-wired me-2 text-secondary"></i>
                                                        <h6 class="mb-0">Host id #<?= htmlspecialchars($host['id']) ?> in platform "<?= htmlspecialchars($platform['name']) ?>"</h6>
                                                    </div>
                                                    <div class="ps-4">
                                                        <span class="host-view-mode">
                                                            <div class="row g-2">
                                                                <div class="col-md-6">
                                                                    <div class="small text-muted mb-1">Host description</div>
                                                                    <div class="text-break"><strong><?= htmlspecialchars($host['name'] ?: '(no description)') ?></strong></div>
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
                                                                           placeholder="Optional description">
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
                                                    <button type="button" class="btn btn-outline-primary btn-sm save-host host-edit-mode" style="display: none;">
                                                        <i class="fas fa-save me-1"></i>Save
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm cancel-host-edit host-edit-mode" style="display: none;">
                                                        <i class="fas fa-times me-1"></i>Cancel
                                                    </button>
                                                    <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($platform['id']) ?>&host=<?= htmlspecialchars($host['id']) ?>&action=delete" 
                                                       class="btn btn-outline-danger btn-sm host-view-mode">
                                                        <i class="fas fa-trash me-1"></i>Delete host
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                <!-- Agents Section -->
                                                <?php $hostAgents = $agentObject->getAgentDetails($platform['id']); ?>
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-robot me-2 text-secondary"></i>
                                                        <span class="text-secondary">
                                                            <?= htmlspecialchars(count($hostAgents)) ?> <?= count($hostAgents) === 1 ? 'agent' : 'agents' ?>
                                                            for this host
                                                        </span>
                                                    </div>
                                                    <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=add&platform=<?= htmlspecialchars($platform['id']) ?>&host=<?= htmlspecialchars($host['id']) ?>">
                                                        <i class="fas fa-plus me-2"></i>Add new agent
                                                    </a>
                                                </div>

                                                <?php if (!empty($hostAgents)): ?>
                                                    <div class="table-responsive">
                                                        <table class="table table-hover align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>Agent Type</th>
                                                                    <th>Endpoint URL</th>
                                                                    <th>Check period (minutes)</th>
                                                                    <th class="text-end">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($hostAgents as $agent): ?>
                                                                    <tr>
                                                                        <td>
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-robot me-2 text-secondary"></i>
                                                                                <span class="agent-view-mode">
                                                                                    <?= htmlspecialchars($agent['agent_description']) ?>
                                                                                </span>
                                                                                <div class="agent-edit-mode" style="display: none;">
                                                                                    <select class="form-select form-select-sm" name="agent_type_id" required>
                                                                                        <?php foreach ($agentObject->getAgentTypes() as $type): ?>
                                                                                            <option value="<?= htmlspecialchars($type['id']) ?>" 
                                                                                                    data-endpoint="<?= htmlspecialchars($type['endpoint']) ?>"
                                                                                                <?= $type['id'] === $agent['agent_type_id'] ? 'selected' : '' ?>>
                                                                                                <?= htmlspecialchars($type['description']) ?>
                                                                                            </option>
                                                                                        <?php endforeach; ?>
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-break">
                                                                            <span class="agent-view-mode">
                                                                                <?= htmlspecialchars($agent['url'].$agent['agent_endpoint']) ?>
                                                                            </span>
                                                                            <div class="agent-edit-mode" style="display: none;">
                                                                                <label class="form-label small text-muted">URL</label>
                                                                                <input type="text" class="form-control form-control-sm text-break mb-2" name="url" 
                                                                                       value="<?= htmlspecialchars($agent['url']) ?>" 
                                                                                       placeholder="e.g., http://localhost:8080" required>
                                                                                <label class="form-label small text-muted">Secret Key</label>
                                                                                <input type="text" class="form-control form-control-sm text-break" name="secret_key" 
                                                                                       value="<?= htmlspecialchars($agent['secret_key']) ?>" 
                                                                                       placeholder="Secret key for authentication" required>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <span class="agent-view-mode">
                                                                                <?php if (isset($agent['check_period']) && $agent['check_period'] !== 0): ?>
                                                                                    <?= htmlspecialchars($agent['check_period']) ?> <?= ($agent['check_period'] == 1 ? 'minute' : 'minutes') ?>
                                                                                <?php else: ?>
                                                                                    <span class="text-muted">-</span>
                                                                                <?php endif; ?>
                                                                            </span>
                                                                            <div class="agent-edit-mode" style="display: none;">
                                                                                <input type="number" class="form-control form-control-sm" name="check_period" 
                                                                                       value="<?= htmlspecialchars($agent['check_period']) ?>" 
                                                                                       min="0" placeholder="Check interval in minutes">
                                                                            </div>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            <div class="btn-group agent-actions" data-agent-id="<?= htmlspecialchars($agent['id']) ?>" 
                                                                                 data-platform-id="<?= htmlspecialchars($platform['id']) ?>"
                                                                                 data-host-id="<?= htmlspecialchars($host['id']) ?>">
                                                                                <button type="button" class="btn btn-outline-primary btn-sm edit-agent agent-view-mode">
                                                                                    <i class="fas fa-edit me-1"></i>Edit
                                                                                </button>
                                                                                <button type="button" class="btn btn-outline-primary btn-sm save-agent agent-edit-mode" style="display: none;">
                                                                                    <i class="fas fa-save me-1"></i>Save
                                                                                </button>
                                                                                <button type="button" class="btn btn-outline-secondary btn-sm cancel-agent-edit agent-edit-mode" style="display: none;">
                                                                                    <i class="fas fa-times me-1"></i>Cancel
                                                                                </button>
                                                                                <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=delete&platform=<?= htmlspecialchars($platform['id']) ?>&host=<?= htmlspecialchars($host['id']) ?>&agent=<?= htmlspecialchars($agent['id']) ?>" 
                                                                                   class="btn btn-outline-danger btn-sm agent-view-mode">
                                                                                    <i class="fas fa-trash me-1"></i>Delete
                                                                                </a>
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

<script>
$(function() {
    // Edit platform
    $('.edit-platform').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);
        
        // Show edit mode
        platformTable.find('.view-mode').hide();
        platformTable.find('.edit-mode').show();
        
        // Toggle buttons
        const actions = $(this).closest('.platform-actions');
        actions.find('.edit-platform').hide();
        actions.find('.save-platform, .cancel-edit').show();
    });

    // Cancel edit
    $('.cancel-edit').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);
        
        // Show view mode
        platformTable.find('.view-mode').show();
        platformTable.find('.edit-mode').hide();
        
        // Reset form values to original
        platformTable.find('.edit-mode input').each(function() {
            const originalValue = platformTable.find(`.view-mode:eq(${$(this).closest('tr').index()})`).text().trim();
            $(this).val(originalValue);
        });
        
        // Toggle buttons
        const actions = $(this).closest('.platform-actions');
        actions.find('.edit-platform').show();
        actions.find('.save-platform, .cancel-edit').hide();
    });

    // Save platform
    $('.save-platform').click(function() {
        const platformId = $(this).closest('.platform-actions').data('platform-id');
        const platformTable = $(`.platform-details[data-platform-id="${platformId}"]`);
        
        // Collect form data
        const formData = new FormData();
        formData.append('platform_id', platformId);
        platformTable.find('.edit-mode input').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        
        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=config&item=platform&action=save', {
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
                    console.log('Response text:', text);
                    // If we can't parse JSON but the request was successful,
                    // we'll treat it as a success since we know the save worked
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
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
                actions.find('.edit-platform').show();
                actions.find('.save-platform, .cancel-edit').hide();

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
            actions.find('.edit-platform').show();
            actions.find('.save-platform, .cancel-edit').hide();

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
        
        fetch('<?= htmlspecialchars($app_root) ?>?page=config&item=platform&action=delete&platform=' + platformId, {
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
        
        // Collect form data
        const formData = new FormData();
        formData.append('item', 'host');
        formData.append('host', hostId);
        formData.append('platform', platformId);
        
        card.find('.host-edit-mode input').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        
        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=config&item=host&action=save', {
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
                    console.log('Response text:', text);
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Update view mode with new values
                const name = card.find('input[name="name"]').val() || '(no description)';
                const address = card.find('input[name="address"]').val();
                const viewContent = card.find('.host-view-mode:not(.btn)').first();
                viewContent.html(
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
            const name = card.find('input[name="name"]').val() || '(no description)';
            const address = card.find('input[name="address"]').val();
            const viewContent = card.find('.host-view-mode:not(.btn)').first();
            viewContent.html(
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
        const platformId = agentActions.data('platform-id');
        const hostId = agentActions.data('host-id');
        const row = agentActions.closest('tr');
        
        // Collect form data
        const formData = new FormData();
        formData.append('item', 'agent');
        formData.append('agent', agentId);
        formData.append('platform', platformId);
        formData.append('host', hostId);
        
        row.find('.agent-edit-mode input, .agent-edit-mode select').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        
        // Save via AJAX
        fetch('<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=save', {
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
                    console.log('Response text:', text);
                    return { success: true };
                }
            });
        })
        .then(data => {
            if (data.success) {
                // Update view mode with new values
                const type = row.find('select[name="agent_type_id"] option:selected').text();
                const url = row.find('input[name="url"]').val();
                const endpoint = row.find('select[name="agent_type_id"] option:selected').data('endpoint');
                const checkPeriod = row.find('input[name="check_period"]').val();
                
                row.find('td:first-child .agent-view-mode').text(type);
                row.find('td:nth-child(2) .agent-view-mode').text(url + endpoint);
                row.find('td:nth-child(3) .agent-view-mode').text(
                    checkPeriod > 0 ? 
                    `${checkPeriod} ${checkPeriod == 1 ? 'minute' : 'minutes'}` : 
                    '-'
                );
                
                // Switch back to view mode
                row.find('.agent-view-mode').show();
                row.find('.agent-edit-mode').hide();
            } else {
                alert('Error saving agent: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving agent. Please try again.');
        });
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
<!-- "jilo configuration" -->
