<?php if (!empty($agentDetails)): ?>
<div class="card text-center w-75 mx-lef">
    <p class="h4 card-header">Edit Jilo agent</p>
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($app_root . '?page=' . $page) ?>">
            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>">
            <input type="hidden" name="agent" value="<?= htmlspecialchars($agentDetails['id']) ?>">
            <input type="hidden" name="item" value="agent">

            <div class="mb-3 row">
                <label for="type" class="col-sm-2 col-form-label">Agent Type:</label>
                <div class="col-sm-10">
                    <select class="form-select" id="type" name="type" required>
                        <?php foreach ($jilo_agent_types as $type): ?>
                            <option value="<?= htmlspecialchars($type['id']) ?>" <?= $type['id'] == $agentDetails['agent_type_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="url" class="col-sm-2 col-form-label">URL:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="url" name="url" value="<?= htmlspecialchars($agentDetails['url']) ?>" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="secret_key" class="col-sm-2 col-form-label">Secret Key:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="secret_key" name="secret_key" value="<?= htmlspecialchars($agentDetails['secret_key']) ?>" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="check_period" class="col-sm-2 col-form-label">Check Period (minutes):</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" id="check_period" name="check_period" value="<?= htmlspecialchars($agentDetails['check_period']) ?>" min="1" required>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="alert alert-danger">
    Agent not found.
</div>
<?php endif; ?>
