
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo web configuration for Jitsi platform"<?= htmlspecialchars($platform_id) ?>"</p>
                    <div class="card-body">
                        <p class="card-text">edit the platform details:</p>
                        <form method="POST" action="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">
<?php foreach ($config['platforms'][$platform_id] as $config_item => $config_value) { ?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($config_item) ?>" class="form-label"><?= htmlspecialchars($config_item) ?></label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="<?= htmlspecialchars($config_item) ?>" value="<?= htmlspecialchars($config_value ?? '')?>" required />
                                </div>
                            </div>
<?php } ?>
                            <br />&nbsp;<br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
