<div style="position: relative; width: 800px; height: 400px;">
    <div id="current-period" style="text-align: center; position: absolute; top: 0; left: 0; right: 0; z-index: 10; font-size: 14px; background-color: rgba(255, 255, 255, 0.7);"></div>
    <canvas id="graphsConferences" style="margin-top: 20px;"></canvas>
</div>

<div style="position: relative; width: 800px; height: 400px;">
    <div id="current-period" style="text-align: center; position: absolute; top: 0; left: 0; right: 0; z-index: 10; font-size: 14px; background-color: rgba(255, 255, 255, 0.7);"></div>
    <canvas id="graphsParticipants" style="margin-top: 20px;"></canvas>
</div>


<script>
//CONFERENCES

var ctx = document.getElementById('graphsConferences').getContext('2d');
var chartData = <?php echo json_encode($data); ?>;
var chartData2 = <?php echo json_encode($data2); ?>;

var labels = chartData.map(function(item) {
    return item.date;
});

var values = chartData.map(function(item) {
    return item.value;
});

var values2 = chartData2.map(function(item) {
    return item.value;
});

var graphsConferences = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Conferences from Jitsi logs (Jilo)',
                data: values,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            },
            {
                label: 'Conferences from Jitsi API (Jilo Agents)',
                data: values2,
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: false
            }
        ]
    },
    options: {
        layout: {
            padding: {
                top: 30
            }
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'day'
                }
            },
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            zoom: {
                pan: {
                    enabled: true,
                    mode: 'x'
                },
                zoom: {
                    enabled: true,
                    mode: 'x'
                }
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 20,
                    padding: 30
                }
            }
        }
    }
});

// Add dynamic label to show the currently displayed time period
var currentPeriodLabel = document.getElementById('current-period');

function updatePeriodLabel(chart) {
    var startDate = chart.scales.x.min;
    var endDate = chart.scales.x.max;
    currentPeriodLabel.innerHTML = 'Currently displaying: ' + new Date(startDate).toLocaleDateString() + ' - ' + new Date(endDate).toLocaleDateString();
}

// Attach the update function to the 'zoom' event (to be used with the time period links)
graphsConferences.options.plugins.zoom.onZoom = function({ chart }) {
    updatePeriodLabel(chart);
};

// Update the label initially when the chart is rendered
updatePeriodLabel(graphsConferences);


// PARTICIPANTS
var ctx = document.getElementById('graphsParticipants').getContext('2d');
var chartData = <?php echo json_encode($data); ?>;
var chartData2 = <?php echo json_encode($data2); ?>;

var labels = chartData.map(function(item) {
    return item.date;
});

var values = chartData.map(function(item) {
    return item.value;
});

var values2 = chartData2.map(function(item) {
    return item.value;
});

var graphsParticipants = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Conferences from Jitsi logs (Jilo)',
                data: values,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            },
            {
                label: 'Conferences from Jitsi API (Jilo Agents)',
                data: values2,
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: false
            }
        ]
    },
    options: {
        layout: {
            padding: {
                top: 30
            }
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'day'
                }
            },
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            zoom: {
                pan: {
                    enabled: true,
                    mode: 'x'
                },
                zoom: {
                    enabled: true,
                    mode: 'x'
                }
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 20,
                    padding: 30
                }
            }
        }
    }
});

// Add dynamic label to show the currently displayed time period
var currentPeriodLabel = document.getElementById('current-period');

function updatePeriodLabel(chart) {
    var startDate = chart.scales.x.min;
    var endDate = chart.scales.x.max;
    currentPeriodLabel.innerHTML = 'Currently displaying: ' + new Date(startDate).toLocaleDateString() + ' - ' + new Date(endDate).toLocaleDateString();
}

// Attach the update function to the 'zoom' event (for using with the time period links)
graphsParticipants.options.plugins.zoom.onZoom = function({ chart }) {
    updatePeriodLabel(chart);
};

// Update the label initially when the chart is rendered
updatePeriodLabel(graphsParticipants);


</script>
