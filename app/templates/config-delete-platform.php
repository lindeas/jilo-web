
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo web configuration for Jitsi platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">delete a platform:</p>
                        <form method="POST" action="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">
<?php
foreach ($platformDetails[0] as $key => $value) {
    if ($key === 'id') continue;
?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= htmlspecialchars($key) ?>" class="form-label"><?= htmlspecialchars($key) ?>:</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="text-start"><?= htmlspecialchars($value ?? '')?></div>
                                    <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value ?? '')?>" />
                                </div>
                            </div>
<?php } ?>
                            <br />
                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>" />
                            <input type="hidden" name="delete" value="true" />
                            <p class="h5 text-danger">Are you sure you want to delete this platform?</p>
                            <br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config#platform<?= htmlspecialchars($platform_id) ?>" />Cancel</a>
                            <input type="submit" class="btn btn-danger" value="Delete" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->