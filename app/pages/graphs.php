<?php

// FIXME example data
$data = [
    ['date' => '2023-01-01', 'value' => 10],
    ['date' => '2023-01-02', 'value' => 20],
    ['date' => '2023-01-03', 'value' => 15],
    ['date' => '2023-01-04', 'value' => 25],
];

$data2 = [
    ['date' => '2023-01-01', 'value' => 12],
    ['date' => '2023-01-02', 'value' => 23],
    ['date' => '2023-01-03', 'value' => 11],
    ['date' => '2023-01-04', 'value' => 27],
];

include '../app/templates/graphs-conferences.php';

?>
