<?php

require_once 'classes/database.php';
require 'classes/participant.php';

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

// participant id/name/IP are specified when searching specific participant(s)
// participant name - this is 'stats_id' in the db
// either id, name, OR IP - in that order
// we use $_REQUEST, so that both links and forms work
if (isset($_REQUEST['id']) && $_REQUEST['id'] != '') {
    $participant_id = $_REQUEST['id'];
    unset($_REQUEST['name']);
    unset($participant_name);
} elseif (isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
    unset($participant_id);
    $participant_name = $_REQUEST['name'];
} elseif (isset($_REQUEST['ip']) && $_REQUEST['ip'] != '') {
    unset($participant_id);
    $participant_ip = $_REQUEST['ip'];
} else {
    unset($participant_id);
    unset($participant_name);
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
// Participant listings
//


// search and list specific participant ID
if (isset($participant_id)) {

    try {
        $participant = new Participant($db);

        // prepare the result
        $search = $participant->conferenceByParticipantId($participant_id, $from_time, $until_time, $participant_id, $from_time, $until_time);

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
    echo "<div class=\"results-message\">Conferences with participant ID matching \"<strong>$participant_id</strong>\"";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

    echo "</div>\n\n";

    // results table
    echo "<div class=\"mb-5\">\n";

    if (!empty($conferences['records'])) {

        echo "\t<table class=\"table table-striped table-hover table-bordered\">\n";

        echo "\t\t<thead class=\"thead-dark\">\n";
        echo "\t\t\t<tr>\n";

        // table headers
        foreach (array_keys($conferences['records'][0]) as $header) {
            echo "\t\t\t\t<th scope=\"col\">" . htmlspecialchars($header) . "</th>\n";
        }
        echo "\t\t\t</tr>\n";
        echo "\t\t</thead>\n";

        echo "\t\t<tbody>\n";

        //table rows
        foreach ($conferences['records'] as $row) {
            echo "\t\t\t<tr>\n";
            $stats_id = false;
            $participant_ip = false;
            if ($row['event'] === 'stats_id') $stats_id = true;
            if ($row['event'] === 'pair selected') $participant_ip = true;
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'participant ID' && $column === $participant_id) {
                    echo "\t\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
                } elseif ($key === 'conference ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($stats_id && $key === 'parameter') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=participants&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($participant_ip && $key === 'parameter') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=participants&ip=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } else {
                    echo "\t\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
                }
            }
            echo "\t\t\t</tr>\n";
        }

        echo "\t\t</tbody>\n";
        echo "\t</table>\n";

    } else {
        echo '<p>No matching conferences found.</p>';
    }
    echo "\n</div>\n";


// search and list specific participant name (stats_id)
} elseif (isset($participant_name)) {

    try {
        $participant = new Participant($db);

        // prepare the result
        $search = $participant->conferenceByParticipantName($participant_name, $from_time, $until_time);

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
    echo "<div class=\"results-message\">Conferences with participant name (stats_id) matching \"<strong>$participant_name</strong>\"";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

    echo "</div>\n\n";

    // results table
    echo "<div class=\"mb-5\">\n";

    if (!empty($conferences['records'])) {

        echo "\t<table class=\"table table-striped table-hover table-bordered\">\n";

        echo "\t\t<thead class=\"thead-dark\">\n";
        echo "\t\t\t<tr>\n";

        // table headers
        foreach (array_keys($conferences['records'][0]) as $header) {
            echo "\t\t\t\t<th scope-\"col\">" . htmlspecialchars($header) . "</th>\n";
        }
        echo "\t\t\t</tr>\n";
        echo "\t\t</thead>\n";

        echo "\t\t<tbody>\n";

        //table rows
        foreach ($conferences['records'] as $row) {
            echo "\t\t\t<tr>\n";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'parameter' && $column === $participant_name) {
                    echo "\t\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
                } elseif ($key === 'conference ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'participant ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=participants&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } else {
                    echo "\t\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
                }
            }
            echo "\t\t\t</tr>\n";
        }

        echo "\t\t</tbody>\n";
        echo "\t</table>\n";

    } else {
        echo '<p>No matching conferences found.</p>';
    }
    echo "\n</div>\n";


// search and list specific participant IP
} elseif (isset($participant_ip)) {

    try {
        $participant = new Participant($db);

        // prepare the result
        $search = $participant->conferenceByParticipantIP($participant_ip, $from_time, $until_time);

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
    echo "<div class=\"results-message\">Conferences with participant IP matching \"<strong>$participant_ip</strong>\"";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

    echo "</div>\n\n";

    // results table
    echo "<div class=\"mb-5\">\n";

    if (!empty($conferences['records'])) {

        echo "\t<table class=\"table table-striped table-hover table-bordered\">\n";

        echo "\t\t<thead class=\"thead-dark\">\n";
        echo "\t\t\t<tr>\n";

        // table headers
        foreach (array_keys($conferences['records'][0]) as $header) {
            echo "\t\t\t\t<th scope=\"col\">" . htmlspecialchars($header) . "</th>\n";
        }
        echo "\t\t\t</tr>\n";
        echo "\t\t</thead>\n";

        echo "\t\t<tbody>\n";

        //table rows
        foreach ($conferences['records'] as $row) {
            echo "\t\t\t<tr>\n";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'parameter' && $column === $participant_ip) {
                    echo "\t\t\t\t<td><strong>" . htmlspecialchars($column ?? '') . "</strong></td>\n";
                } elseif ($key === 'conference ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'conference name') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&name=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'participant ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=participants&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } else {
                    echo "\t\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
                }
            }
            echo "\t\t\t</tr>\n";
        }

        echo "\t\t</tbody>\n";
        echo "\t</table>\n";

    } else {
        echo '<p>No matching conferences found.</p>';
    }
    echo "\n</div>\n";


// list of all participants (default)
} else {
    try {
        $participant = new Participant($db);

        // prepare the result
        $search = $participant->participantsAll($from_time, $until_time);

        if (!empty($search)) {
            $participants = array();
            $participants['records'] = array();

            foreach ($search as $item) {
                extract($item);
                $participant_record = array(
                    // assign title to the field in the array record
                    'component'		=> $jitsi_component,
                    'participant ID'	=> $endpoint_id,
                    'conference ID'	=> $conference_id,
                );
                // populate the result array
                array_push($participants['records'], $participant_record);
            }
        }

    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
        include 'templates/message.php';
        exit();
    }

    // display the result
    echo "<div class=\"results-header\">\n";
    echo "<div class=\"results-message\">All participants";
    if ($time_range_specified) {
        echo "<br />for the time period <strong>$from_time - $until_time</strong>";
    }
    echo "</div>\n\n";

    // filters - time selection and sorting dropdowns
    include 'templates/results-filter.php';

    echo "</div>\n\n";

    // results table
    echo "<div class=\"mb-5\">\n";

    if (!empty($participants['records'])) {

        echo "\t<table class=\"table table-striped table-hover table-bordered\">\n";

        echo "\t\t<thead class=\"thead-dark\">\n";
        echo "\t\t\t<tr>\n";

        // table headers
        foreach (array_keys($participants['records'][0]) as $header) {
            echo "\t\t\t\t<th>" . htmlspecialchars($header) . "</th>\n";
        }
        echo "\t\t\t</tr>\n";
        echo "\t\t</thead>\n";

        echo "\t\t<tbody>\n";

        //table rows
        foreach ($participants['records'] as $row) {
            echo "\t\t\t<tr>\n";
            // sometimes $column is empty, we make it '' then
            foreach ($row as $key => $column) {
                if ($key === 'participant ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=participants&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } elseif ($key === 'conference ID') {
                    echo "\t\t\t\t<td><a href=\"$app_root?page=conferences&id=" . htmlspecialchars($column ?? '') . "\">" . htmlspecialchars($column ?? '') . "</a></td>\n";
                } else {
                    echo "\t\t\t\t<td>" . htmlspecialchars($column ?? '') . "</td>\n";
                }
            }
            echo "\t\t\t</tr>\n";
        }

        echo "\t\t</tbody>\n";
        echo "\t</table>\n";

    } else {
        echo '<p>No matching participants found.</p>';
    }
    echo "\n</div>\n";

}

?>
