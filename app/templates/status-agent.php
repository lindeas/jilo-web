
                <!-- jilo agent status -->
                <div class="card text-center w-75 mx-lef" style="padding-left: 80px;">
                    <div class="card-body">
                        <p class="card-text text-left" style="text-align: left;">
                            Jilo Agent <a href="<?= htmlspecialchars($app_root) ?>?page=config#platform<?= htmlspecialchars($platform['id']) ?>agent<?= htmlspecialchars($agent['id']) ?>"><?= htmlspecialchars($agent['agent_description']) ?></a>:
                            <strong><?= $agent_availability ?></strong>
                            <br />
                            host: <strong><?= htmlspecialchars($agent_host) ?></strong>,
                            port: <strong><?= htmlspecialchars($agent_port) ?></strong>,
                            endpoint: <strong><?= htmlspecialchars($agent['agent_endpoint']) ?></strong>
                        </p>
                    </div>
                </div>
