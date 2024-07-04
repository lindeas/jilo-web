<?php

require_once 'classes/database.php';
require 'classes/conference.php';

// list of all conferences
try {
    $db = new Database($config['jilo_database']);
    $conference = new Conference($db);

    $search = $conference->conferences_all_formatted();

    if ($search->rowCount() > 0) {
        $conferences = array();
        $conferences['records'] = array();

        while ($row = $search->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $conference_record = array(
                'jitsi_component'	=> $jitsi_component,
                'start'			=> $start,
                'end'			=> $end,
                'conference_id'		=> $conference_id,
                'conference_name'	=> $conference_name,
                'participants'		=> $participants,
                'name_count'		=> $name_count,
                'conference_host'	=> $conference_host
            );
            array_push($conferences['records'], $conference_record);
        }

        // FIXME format this better
        echo json_encode($conferences);

    } else {
        echo 'No matching conferences found';
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
