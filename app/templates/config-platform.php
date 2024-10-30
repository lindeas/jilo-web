
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration :: Jitsi Meet platforms</p>
                    <div class="card-body">
                        <p class="card-text">Jitsi platforms configuration &nbsp;<a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&action=add">add new</a></p>
<?php foreach ($platformsAll as $platform_array) {
    $agents = $agentObject->getAgentDetails($platform_array['id']);
?>
                        <a name="platform<?= htmlspecialchars($platform_array['id']) ?>"></a>
                        <div class="row mb-1 border" style="padding: 20px; padding-bottom: 0px;">
                            <p>
                                platform id <?= htmlspecialchars($platform_array['id']) ?> - <strong><?= htmlspecialchars($platform_array['name']) ?></strong>
                                &nbsp;
                                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=edit">edit platform</a>
<?php if (count($platformsAll) <= 1) { ?>
                                <span class="btn btn-outline-light btn-sm" href="#" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="can't delete the last platform">delete platform</span>
<?php } else { ?>
                                <a class="btn btn-outline-danger btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=delete">delete platform</a>
<?php } ?>
                            </p>
                            <div style="padding-left: 100px; padding-bottom: 20px;">
<?php foreach ($platform_array as $key => $value) {
        if ($key === 'id') continue;
?>
                                <div class="row mb-1" style="padding-left: 100px;">
                                    <div class="col-md-4 text-end">
                                        <?= htmlspecialchars($key) ?>:
                                    </div>
                                    <div class="col-md-8 text-start">
                                        <?= htmlspecialchars($value) ?>
                                    </div>
                                </div>
<?php } ?>
                                <div class="row mb-1" style="padding-left: 100px;">
                                    <div class="col-md-4 text-end"></div>
                                    <div class="col-md-8 text-start">
                                        <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($platform_array['id']) ?>">FIXME 3 hosts</a>
                                    </div>
                                </div>
                                <div class="row mb-1" style="padding-left: 100px;">
                                    <div class="col-md-4 text-end"></div>
                                    <div class="col-md-8 text-start">
                                        <a href="<?= htmlspecialchars($app_root) ?>?page=config&item=endpoint&platform=<?= htmlspecialchars($platform_array['id']) ?>"><?= htmlspecialchars(count($agents)) ?> <?= htmlspecialchars(count($agents)) === 1 ? 'jilo agent endpoint' : 'jilo agent endpoints' ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "config" -->
