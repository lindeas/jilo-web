<div style="position: relative; width: 800px; height: 400px;">
    <div id="current-period-<?= $data['graph_name'] ?>" style="text-align: center; position: absolute; top: 0; left: 0; right: 0; z-index: 10; font-size: 14px; background-color: rgba(255, 255, 255, 0.7);"></div>
    <canvas id="graph_<?= $data['graph_name'] ?>" style="margin-top: 20px; margin-bottom: 50px;"></canvas>
</div>

<script>
var ctx = document.getElementById('graph_<?= $data['graph_name'] ?>').getContext('2d');
var chartData0 = <?php echo json_encode($data['data0']); ?>;
var chartData1 = <?php echo json_encode($data['data1']); ?>;
var timeRangeName = '';

var labels = chartData0.map(function(item) {
    return item.date;
});
var values0 = chartData0.map(function(item) {
    return item.value;
});
var values1 = chartData1.map(function(item) {
    return item.value;
});

var graph_<?= $data['graph_name'] ?> = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: '<?= $data['graph_data0_label'] ?? '' ?>',
                data: values0,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                fill: false
            },
            {
                label: '<?= $data['graph_data1_label'] ?? '' ?>',
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
//                    enabled: true,
                    mode: 'x',
                    drag: {
                        enabled: true,  // Enable drag to select range
                        borderColor: 'rgba(255, 99, 132, 0.3)',
                        borderWidth: 1
                    },
                    onZoom: function({ chart }) {
                        propagateZoom(chart); // Propagate the zoom to all graphs
                        setActive(document.getElementById('custom_range'));
                    }
                }
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 20,
                    padding: 10
                }
            },
            title: {
                display: true,
                text: '<?= $data['graph_title'] ?>',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: {
                    bottom: 10
                }
            }
        }
    }
});

// Store the graphs in an array
graphs.push({
    graph: graph_<?= $data['graph_name'] ?>,
    label: document.getElementById('current-period-<?= $data['graph_name'] ?>')
});

// Update the time range label
function updatePeriodLabel(chart, labelElement) {
    var startDate = chart.scales.x.min;
    var endDate = chart.scales.x.max;
    if (timeRangeName == 'today') {
        labelElement.innerHTML = 'Currently displaying: ' + timeRangeName + ' (' + new Date(startDate).toLocaleDateString() + ')';
    } else {
        labelElement.innerHTML = 'Currently displaying: ' + timeRangeName + ' (' + new Date(startDate).toLocaleDateString() + ' - ' + new Date(endDate).toLocaleDateString() + ')';
    }
}

// Attach the update function to the 'zoom' event
graph_<?= $data['graph_name'] ?>.options.plugins.zoom.onZoom = function({ chart }) {
    updatePeriodLabel(chart, document.getElementById('current-period-<?= $data['graph_name'] ?>'));
};

// Update the label initially when the chart is rendered
updatePeriodLabel(graph_<?= $data['graph_name'] ?>, document.getElementById('current-period-<?= $data['graph_name'] ?>'));

</script>
