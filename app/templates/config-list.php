
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo web configuration</p>
                    <div class="card-body">
                        <p class="card-text">main variables</p>
<?php
include '../app/helpers/render.php';
renderConfig($config, '0');
echo "\n";
?>

<hr />
                        <p class="card-text">platforms configuration &nbsp;<a class="btn btn-secondary" style="padding: 0px;" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&action=add">add new</a></p>

<?php foreach ($platformsAll as $platform_array) {
    $agents = $agentObject->getAgentDetails($platform_array['id']);
?>

                        <a name="platform<?= htmlspecialchars($platform_array['id']) ?>"></a>
                        <div class="row mb-3" style="padding-left: 0px;">
                            <div class="border bg-light" style="padding-left: 50px; padding-bottom: 0px; padding-top: 0px;">
                                <a style="text-decoration: none;" data-toggle="collapse" href="#collapsePlatform<?= htmlspecialchars($platform_array['id']) ?>" role="button" aria-expanded="true" aria-controls="collapsePlatform<?= htmlspecialchars($platform_array['id']) ?>">
                                    <div class="border bg-white text-start mb-3 rounded mt-3" data-toggle="tooltip" data-placement="bottom" title="configuration for platform <?= htmlspecialchars($platform_array['id']) ?>">
                                        <i class="fas fa-wrench"></i>
                                        <small>platform <?= htmlspecialchars($platform_array['id']) ?> (<?= htmlspecialchars($platform_array['name']) ?>)</small>
                                    </div>
                                </a>
                                <div class="collapse show" id="collapsePlatform<?= htmlspecialchars($platform_array['id']) ?>">

                                    <div class="row mb-1" style="padding-left: 0px;">
                                        <div class="col-md-8 text-start">

                                            <div class="row mb-1">
                                                <div class="col-md-8 text-start">
                                                    <a class="btn btn-secondary" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=edit">edit platform</a>
<?php if (count($platformsAll) <= 1) { ?>
                                                    <span class="btn btn-light" style="padding: 2px;" href="#" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="can't delete the last platform">delete platform</span>
<?php } else { ?>
                                                    <a class="btn btn-danger" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=delete">delete platform</a>
<?php } ?>
                                                </div>
                                            </div>

                                        </div>
                                        <div style="padding-left: 100px; padding-bottom: 20px;">
<?php foreach ($platform_array as $key => $value) {
        if ($key === 'id') continue;
?>
                                            <div class="row mb-1" style="padding-left: 100px;">
                                                <div class="col-md-4 text-end">
                                                    <?= htmlspecialchars($key) ?>:
                                                </div>
                                                <div class="border col-md-8 text-start">
                                                    <?= htmlspecialchars($value) ?>
                                                </div>
                                            </div>
<?php } ?>

                                        </div>
                                        <hr />
                                        <p class="card-text">jilo agents on platform <?= htmlspecialchars($platform_array['id']) ?> (<?= htmlspecialchars($platform_array['name']) ?>)
                                            <br />
                                            total <?= htmlspecialchars(count($agents)) ?> <?= htmlspecialchars(count($agents)) === 1 ? 'jilo agent' : 'jilo agents' ?>&nbsp;
                                            <a class="btn btn-secondary" style="padding: 0px;" href="<?= htmlspecialchars($app_root) ?>?page=config&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=add-agent">
                                                add new
                                            </a>
                                        </p>

<?php foreach ($agents as $agent_array) { ?>

                                        <a name="platform<?= htmlspecialchars($platform_array['id']) ?>agent<?= htmlspecialchars($agent_array['id']) ?>"></a>
                                        <div class="row mb-3" style="padding-left: 0px;">
                                            <div class="border rounded bg-light" style="padding-left: 50px; padding-bottom: 20px; padding-top: 20px;">
                                                <div class="row mb-1" style="padding-left: 0px;">
                                                    <div class="col-md-4 text-end">
                                                        agent id <?= htmlspecialchars($agent_array['id']) ?>:
                                                    </div>
                                                    <div class="col-md-8 text-start">
                                                        <a class="btn btn-secondary" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&platform=<?= htmlspecialchars($agent_array['platform_id']) ?>&agent=<?= htmlspecialchars($agent_array['id']) ?>&action=edit">edit agent</a>
                                                        <a class="btn btn-danger" style="padding: 2px;" href="<?= htmlspecialchars($app_root) ?>?page=config&platform=<?= htmlspecialchars($agent_array['platform_id']) ?>&agent=<?= htmlspecialchars($agent_array['id']) ?>&action=delete">delete agent</a>
                                                    </div>
                                                    <div style="padding-left: 100px; padding-bottom: 20px;">
                                                        <div class="row mb-1" style="padding-left: 100px;">
                                                            <div class="col-md-4 text-end">
                                                                agent type:
                                                            </div>
                                                            <div class="border col-md-8 text-start">
                                                                <?= htmlspecialchars($agent_array['agent_description']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-1" style="padding-left: 100px;">
                                                            <div class="col-md-4 text-end">
                                                                endpoint:
                                                            </div>
                                                            <div class="border col-md-8 text-start">
                                                                <?= htmlspecialchars($agent_array['url'].$agent_array['agent_endpoint']) ?>
                                                            </div>
                                                        </div>
<?php if (isset($agent_array['check_period']) && $agent_array['check_period'] !== 0) { ?>
                                                        <div class="row mb-1" style="padding-left: 100px;">
                                                            <div class="col-md-4 text-end">
                                                                check period:
                                                            </div>
                                                            <div class="border col-md-8 text-start">
                                                                <?= htmlspecialchars($agent_array['check_period']) ?> minutes
                                                            </div>
                                                        </div>
<?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
<?php } ?>



                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>


                    </div>
                </div>
                <!-- /widget "config" -->
