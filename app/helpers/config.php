<?php

// Function to format arrays with square brackets
function formatArray(array $array, $indentLevel = 2) {
    $indent = str_repeat('    ', $indentLevel); // 4 spaces per indent level
    $output = "[\n";

    foreach ($array as $key => $value) {
        $output .= $indent . "'" . $key . "'" . ' => ';

        if (is_array($value)) {
            $output .= formatArray($value, $indentLevel + 1);
        } else {
            $output .= var_export($value, true);
        }

        $output .= ",\n";
    }

    $output .= str_repeat('    ', $indentLevel - 1) . ']';

    return $output;
}

?>
