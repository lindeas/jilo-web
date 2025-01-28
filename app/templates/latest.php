
                <!-- latest data -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-12 mb-4">
                            <h2 class="mb-0">Latest data from Jilo Agents</h2>
                            <small>gathered for platform <?= htmlspecialchars($platform_id) ?> (<?= htmlspecialchars($platformDetails[0]['name']) ?>)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-4">
<?php if (!empty($hostsData)) { ?>
<?php foreach ($hostsData as $host) { ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-network-wired me-2 text-secondary"></i>
                                        <?= htmlspecialchars($host['name']) ?><small class="text-muted"> (<?= htmlspecialchars($host['address']) ?>)</small>
                                    </h5>
                                </div>
                                <div class="card-body">
<?php foreach ($host['agents'] as $agent) { ?>
                                    <div class="mb-4">
                                        <h6 class="border-bottom pb-2">
                                            <i class="fas fa-robot me-2 text-secondary"></i>
                                            <?= htmlspecialchars($agent['name']) ?> agent
                                        </h6>
                                        <table class="table table-results table-striped table-hover table-bordered">
                                            <thead class="align-top">
                                                <tr>
                                                    <th>Metric</th>
                                                    <th>
                                                        Latest value
                                                        <br>
                                                        <small class="text-muted"><?= date('d M Y H:i:s', strtotime($agent['timestamp'])) ?></small>
                                                    </th>
                                                    <th>
                                                        Previous value
<?php
// Find first metric with previous data to get timestamp
$prevTimestamp = null;
foreach ($metrics as $m_section => $m_metrics) {
    foreach ($m_metrics as $m_metric => $m_config) {
        if (isset($agent['metrics'][$m_section][$m_metric]['previous'])) {
            $prevTimestamp = $agent['metrics'][$m_section][$m_metric]['previous']['timestamp'];
            break 2;
        }
    }
}
if ($prevTimestamp) { ?>
                                                        <br>
                                                        <small class="text-muted"><?= date('d M Y H:i:s', strtotime($prevTimestamp)) ?></small>
<?php } ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php foreach ($metrics as $section => $section_metrics) { ?>
<?php
// Check if this section has any data for this agent
$hasData = false;
foreach ($section_metrics as $metric => $metricConfig) {
    if (isset($agent['metrics'][$section][$metric])) {
        $hasData = true;
        break;
    }
}
if (!$hasData) continue;
?>
                                                <tr class="table-secondary">
                                                    <th colspan="3"><?= htmlspecialchars($section) ?></th>
                                                </tr>
<?php foreach ($section_metrics as $metric => $metricConfig) { ?>
<?php if (isset($agent['metrics'][$section][$metric])) {
    $metric_data = $agent['metrics'][$section][$metric];
?>
                                                <tr>
                                                    <td><?= htmlspecialchars($metricConfig['label']) ?></td>
                                                    <td>
<?php if ($metric_data['link']) { ?>
                                                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=<?= htmlspecialchars($metric_data['link']) ?>&from_time=<?= htmlspecialchars($metric_data['latest']['timestamp']) ?>&until_time=<?= htmlspecialchars($metric_data['latest']['timestamp']) ?>"><?= htmlspecialchars($metric_data['latest']['value']) ?></a>
<?php } else { ?>
                                                        <?= htmlspecialchars($metric_data['latest']['value']) ?>
<?php } ?>
                                                    </td>
                                                    <td>
<?php if ($metric_data['previous']) { ?>
<?php if ($metric_data['link']) { ?>
                                                        <a href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=<?= htmlspecialchars($metric_data['link']) ?>&from_time=<?= htmlspecialchars($metric_data['previous']['timestamp']) ?>&until_time=<?= htmlspecialchars($metric_data['previous']['timestamp']) ?>"><?= htmlspecialchars($metric_data['previous']['value']) ?></a>
<?php } else { ?>
                                                        <?= htmlspecialchars($metric_data['previous']['value']) ?>
<?php } ?>
<?php } else { ?>
                                                        <span class="text-muted">No previous data</span>
<?php } ?>
                                                    </td>
                                                </tr>
<?php } ?>
<?php } ?>
<?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
<?php } ?>
                                </div>
                            </div>
<?php } ?>
<?php } else { ?>
                            <div class="alert alert-info m-3" role="alert">
                                No data available from any agents. Please check agent configuration and connectivity.
                            </div>
<?php } ?>
                        </div>
                    </div>
                </div>
                <!-- /latest data -->
