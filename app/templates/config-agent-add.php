<?php
// Get available agent types that are not yet in the platform
$available_agent_types = array_filter($jilo_agent_types, function($type) use ($jilo_agent_types_in_platform) {
    return !in_array($type['id'], $jilo_agent_types_in_platform);
});
?>

<div class="card text-center w-75 mx-lef">
    <p class="h4 card-header">Add new Jilo agent</p>
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars($app_root) ?>">
            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>">
            <input type="hidden" name="item" value="agent">
            <input type="hidden" name="new" value="true">

            <div class="mb-3 row">
                <label for="type" class="col-sm-2 col-form-label">Agent Type:</label>
                <div class="col-sm-10">
                    <select class="form-select" id="type" name="type" required>
                        <option value="">Select agent type</option>
                        <?php foreach ($available_agent_types as $type): ?>
                            <option value="<?= htmlspecialchars($type['id']) ?>">
                                <?= htmlspecialchars($type['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="url" class="col-sm-2 col-form-label">URL:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="url" name="url" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="secret_key" class="col-sm-2 col-form-label">Secret Key:</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="secret_key" name="secret_key" required>
                </div>
            </div>

            <div class="mb-3 row">
                <label for="check_period" class="col-sm-2 col-form-label">Check Period (minutes):</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" id="check_period" name="check_period" min="1" required>
                </div>
            </div>

            <div class="mb-3 row">
                <div class="col-sm-10 offset-sm-2">
                    <button type="submit" class="btn btn-primary">Add Agent</button>
                    <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
