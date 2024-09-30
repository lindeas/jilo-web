
                <!-- jilo agents -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo Agents on platform <?= htmlspecialchars($platform_id) ?> (<?= htmlspecialchars($platformDetails[0]['name']) ?>)</p>
                    <div class="card-body">
<?php foreach ($agentDetails as $agent) { ?>
                        <p class="card-text">
                            agent id<?= htmlspecialchars($agent['id']) ?>: type <?= htmlspecialchars($agent['agent_type_id']) ?>, url <?= htmlspecialchars($agent['url']) ?>
                            <button onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '<?= htmlspecialchars($agent['agent_endpoint']) ?>')">fetch data</button>
                            <button onclick="fetchData('<?= htmlspecialchars($agent['id']) ?>', '<?= htmlspecialchars($agent['url']) ?>', '<?= htmlspecialchars($agent['agent_endpoint']) ?>', true)">force refresh</button>
                        </p>
                        <p>Result:</p>
                        <pre id="result<?= htmlspecialchars($agent['id']) ?>">click a button to fetch data from the agent.</pre>

<?php } ?>
