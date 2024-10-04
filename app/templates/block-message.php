<?php if (isset($error)) { ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
<?php } ?>

<?php if (isset($notice)) { ?>
        <div class="notice"><?= htmlspecialchars($notice) ?></div>
<?php } ?>
