<?php

// FIXME example data
$one = date('Y-m-d',strtotime("-5 days"));
$two = date('Y-m-d',strtotime("-4 days"));
$three = date('Y-m-d',strtotime("-2 days"));
$four = date('Y-m-d',strtotime("-1 days"));

$graph[0]['data0'] = [
    ['date' => $one, 'value' => 10],
    ['date' => $two, 'value' => 20],
    ['date' => $three, 'value' => 15],
    ['date' => $four, 'value' => 25],
];

$graph[0]['data1'] = [
    ['date' => $one, 'value' => 12],
    ['date' => $two, 'value' => 23],
    ['date' => $three, 'value' => 11],
    ['date' => $four, 'value' => 27],
];

$graph[0]['graph_name'] = 'conferences';
$graph[0]['graph_title'] = 'Conferences in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
$graph[0]['graph_data0_label'] = 'Conferences from Jitsi logs (Jilo)';
$graph[0]['graph_data1_label'] = 'Conferences from Jitsi API (Jilo Agents)';

$graph[1]['data0'] = [
    ['date' => $one, 'value' => 20],
    ['date' => $two, 'value' => 30],
    ['date' => $three, 'value' => 15],
    ['date' => $four, 'value' => 55],
];

$graph[1]['data1'] = [
    ['date' => $one, 'value' => 22],
    ['date' => $two, 'value' => 33],
    ['date' => $three, 'value' => 11],
    ['date' => $four, 'value' => 57],
];

$graph[1]['graph_name'] = 'participants';
$graph[1]['graph_title'] = 'Participants in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
$graph[1]['graph_data0_label'] = 'Participants from Jitsi logs (Jilo)';
$graph[1]['graph_data1_label'] = 'Participants from Jitsi API (Jilo Agents)';

include '../app/templates/graphs-combined.php';

?>
