
<div class="card text-center w-75 mx-lef">
    <p class="h4 card-header">Jilo agents configuration</p>
    <div class="card-body">
        <?php if (!empty($platformsAll)): ?>
            <?php foreach ($platformsAll as $platform): ?>
                <?php 
                $agents = $agentObject->getAgentDetails($platform['id']);
                ?>
                <div class="row mb-3">
                    <div class="border bg-light" style="padding-left: 50px; padding-bottom: 0px; padding-top: 0px;">
                        <a style="text-decoration: none;" data-toggle="collapse" href="#collapsePlatform<?= htmlspecialchars($platform['id']) ?>" role="button" aria-expanded="true" aria-controls="collapsePlatform<?= htmlspecialchars($platform['id']) ?>">
                            <div class="border bg-white text-start mb-3 rounded mt-3" data-toggle="tooltip" data-placement="bottom" title="agents for platform <?= htmlspecialchars($platform['id']) ?>">
                                <i class="fas fa-server"></i>
                                <small>platform <?= htmlspecialchars($platform['id']) ?> (<?= htmlspecialchars($platform['name']) ?>)</small>
                            </div>
                        </a>
                        <div class="collapse show" id="collapsePlatform<?= htmlspecialchars($platform['id']) ?>">
                            <p class="card-text text-start">
                                total <?= htmlspecialchars(count($agents)) ?> <?= count($agents) === 1 ? 'agent' : 'agents' ?> &nbsp;
                                <a class="btn btn-secondary" style="padding: 0px;" href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=add&platform=<?= htmlspecialchars($platform['id']) ?>">add new</a>
                            </p>

                            <?php if (!empty($agents)): ?>
                                <?php foreach ($agents as $agent): ?>
                                    <div class="row mb-3" style="padding-left: 0px;">
                                        <div class="border rounded bg-light" style="padding-left: 50px; padding-bottom: 20px; padding-top: 20px;">
                                            <div class="row mb-1" style="padding-left: 0px;">
                                                <div class="col-md-4 text-end">
                                                    agent id <?= htmlspecialchars($agent['id']) ?>:
                                                </div>
                                                <div class="col-md-8 text-start">
                                                    <a class="btn btn-secondary" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=edit&platform=<?= htmlspecialchars($platform['id']) ?>&agent=<?= htmlspecialchars($agent['id']) ?>">edit agent</a>
                                                    <a class="btn btn-danger" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&item=agent&action=delete&platform=<?= htmlspecialchars($platform['id']) ?>&agent=<?= htmlspecialchars($agent['id']) ?>">delete agent</a>
                                                </div>
                                            </div>
                                            <div style="padding-left: 100px;">
                                                <div class="row mb-1" style="padding-left: 100px;">
                                                    <div class="col-md-4 text-end">
                                                        agent type:
                                                    </div>
                                                    <div class="border col-md-8 text-start">
                                                        <?= htmlspecialchars($agent['agent_description']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-1" style="padding-left: 100px;">
                                                    <div class="col-md-4 text-end">
                                                        endpoint:
                                                    </div>
                                                    <div class="border col-md-8 text-start">
                                                        <?= htmlspecialchars($agent['url'].$agent['agent_endpoint']) ?>
                                                    </div>
                                                </div>
                                                <?php if (isset($agent['check_period']) && $agent['check_period'] !== 0): ?>
                                                    <div class="row mb-1" style="padding-left: 100px;">
                                                        <div class="col-md-4 text-end">
                                                            check period:
                                                        </div>
                                                        <div class="border col-md-8 text-start">
                                                            <?= htmlspecialchars($agent['check_period']) ?> <?= ($agent['check_period'] == 1 ? 'minute' : 'minutes') ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info text-start">
                                    No agents configured for this platform.
                                </div>
                            <?php endif; ?>
                            <hr />
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">
                No platforms available. Please create a platform first.
            </div>
        <?php endif; ?>
    </div>
</div>
