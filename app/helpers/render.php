<?php

// render config variables array
function renderConfig($config, $indent) {
?>
                        <div style="padding-left: <?= $indent ?>px; padding-bottom: 20px;">
<?php
    foreach ($config as $config_item => $config_value) {
?>
                            <div class="row mb-1" style="padding-left: <?= $indent ?>px;">
                                <div class="col-md-4 text-end">
                                    <?= htmlspecialchars($config_item) ?>:
                                </div>
<?php
        if (is_array($config_value)) {
?>
<?php
            // here we render recursively nested arrays
            $indent = $indent + 50;
            renderConfig($config_value, $indent);
            $indent = 0;
        } else {
            // if it's not array, just display it
?>
                                <div class="border col-md-8 text-start">
                                    <?= htmlspecialchars($config_value ?? '')?>
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
