<?php

// FIXME example data
$data0 = [
    ['date' => '2023-01-01', 'value' => 10],
    ['date' => '2023-01-02', 'value' => 20],
    ['date' => '2023-01-03', 'value' => 15],
    ['date' => '2023-01-04', 'value' => 25],
];

$data1 = [
    ['date' => '2023-01-01', 'value' => 12],
    ['date' => '2023-01-02', 'value' => 23],
    ['date' => '2023-01-03', 'value' => 11],
    ['date' => '2023-01-04', 'value' => 27],
];

$graph_name = 'conferences';
$graph_data0_label = 'Conferences from Jitsi logs (Jilo)';
$graph_data1_label = 'Conferences from Jitsi API (Jilo Agents)';
include '../app/helpers/graph.php';

// FIXME example data
$data0 = [
    ['date' => '2023-01-01', 'value' => 20],
    ['date' => '2023-01-02', 'value' => 30],
    ['date' => '2023-01-03', 'value' => 15],
    ['date' => '2023-01-04', 'value' => 55],
];

$data1 = [
    ['date' => '2023-01-01', 'value' => 22],
    ['date' => '2023-01-02', 'value' => 33],
    ['date' => '2023-01-03', 'value' => 11],
    ['date' => '2023-01-04', 'value' => 57],
];

$graph_name = 'participants';
$graph_data0_label = 'Participants from Jitsi logs (Jilo)';
$graph_data1_label = 'Participants from Jitsi API (Jilo Agents)';
include '../app/helpers/graph.php';

?>
