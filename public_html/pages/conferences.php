<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// FIXME add dropdown menus for selecting from-until
$from_time = '0000-01-01';
$until_time = '9999-12-31';

// FIXME move thi sto a special function
$time_range_specified = true;

// conference id/name are specified when searching specific conference(s)
// either id or name, id has precedence
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['id'])) {
    $conference_id = $_REQUEST['id'];
    unset($conference_name);
} elseif (isset($_REQUEST['name'])) {
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
    echo "Conferences with ID matching \"<strong>$conference_id</strong>\"";
    if ($time_range_specified) {
        echo " for the time period <strong>$from_time - $until_time</strong>";
    }

    if (!empty($conferences['records'])) {

        echo "\t<table id=\"results\">";
        echo "\t\t<tr>";

        // table headers
        foreach (array_keys($conferences['records'][0]) as $header) {
            echo "\t\t\t<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "\t\t</tr>";

        //table rows
        foreach ($conferences['records'] as $row) {
            echo "\t\t<tr>";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($column === $conference_id) {
                    echo "\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>";
                } else {
                    echo "\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>";
                }
            }
            echo "\t\t</tr>";
        }

        echo "\t</table>";

    } else {
        echo '<p>No matching conferences found.</p>';
    }


// list of all conferences
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
                $conference_record = array(
                    // assign title to the field in the array record
                    'component'		=> $jitsi_component,
                    'start'		=> $start,
                    'end'		=> $end,
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
    echo "Conferences for the time period $from_time - $until_time";

    if (!empty($conferences['records'])) {

        echo "\t<table id=\"results\">";
        echo "\t\t<tr>";

        // table headers
        foreach (array_keys($conferences['records'][0]) as $header) {
            echo "\t\t\t<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "\t\t</tr>";

        //table rows
        foreach ($conferences['records'] as $row) {
            echo "\t\t<tr>";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'conference ID') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>";
                } else {
                    echo "\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>";
                }
            }
            echo "\t\t</tr>";
        }

        echo "\t</table>";

    } else {
        echo '<p>No matching conferences found.</p>';
    }

}

?>
