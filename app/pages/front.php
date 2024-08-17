<?php

require_once '../app/classes/database.php';
require '../app/classes/conference.php';
require '../app/classes/participant.php';

// connect to database
require '../app/helpers/database.php';
$db = connectDB($config, 'jilo', $platform_id);


//
// dashboard widget listings
//


////
// monthly usage
$conference = new Conference($db);
$participant = new Participant($db);

// monthly conferences for the last year
$fromMonth = (new DateTime())->sub(new DateInterval('P1Y'));
$fromMonth->modify('first day of this month');
$thisMonth = new DateTime();
$from_time = $fromMonth->format('Y-m-d');
$until_time = $thisMonth->format('Y-m-d');

$widget['records'] = array();

// loop 1 year in the past
$i = 0;
while ($fromMonth < $thisMonth) {

    $untilMonth = clone $fromMonth;
    $untilMonth->modify('last day of this month');

    $from_time = $fromMonth->format('Y-m-d');
    $until_time = $untilMonth->format('Y-m-d');

    $searchConferenceNumber = $conference->conferenceNumber($from_time, $until_time);
    $searchParticipantNumber = $participant->participantNumber($from_time, $until_time);

    // pretty format for displaying the month in the widget
    $month = $fromMonth->format('F Y');

    // populate the records
    $widget['records'][$i] = array(
        'from_time'	=> $from_time,
        'until_time'	=> $until_time,
        'table_headers'	=> $month,
        'conferences'	=> $searchConferenceNumber[0]['conferences'],
        'participants'	=> $searchParticipantNumber[0]['participants'],
    );

    // move everything one month in future
    $untilMonth->add(new DateInterval('P1M'));
    $fromMonth->add(new DateInterval('P1M'));
    $i++;
}

$time_range_specified = true;

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LastYearMonths';
$widget['title'] = 'Conferences monthly stats for the last year';
$widget['collapsible'] = true;
$widget['collapsed'] = false;
$widget['filter'] = false;
if (!empty($searchConferenceNumber) && !empty($searchParticipantNumber)) {
    $widget['full'] = true;
}

// display the widget
include('../app/templates/widget-monthly.php');


////
// conferences in last 2 days
$conference = new Conference($db);

// time range limit
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

        // we don't have duration field, so we calculate it
        if (!empty($start) && !empty($end)) {
            $duration = gmdate("H:i:s", abs(strtotime($end) - strtotime($start)));
        } else {
            $duration = '';
        }
        $conference_record = array(
            // assign title to the field in the array record
            'component'		=> $jitsi_component,
            'start'			=> $start,
            'end'			=> $end,
            'duration'		=> $duration,
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

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LastDays';
$widget['title'] = 'Conferences for the last 2 days';
$widget['collapsible'] = true;
$widget['collapsed'] = false;
$widget['filter'] = false;
if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($conferences['records'][0]);
    $widget['table_records'] = $conferences['records'];
}

// display the widget
include('../app/templates/widget.php');


////
// last 10 conferences
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

        // we don't have duration field, so we calculate it
        if (!empty($start) && !empty($end)) {
            $duration = gmdate("H:i:s", abs(strtotime($end) - strtotime($start)));
        } else {
            $duration = '';
        }
        $conference_record = array(
            // assign title to the field in the array record
            'component'		=> $jitsi_component,
            'start'			=> $start,
            'end'			=> $end,
            'duration'		=> $duration,
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

// prepare the widget
$widget['full'] = false;
$widget['name'] = 'LastConferences';
$widget['title'] = 'The last ' . $conference_number . ' conferences';
$widget['collapsible'] = true;
$widget['collapsed'] = false;
$widget['filter'] = false;

if (!empty($conferences['records'])) {
    $widget['full'] = true;
    $widget['table_headers'] = array_keys($conferences['records'][0]);
    $widget['table_records'] = $conferences['records'];
}

// display the widget
include('../app/templates/widget.php');

?>
