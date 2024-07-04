<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// FIXME add dropdown menus for selecting from-until
$from_time = '0000-01-01';
$until_time = '9999-12-31';

// list of all conferences
try {
    $db = new Database($config['jilo_database']);
    $conference = new Conference($db);

    $search = $conference->conferencesAllFormatted($from_time,$until_time);

    if (!empty($search)) {
        $conferences = array();
        $conferences['records'] = array();

        foreach ($search as $item) {
            extract($item);
            $conference_record = array(
                // assign title to the field in the array record
                'jitsi_component'	=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'conference_id'		=> $conference_id,
                'conference_name'	=> $conference_name,
                'participants'		=> $participants,
                'name_count'		=> $name_count,
                'conference_host'	=> $conference_host
            );
            // populate the result array
            array_push($conferences['records'], $conference_record);
        }
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

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
        foreach ($row as $column) {
            echo "\t\t\t<td>" . htmlspecialchars($column) . "</td>";
        }
        echo "\t\t</tr>";
    }

    echo "\t</table>";

} else {
    echo '<p>No matching conferences found.</p>';
}

?>
