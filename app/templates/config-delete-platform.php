
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo web configuration for Jitsi platform "<?= htmlspecialchars($platform_id) ?>"</p>
                    <div class="card-body">
                        <p class="card-text">delete a platform:</p>
                        <form method="POST" action="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">
<?php foreach ($config['platforms'][$platform_id] as $config_item => $config_value) { ?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($config_item) ?>" class="form-label"><?= htmlspecialchars($config_item) ?>:</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="text-start"><?= htmlspecialchars($config_value ?? '')?></div>
                                    <input type="hidden" name="<?= htmlspecialchars($config_item) ?>" value="<?= htmlspecialchars($config_value ?? '')?>" />
                                </div>
                            </div>
<?php } ?>
                            <br />
                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>" />
                            <input type="hidden" name="delete" value="true" />
                            <p class="h5 text-danger">Are you sure you want to delete this platform?</p>
                            <br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config" />Cancel</a>
                            <input type="submit" class="btn btn-danger" value="Delete" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
