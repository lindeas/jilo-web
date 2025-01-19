<?php if (!empty($agentDetails)): ?>
<div class="card text-center w-75 mx-lef">
    <p class="h4 card-header">Delete Jilo agent</p>
    <div class="card-body">
        <p class="card-text">Are you sure you want to delete this agent?</p>
        
        <div class="mb-3">
            <strong>Agent ID:</strong> <?= htmlspecialchars($agentDetails['id']) ?><br>
            <strong>Type:</strong> <?= htmlspecialchars($agentDetails['agent_description']) ?><br>
            <strong>URL:</strong> <?= htmlspecialchars($agentDetails['url']) ?><br>
            <strong>Check Period:</strong> <?= htmlspecialchars($agentDetails['check_period']) ?> <?= ($agentDetails['check_period'] == 1 ? 'minute' : 'minutes') ?>
        </div>

        <form method="post" action="<?= htmlspecialchars($app_root) ?>">
            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>">
            <input type="hidden" name="agent" value="<?= htmlspecialchars($agentDetails['id']) ?>">
            <input type="hidden" name="item" value="agent">
            <input type="hidden" name="delete" value="true">

            <div class="mb-3">
                <button type="submit" class="btn btn-danger">Delete Agent</button>
                <a href="<?= htmlspecialchars($app_root) ?>?page=config" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<div class="alert alert-danger">
    Agent not found.
</div>
<?php endif; ?>
