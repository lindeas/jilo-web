<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// connect to database
try {
    $db = new Database($config['jilo_database']);
} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

//
// dashboard widget listings
//

// conferences in last 7 days
try {
    $conference = new Conference($db);

    // conferences for last 2 days
    $from_time = date('Y-m-d', time() - 60 * 60 * 24 * 2);
    $until_time = date('Y-m-d', time());
    $time_range_specified = true;

    // prepare the result
    $search = $conference->conferencesAllFormatted($from_time, $until_time);

    if (!empty($search)) {
        $conferences = array();
        $conferences['records'] = array();

        foreach ($search as $item) {
            extract($item);
            $conference_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'participants'		=> $participants,
                'name count'		=> $name_count,
                'conference host'	=> $conference_host
            );
            // populate the result array
            array_push($conferences['records'], $conference_record);
        }
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

// display the result
echo "<div class=\"widget\">";

echo "<div class=\"results-header\">\n";
echo "<div class=\"results-message\">Conferences for the last 2 days";
if ($time_range_specified) {
    echo "<br />for the time period <strong>$from_time - $until_time</strong>";
}
echo "</div>\n\n";

//// filters - time selection and sorting dropdowns
//include 'templates/results-filter.php';

echo "</div>\n\n";

// results table
echo "<div class=\"results\">\n";

if (!empty($conferences['records'])) {

    echo "\t<table>\n";
    echo "\t\t<tr>\n";

    // table headers
    foreach (array_keys($conferences['records'][0]) as $header) {
        echo "\t\t\t<th>" . htmlspecialchars($header) . "</th>\n";
    }
    echo "\t\t</tr>\n";

    //table rows
    foreach ($conferences['records'] as $row) {
        echo "\t\t<tr>\n";
        // sometimes $column is empty, we make it '' then
        foreach ($row as $key => $column) {
            if ($key === 'conference ID' && $column === $conference_id) {
                echo "\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
            } elseif ($key === 'conference name') {
                echo "\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
            } else {
                echo "\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
            }
        }
        echo "\t\t</tr>\n";
    }

    echo "\t</table>\n";

} else {
    echo '<p>No matching conferences found.</p>';
}
echo "\n</div>\n";

echo "</div>";



// last 10 conferences
try {
    $conference = new Conference($db);

    // all time
    $from_time = '0000-01-01';
    $until_time = '9999-12-31';
    $time_range_specified = false;
    // number of conferences to show
    $conference_number = 10;

    // prepare the result
    $search = $conference->conferencesAllFormatted($from_time, $until_time);

    if (!empty($search)) {
        $conferences = array();
        $conferences['records'] = array();

        $i = 0;
        foreach ($search as $item) {
            extract($item);
            $conference_record = array(
                // assign title to the field in the array record
                'component'		=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'conference ID'		=> $conference_id,
                'conference name'	=> $conference_name,
                'participants'		=> $participants,
                'name count'		=> $name_count,
                'conference host'	=> $conference_host
            );
            // populate the result array
            array_push($conferences['records'], $conference_record);

            // we only take the first 10 results
            $i++;
            if ($i == 10) break;
        }
    }

} catch (Exception $e) {
    $error = 'Error: ' . $e->getMessage();
    include 'templates/message.php';
    exit();
}

// display the result
echo "<div class=\"widget\">";

echo "<div class=\"results-header\">\n";
echo "<div class=\"results-message\">The last $conference_number conferences";
if ($time_range_specified) {
    echo "<br />for the time period <strong>$from_time - $until_time</strong>";
}
echo "</div>\n\n";

//// filters - time selection and sorting dropdowns
//include 'templates/results-filter.php';

echo "</div>\n\n";

// results table
echo "<div class=\"results\">\n";

if (!empty($conferences['records'])) {

    echo "\t<table>\n";
    echo "\t\t<tr>\n";

    // table headers
    foreach (array_keys($conferences['records'][0]) as $header) {
        echo "\t\t\t<th>" . htmlspecialchars($header) . "</th>\n";
    }
    echo "\t\t</tr>\n";

    //table rows
    foreach ($conferences['records'] as $row) {
        echo "\t\t<tr>\n";
        // sometimes $column is empty, we make it '' then
        foreach ($row as $key => $column) {
            if ($key === 'conference ID' && $column === $conference_id) {
                echo "\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
            } elseif ($key === 'conference name') {
                echo "\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
            } else {
                echo "\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
            }
        }
        echo "\t\t</tr>\n";
    }

    echo "\t</table>\n";

} else {
    echo '<p>No matching conferences found.</p>';
}
echo "\n</div>\n";

echo "</div>";


?>
