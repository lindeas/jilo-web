<div style="position: relative; width: 800px; height: 400px;">
    <div id="current-period-<?= $data['graph_name'] ?>" style="text-align: center; position: absolute; top: 0; left: 0; right: 0; z-index: 10; font-size: 14px; background-color: rgba(255, 255, 255, 0.7);"></div>
    <canvas id="graph_<?= $data['graph_name'] ?>" style="margin-top: 20px; margin-bottom: 50px;"></canvas>
</div>

<script>
var ctx = document.getElementById('graph_<?= $data['graph_name'] ?>').getContext('2d');
var timeRangeName = '';

// Prepare datasets
var datasets = [];
<?php foreach ($data['datasets'] as $dataset): ?>
    var chartData = <?php echo json_encode($dataset['data']); ?>;
    datasets.push({
        label: '<?= $dataset['label'] ?>',
        data: chartData.map(function(item) {
            return {
                x: item.date,
                y: item.value
            };
        }),
        borderColor: '<?= $dataset['color'] ?>',
        borderWidth: 1,
        fill: false
    });
<?php endforeach; ?>

var graph_<?= $data['graph_name'] ?> = new Chart(ctx, {
    type: 'line',
    data: {
        datasets: datasets
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

// Store graph instance and title for later reference
graphs.push({
    graph: graph_<?= $data['graph_name'] ?>,
    label: '<?= $data['graph_title'] ?>'
});

// Function to update the period label
function updatePeriodLabel(chart, label) {
    var startDate = new Date(chart.scales.x.min);
    var endDate = new Date(chart.scales.x.max);
    var periodLabel = document.getElementById('current-period-<?= $data['graph_name'] ?>');

    if (timeRangeName) {
        periodLabel.textContent = label + ' (' + timeRangeName + ')';
    } else {
        periodLabel.textContent = label + ' (from ' + startDate.toLocaleDateString() + ' to ' + endDate.toLocaleDateString() + ')';
    }
}

// Initial label update
updatePeriodLabel(graph_<?= $data['graph_name'] ?>, '<?= $data['graph_title'] ?>');
</script>
