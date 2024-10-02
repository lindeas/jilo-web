
                <!-- jilo agents -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo Agents on platform <?= htmlspecialchars($platform_id) ?> (<?= htmlspecialchars($platformDetails[0]['name']) ?>)</p>
                    <div class="card-body">
<?php foreach ($agentDetails as $agent) { ?>
                        <p class="card-text text-left" style="text-align: left;">
                            agent id: <strong><?= htmlspecialchars($agent['id']) ?></strong>
                            agent type: <?= htmlspecialchars($agent['agent_type_id']) ?> (<strong><?= htmlspecialchars($agent['agent_description']) ?></strong>)
                            <br />
                            endpoint: <strong><?= htmlspecialchars($agent['url']) ?><?= htmlspecialchars($agent['agent_endpoint']) ?></strong>
                            <br />
<?php
    $payload = [
        'iss' => 'Jilo Web',
        'aud' => $config['domain'],
        'iat' => time(),
        'exp' => time() + 3600,
        'agent_id' => $agent['id']
    ];
    $jwt = $agentObject->generateAgentToken($payload, $agent['secret_key']);
?>
<?php if (isset($_SESSION["agent{$agent['id']}_cache"])) { ?>
                            <button class="btn btn-primary" data-toggle="tooltip" data-trigger="hover" data-placement="bottom" title="load recently cached data" onclick="loadCache('<?= htmlspecialchars($agent['id']) ?>')">load cache</button>
                            <button class="btn btn-primary" data-toggle="tooltip" data-trigger="hover" data-placement="bottom" title="get fresh data from agent" onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '<?= htmlspecialchars($agent['agent_endpoint']) ?>', '<?= htmlspecialchars($jwt) ?>', true)">force refresh</button>
                            <span id="cacheInfo<?= htmlspecialchars($agent['id']) ?>" style="margin: 5px 0;"></span>
<?php } else { ?>
                            <button class="btn btn-primary" data-toggle="tooltip" data-trigger="hover" data-placement="bottom" title="get data from the agent" onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '<?= htmlspecialchars($agent['agent_endpoint']) ?>', '<?= htmlspecialchars($jwt) ?>')">fetch data</button>
                            <button class="btn btn-light" data-toggle="tooltip" data-trigger="hover" data-placement="bottom" title="no cache to refresh">force refresh</button>
<?php } ?>
                    </p>
                        <pre id="result<?= htmlspecialchars($agent['id']) ?>">click a button to fetch data from the agent.</pre>
<?php } ?>
