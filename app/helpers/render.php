<?php

// render config variables array
function renderConfig($configPart, $indent, $platform=false, $parent='') {
    global $app_root;
    global $config;
?>
                        <div style="padding-left: <?= $indent ?>px; padding-bottom: 20px;">
<?php foreach ($configPart as $config_item => $config_value) { ?>
                            <div class="row mb-1" style="padding-left: <?= $indent ?>px;">
                                <div class="col-md-4 text-end">
                                    <?= htmlspecialchars($config_item) ?>:
                                </div>
<?php
            if (is_array($config_value)) {
                // here we render recursively nested arrays
                $indent = $indent + 50;
                if ($parent === 'platforms') {
                    $indent = 100;
                }
                if ($config_item === 'platforms') {
                    renderConfig($config_value, $indent, $platform, 'platforms');
                } else {
                    renderConfig($config_value, $indent, $platform);
                }
                $indent = 0;
            } else {
                // if it's not array, just display it
?>
                                <div class="border col-md-8 text-start">
                                    <?= htmlspecialchars($config_value ?? '')?>
                                </div>
<?php       } ?>
                            </div>
<?php } ?>
                        </div>
<?php
}


// render config variables array
function editConfig($configPart, $indent, $platform=false, $parent='') {
    global $app_root;
    global $config;
?>
                        <div style="padding-left: <?= $indent ?>px; padding-bottom: 20px;">
<?php foreach ($configPart as $config_item => $config_value) { ?>
                            <div class="row mb-1" style="padding-left: <?= $indent ?>px;">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($config_item) ?>" class="form-label"><?= htmlspecialchars($config_item) ?></label>
                                </div>

<?php
            if (is_array($config_value)) {
                // here we render recursively nested arrays
                $indent = $indent + 50;
                editConfig($config_value, $indent, $platform);
                $indent = 0;
            } else {
                // if it's not array, just display it
?>
                                <div class="col-md-8 text-start">
<?php if ($config_item === 'registration_enabled') { ?>
                                    <input class="form-check-input" type="checkbox" role="switch" name="<?= htmlspecialchars($config_item) ?>" value="<?= htmlspecialchars($config_value ?? 0) ?>" <?= ($config_value === 1 || $config_value === true) ? 'checked' : '' ?> />
<?php } elseif ($config_item === 'environment') { ?>
                                    <select class="form-control" type="text" name="<?= htmlspecialchars($config_item) ?>">
                                        <option value="development"<?= ($config_value === 'development') ? ' selected' : '' ?>>development</option>
                                        <option value="production"<?= ($config_value === 'production') ? ' selected' : '' ?>>production</option>
                                    </select>
<?php } elseif ($config_item === 'version') {?>
                                    <input class="form-control" type="text" name="<?= htmlspecialchars($config_item) ?>" value="<?= htmlspecialchars($config_value ?? '') ?>" disabled />
<?php } else { ?>
                                    <input class="form-control" type="text" name="<?= htmlspecialchars($config_item) ?>" value="<?= htmlspecialchars($config_value ?? '') ?>" />
<?php } ?>
                                </div>
<?php       } ?>
                            </div>
<?php } ?>
                        </div>
<?php
}


?>
