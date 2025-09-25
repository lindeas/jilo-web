<?php
/**
 * Maintenance mode page
 */
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <strong>Maintenance mode</strong>
                </div>
                <div class="card-body">
                    <p class="lead">The site is temporarily unavailable due to maintenance.</p>
                    <?php $mm = \App\Core\Maintenance::getMessage(); ?>
                    <?php if ($mm): ?>
                    <p class="mb-0"><em><?= htmlspecialchars($mm) ?></em></p>
                    <?php else: ?>
                    <p class="text-muted">Please try again later.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
