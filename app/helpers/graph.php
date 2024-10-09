<div style="position: relative; width: 800px; height: 400px;">
    <div id="current-period" style="text-align: center; position: absolute; top: 0; left: 0; right: 0; z-index: 10; font-size: 14px; background-color: rgba(255, 255, 255, 0.7);"></div>
    <canvas id="graph_<?= $graph_name ?>" style="margin-top: 20px;"></canvas>
</div>

<script>
var ctx = document.getElementById('graph_<?= $graph_name ?>').getContext('2d');
var chartData0 = <?php echo json_encode($data0); ?>;
var chartData1 = <?php echo json_encode($data1); ?>;

var labels = chartData0.map(function(item) {
    return item.date;
});

var values0 = chartData0.map(function(item) {
    return item.value;
});

var values1 = chartData1.map(function(item) {
    return item.value;
});

var graph_<?= $graph_name ?> = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: '<?= $graph_data0_label ?? '' ?>',
                data: values0,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            },
            {
                label: '<?= $graph_data1_label ?? '' ?>',
                data: values1,
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
graph_<?= $graph_name ?>.options.plugins.zoom.onZoom = function({ chart }) {
    updatePeriodLabel(chart);
};

// Update the label initially when the chart is rendered
updatePeriodLabel(graph_<?= $graph_name ?>);

</script>
