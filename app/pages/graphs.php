<?php

// FIXME example data
$graph[0]['data0'] = [
    ['date' => '2024-10-06', 'value' => 10],
    ['date' => '2024-10-07', 'value' => 20],
    ['date' => '2024-10-10', 'value' => 15],
    ['date' => '2024-10-11', 'value' => 25],
];

$graph[0]['data1'] = [
    ['date' => '2024-10-06', 'value' => 12],
    ['date' => '2024-10-07', 'value' => 23],
    ['date' => '2024-10-10', 'value' => 11],
    ['date' => '2024-10-11', 'value' => 27],
];

$graph[0]['graph_name'] = 'conferences';
$graph[0]['graph_title'] = 'Conferences in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
$graph[0]['graph_data0_label'] = 'Conferences from Jitsi logs (Jilo)';
$graph[0]['graph_data1_label'] = 'Conferences from Jitsi API (Jilo Agents)';

$graph[1]['data0'] = [
    ['date' => '2024-10-06', 'value' => 20],
    ['date' => '2024-10-07', 'value' => 30],
    ['date' => '2024-10-10', 'value' => 15],
    ['date' => '2024-10-11', 'value' => 55],
];

$graph[1]['data1'] = [
    ['date' => '2024-10-06', 'value' => 22],
    ['date' => '2024-10-07', 'value' => 33],
    ['date' => '2024-10-10', 'value' => 11],
    ['date' => '2024-10-11', 'value' => 57],
];

$graph[1]['graph_name'] = 'participants';
$graph[1]['graph_title'] = 'Participants in "' . htmlspecialchars($platformDetails[0]['name']) . '" over time';
$graph[1]['graph_data0_label'] = 'Participants from Jitsi logs (Jilo)';
$graph[1]['graph_data1_label'] = 'Participants from Jitsi API (Jilo Agents)';

include '../app/templates/graphs-combined.php';

?>
