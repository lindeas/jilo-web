
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration :: Jitsi Meet hosts</p>
                    <div class="card-body">
                        <p class="card-text">Jitsi hosts configuration
                        </p>
<?php foreach ($platformsAll as $platform_array) {
    $hosts = $hostObject->getHostDetails($platform_array['id']);
?>
                        <a name="platform<?= htmlspecialchars($platform_array['id']) ?>"></a>
                        <div class="row mb-1 border" style="padding: 20px; padding-bottom: 0px;">
                            <p class="text-start">
                                platform <strong><?= htmlspecialchars($platform_array['name']) ?></strong>
                            </p>

<?php foreach ($hosts as $host_array) { ?>
                            <a name="platform<?= htmlspecialchars($platform_array['id']) ?>host<?= htmlspecialchars($host_array['id']) ?>"></a>
                            <p class="text-start" style="padding-left: 50px;">
                                <?= htmlspecialchars($host_array['address']) ?>:<?= htmlspecialchars($host_array['port']) ?>
                                &nbsp;
                                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($host_array['platform_id']) ?>&host=<?= htmlspecialchars($host_array['id']) ?>&action=edit">edit host</a>
                                <a class="btn btn-outline-danger btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($host_array['platform_id']) ?>&host=<?= htmlspecialchars($host_array['id']) ?>&action=delete">delete host</a>
                            </p>
<?php } ?>
                            <p class="text-start" style="padding-left: 50px;">
                                total <?= htmlspecialchars(count($hosts)) ?> <?= htmlspecialchars(count($hosts)) === 1 ? 'jilo host' : 'jilo hosts' ?>&nbsp;
                                &nbsp;
                                <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($platform_array['id']) ?>&action=add">add new</a>
                            </p>

                        </div>
<?php } ?>
                    </div>
                </div>
                <!-- /widget "config" -->
