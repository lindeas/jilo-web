
                <!-- widget "platforms" -->
                <div class="card text-center w-50 mx-lef">
                    <p class="h4 card-header">Jilo configuration for Jitsi platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong> :: delete</p>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">
<?php
foreach ($platformDetails[0] as $key => $value) {
    if ($key === 'id') continue;
?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($key) ?>" class="form-label"><?= htmlspecialchars($key) ?>:</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="text-start"><?= htmlspecialchars($value) ?? '' ?></div>
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value ?? '')?>" />
                                </div>
                            </div>
<?php } ?>
                            <br />
                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>" />
                            <input type="hidden" name="delete" value="true" />
                            <p class="h5 text-danger">Are you sure you want to delete this platform?</p>
                            <br />
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform&platform=<?= htmlspecialchars($platform_id) ?>#platform<?= htmlspecialchars($platform_id) ?>" />Cancel</a>
                            &nbsp;&nbsp;
                            <input type="submit" class="btn btn-danger btn-sm" value="Delete" />
                        </form>
                    </div>
                </div>
                <!-- /widget "platforms" -->
