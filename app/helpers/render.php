<?php

// render config variables array
function renderConfig($config) {
    echo "\n\t\t\t\t\t<ul>";
    foreach ($config as $config_item => $config_value) {
        echo "\n\t\t\t\t\t\t<li>";
        echo htmlspecialchars($config_item) . ': ';

        if (is_array($config_value)) {
            // here we render recursively nested arrays
            renderConfig($config_value);
        } else {
            // if it's not array, just display it
            echo htmlspecialchars($config_value ?? '');
        }

        echo '</li>';
    }
    echo "\n\t\t\t\t\t</ul>";
}

?>
