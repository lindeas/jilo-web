<?php

// render config variables array
function renderConfig($configPart, $indent, $platform=false, $parent='') {
    global $app_root;
    global $config;
        if ($parent === 'platforms') {
?>
                        <div class="col-md-8 text-start">
                            <a class="btn btn-secondary" style="padding: 0px;" href="<?= $app_root ?>?page=config&action=add">add</a>
                        </div>
                        <div class="border bg-light" style="padding-left: <?= $indent ?>px; padding-bottom: 20px; padding-top: 20px;">
<?php   } else {
?>
                        <div style="padding-left: <?= $indent ?>px; padding-bottom: 20px;">
<?php
        }
        foreach ($configPart as $config_item => $config_value) {
            if ($parent === 'platforms') {
                $indent = 0;
            }
?>
                            <div class="row mb-1" style="padding-left: <?= $indent ?>px;">
                                <div class="col-md-4 text-end">
                                    <?= htmlspecialchars($config_item) ?>:
                                </div>
<?php
            if ($parent === 'platforms') { ?>
                                <div class="col-md-8 text-start">
                                    <a class="btn btn-secondary" style="padding: 2px;" href="<?= $app_root ?>?platform=<?= htmlspecialchars($config_item) ?>&page=config&action=edit">edit</a>
<?php
            // we don't delete the last platform
            if (count($configPart) <= 1) { ?>
                                    <span class="btn btn-light" style="padding: 2px;" href="#" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="can't delete the last platform">delete</span>
<?php           } else { ?>
                                    <a class="btn btn-danger" style="padding: 2px;" href="<?= $app_root ?>?platform=<?= htmlspecialchars($config_item) ?>&page=config&action=delete">delete</a>
<?php           } ?>
                                </div>
<?php       }

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
<?= $platform ?>
                                </div>
<?php
            }
?>
                            </div>
<?php
        }
    echo '</div>';
}

?>
