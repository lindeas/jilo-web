<?php

require_once 'classes/database.php';
require 'classes/component.php';

// FIXME move thi sto a special function
$time_range_specified = false;
if (!isset($_REQUEST['from_time']) || (isset($_REQUEST['from_time']) && $_REQUEST['from_time'] == '')) {
    $from_time = '0000-01-01';
} else {
    $from_time = $_REQUEST['from_time'];
    $time_range_specified = true;
}
if (!isset($_REQUEST['until_time']) || (isset($_REQUEST['until_time']) && $_REQUEST['until_time'] == '')) {
    $until_time = '9999-12-31';
} else {
    $until_time = $_REQUEST['until_time'];
    $time_range_specified = true;
}

// jitsi component events list
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    $jitsi_component = "'" . $_REQUEST['name'] . "'";
    $component_id = 'component_id';
} elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $component_id = "'" . $_REQUEST['id'] . "'";
    $jitsi_component = 'jitsi_component';
} else {
    // we need the variables to use them later in sql for columnname = columnname
    $jitsi_component = 'jitsi_component';
    $component_id = 'component_id';
}

// connect to database
try {
    $db = new Database($config['jilo_database']);
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}


//
// Component events listings
//


// list of all component events (default)
//if ($jitsi_component) {
    try {
        $component = new Component($db);

        // prepare the result
        $search = $component->jitsiComponents($jitsi_component, $component_id, $from_time, $until_time);

        if (!empty($search)) {
            $components = array();
            $components['records'] = array();

            foreach ($search as $item) {
                extract($item);
                $component_record = array(
                    // assign title to the field in the array record
                    'component'		=> $jitsi_component,
                    'loglevel'		=> $loglevel,
                    'time'		=> $time,
                    'component ID'	=> $component_id,
                    'event'		=> $event_type,
                    'param'		=> $event_param,
                );
                // populate the result array
                array_push($components['records'], $component_record);
            }
        }

    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
        include 'templates/message.php';
        exit();
    }

    // display the result

    // format the header message
    echo "<div class=\"results-header\">\n";
    if (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
        echo "<div class=\"results-message\">Jitsi events for component <strong>" . $_REQUEST['name'] . "</strong>";
    } elseif (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
        echo "<div class=\"results-message\">Jitsi events for component ID <br /><strong>" . $_REQUEST['id'] . "</strong>";
    } else {
        echo "<div class=\"results-message\">Jitsi events for <strong>all components</strong>";
    }
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

    echo "</div>\n\n";

    // results table
    echo "<div class=\"results\">\n";

    if (!empty($components['records'])) {

        echo "\t<table>\n";
        echo "\t\t<tr>\n";

        // table headers
        foreach (array_keys($components['records'][0]) as $header) {
            echo "\t\t\t<th>" . htmlspecialchars($header) . "</th>\n";
        }
        echo "\t\t</tr>\n";

        //table rows
        foreach ($components['records'] as $row) {
            echo "\t\t<tr>\n";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'component ID') {
                    echo "\t\t\t<td><a href=\"$app_root?page=components&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'component') {
                    echo "\t\t\t<td><a href=\"$app_root?page=components&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } else {
                    echo "\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
                }
            }
            echo "\t\t</tr>\n";
        }

        echo "\t</table>\n";

    } else {
        echo '<p>No matching Jitsi component events found.</p>';
    }
    echo "\n</div>\n";

//}

?>
