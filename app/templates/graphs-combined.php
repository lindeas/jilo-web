
<div>
    <button onclick="setTimeRange('today')">today</button>
    <button onclick="setTimeRange('last2days')">last 2 days</button>
    <button onclick="setTimeRange('last7days')">last 7 days</button>
    <button onclick="setTimeRange('thisMonth')">month</button>
    <button onclick="setTimeRange('thisYear')">year</button>

    <input type="date" id="start-date">

    <input type="date" id="end-date">
    <button onclick="setCustomTimeRange()">custom range</button>
</div>

<script>
// Define an array to store all graph instances
var graphs = [];
</script>

<?php

foreach ($graph as $data) {
    include '../app/helpers/graph.php';
}

?>

<script>
// Function to update the label and propagate zoom across charts
function propagateZoom(chart) {
    var startDate = chart.scales.x.min;
    var endDate = chart.scales.x.max;

    // Update the datetime input fields
    document.getElementById('start-date').value = new Date(startDate).toISOString().slice(0, 10);
    document.getElementById('end-date').value = new Date(endDate).toISOString().slice(0, 10);

    // Update all charts with the new date range
    graphs.forEach(function(graphObj) {
        if (graphObj.graph !== chart) {
            graphObj.graph.options.scales.x.min = startDate;
            graphObj.graph.options.scales.x.max = endDate;
            graphObj.graph.update(); // Redraw chart with new range
        }
        updatePeriodLabel(graphObj.graph, graphObj.label); // Update period label
    });
}

// Predefined time range buttons
function setTimeRange(range) {
    var startDate, endDate;
    var now = new Date();

    switch (range) {
        case 'today':
            startDate = new Date(now.setHours(0, 0, 0, 0));
            endDate = new Date(now.setHours(23, 59, 59, 999));
            timeRangeName = 'today';
            break;
        case 'last2days':
            startDate = new Date(now.setDate(now.getDate() - 2));
            endDate = new Date();
            timeRangeName = 'last 2 days';
            break;
        case 'last7days':
            startDate = new Date(now.setDate(now.getDate() - 7));
            endDate = new Date();
            timeRangeName = 'last 7 days';
            break;
        case 'thisMonth':
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
            endDate = new Date();
            timeRangeName = 'this month so far';
            break;
        case 'thisYear':
            startDate = new Date(now.getFullYear(), 0, 1);
            endDate = new Date();
            timeRangeName = 'this year so far';
            break;
        default:
            return;
    }

    // We set the date input fields to match the selected period
    document.getElementById('start-date').value = startDate.toISOString().slice(0, 10);
    document.getElementById('end-date').value = endDate.toISOString().slice(0, 10);

    // Loop through all graphs and update their time range and label
    graphs.forEach(function(graphObj) {
        graphObj.graph.options.scales.x.min = startDate;
        graphObj.graph.options.scales.x.max = endDate;
        graphObj.graph.update();
        updatePeriodLabel(graphObj.graph, graphObj.label); // Update the period label
    });
}

// Custom date range
function setCustomTimeRange() {
    var startDate = document.getElementById('start-date').value;
    var endDate = document.getElementById('end-date').value;

    if (!startDate || !endDate) return;

    // Convert the input dates to JavaScript Date objects
    startDate = new Date(startDate);
    endDate = new Date(endDate);
    timeRangeName = 'custom range';

    // Loop through all graphs and update the custom time range
    graphs.forEach(function(graphObj) {
        graphObj.graph.options.scales.x.min = startDate;
        graphObj.graph.options.scales.x.max = endDate;
        graphObj.graph.update();
        updatePeriodLabel(graphObj.graph, graphObj.label); // Update the period label
    });
}

// Call setTimeRange('last7days') on page load to pre-load last 7 days by default
window.onload = function() {
    setTimeRange('last7days');
};
</script>
