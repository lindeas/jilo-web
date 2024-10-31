
                <!-- widget "platforms" -->
                <div class="card text-center w-50 mx-lef">
                    <p class="h4 card-header">Jilo configuration for Jitsi platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong> :: edit</p>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=platform">
<?php
foreach ($platformDetails[0] as $key => $value) {
    if ($key === 'id') continue;
?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($config_item) ?>" class="form-label"><?= htmlspecialchars($key) ?></label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value ?? '') ?>" required autofocus />
<?php if ($key === 'name') { ?>
                                    <p class="text-start"><small>descriptive name for the platform</small></p>
<?php } elseif ($key === 'jitsi_url') { ?>
                                    <p class="text-start"><small>URL of the Jitsi Meet (used for checks and for loading config.js)</small></p>
<?php } elseif ($key === 'jilo_database') { ?>
                                    <p class="text-start"><small>path to the database file (relative to the app root)</small></p>
<?php } ?>
                                </div>
                            </div>
<?php } ?>
                            <br />
                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>" />
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&platform=<?= htmlspecialchars($platform_id) ?>#platform<?= htmlspecialchars($platform_id) ?>" />Cancel</a>
                            &nbsp;&nbsp;
                            <input type="submit" class="btn btn-primary btn-sm" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "platforms" -->
