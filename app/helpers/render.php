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
?>
