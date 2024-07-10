<?php

require_once 'classes/database.php';
require 'classes/conference.php';

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

// conference id/name are specified when searching specific conference(s)
// either id OR name, id has precedence
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $conference_id = $_REQUEST['id'];
    unset($_REQUEST['name']);
    unset($conference_name);
} elseif (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    unset($conference_id);
    $conference_name = $_REQUEST['name'];
} else {
    unset($conference_id);
    unset($conference_name);
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
// Conference listings
//


// search and list specific conference ID
if (isset($conference_id)) {

    try {
        $conference = new Conference($db);

        // prepare the result
        $search = $conference->conferenceById($conference_id, $from_time, $until_time);

        if (!empty($search)) {
            $conferences = array();
            $conferences['records'] = array();

            foreach ($search as $item) {
                extract($item);
                $conference_record = array(
                    // assign title to the field in the array record
                    'time'		=> $time,
                    'conference ID'	=> $conference_id,
                    'conference name'	=> $conference_name,
                    'conference host'	=> $conference_host,
                    'loglevel'		=> $loglevel,
                    'participant ID'	=> $participant_id,
                    'event'		=> $event_type,
                    'parameter'		=> $event_param
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
    echo "<div class=\"results-header\">\n";
    echo "<div class=\"results-message\">Conferences with ID matching \"<strong>$conference_id</strong>\"";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

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
            $stats_id = false;
            $participant_ip = false;
            if ($row['event'] === 'stats_id') $stats_id = true;
            if ($row['event'] === 'pair selected') $participant_ip = true;
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'conference ID' && $column === $conference_id) {
                    echo "\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($stats_id && $key === 'parameter') {
                    echo "\t\t\t<td><a href=\"$app_root?page=participants&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($participant_ip && $key === 'parameter') {
                    echo "\t\t\t<td><a href=\"$app_root?page=participants&ip=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
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


// search and list specific conference ID
} elseif (isset($conference_name)) {

    try {
        $conference = new Conference($db);

        // prepare the result
        $search = $conference->conferenceByName($conference_name, $from_time, $until_time);

        if (!empty($search)) {
            $conferences = array();
            $conferences['records'] = array();

            foreach ($search as $item) {
                extract($item);
                $conference_record = array(
                    // assign title to the field in the array record
                    'time'		=> $time,
                    'conference ID'	=> $conference_id,
                    'conference name'	=> $conference_name,
                    'conference host'	=> $conference_host,
                    'loglevel'		=> $loglevel,
                    'participant ID'	=> $participant_id,
                    'event'		=> $event_type,
                    'parameter'		=> $event_param
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
    echo "<div class=\"results-header\">\n";
    echo "<div class=\"results-message\">Conferences with name matching \"<strong>$conference_name</strong>\"";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

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
            $stats_id = false;
            $participant_ip = false;
            if ($row['event'] === 'stats_id') $stats_id = true;
            if ($row['event'] === 'pair selected') $participant_ip = true;
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'conference name' && $column === $conference_name) {
                    echo "\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
                } elseif ($key === 'conference ID') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'participant ID') {
                    echo "\t\t\t<td><a href=\"$app_root?page=participants&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($stats_id && $key === 'parameter') {
                    echo "\t\t\t<td><a href=\"$app_root?page=participants&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($participant_ip && $key === 'parameter') {
                    echo "\t\t\t<td><a href=\"$app_root?page=participants&ip=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
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


// list of all conferences (default)
} else {
    try {
        $conference = new Conference($db);

        // prepare the result
        $search = $conference->conferencesAllFormatted($from_time, $until_time);

        if (!empty($search)) {
            $conferences = array();
            $conferences['records'] = array();

            foreach ($search as $item) {
                extract($item);

                // we don't have duration field, so we calculate it
                if (!empty($start) && !empty($end)) {
                    $duration = gmdate("H:i:s", abs(strtotime($end) - strtotime($start)));
                } else {
                    $duration = '';
                }
                $conference_record = array(
                    // assign title to the field in the array record
                    'component'		=> $jitsi_component,
                    'start'		=> $start,
                    'end'		=> $end,
                    'duration'		=> $duration,
                    'conference ID'	=> $conference_id,
                    'conference name'	=> $conference_name,
                    'participants'	=> $participants,
                    'name count'	=> $name_count,
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
    echo "<div class=\"results-header\">\n";
    echo "<div class=\"results-message\">All conferences";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

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
                if ($key === 'conference ID') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
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

}

?>
