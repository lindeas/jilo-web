
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo web configuration for Jitsi platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">edit the platform details:</p>
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">
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
                            <a class="btn btn-secondary" href="<?= htmlspecialchars($app_root) ?>?page=config#platform<?= htmlspecialchars($platform_id) ?>" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
