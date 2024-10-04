
                <!-- widget "agents" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo Agent configuration for Jitsi platform <strong>"<?= $platformDetails[0]['name'] ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">delete an agent:</p>
                        <form method="POST" action="<?= $app_root ?>?platform=<?= $platform_id ?>&page=config">
<?php
foreach ($agentDetails[0] as $key => $value) {
//    if ($key === 'id') continue;
?>
                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="<?= $key ?>" class="form-label"><?= $key ?>:</label>
                                </div>
                                <div class="col-md-8">
                                    <div class="text-start"><?= $value ?? '')?></div>
                                    <input type="hidden" name="<?= $key ?>" value="<?= $value ?? '' ?>" />
                                </div>
                            </div>
<?php } ?>
                            <br />
                            <input type="hidden" name="agent" value="<?= $agentDetails[0]['id'] ?>" />
                            <input type="hidden" name="delete" value="true" />
                            <p class="h5 text-danger">Are you sure you want to delete this agent?</p>
                            <br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config#platform<?= $platform_id ?>agent<?= $agentDetails[0]['id'] ?>" />Cancel</a>
                            <input type="submit" class="btn btn-danger" value="Delete" />
                        </form>
                    </div>
                </div>
                <!-- /widget "agents" -->
