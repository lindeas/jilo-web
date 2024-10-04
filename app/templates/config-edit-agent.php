
                <!-- agents -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Jilo Agent configuration for Jitsi platform <strong>"<?= $platformDetails[0]['name'] ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">edit the agent details:</p>
                        <form method="POST" action="<?= $app_root ?>?platform=<?= $platform_id ?>&page=config">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="type_id" class="form-label">type</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <select class="form-control" type="text" name="type" id="agent_type_id" required>
                                        <option></option>
<?php foreach ($jilo_agent_types as $agent_type) { ?>
                                        <option value="<?= $agent_type['id']?>" <?php if ($agentDetails[0]['agent_type_id'] === $agent_type['id']) echo 'selected'; ?>>
                                            <?= $agent_type['description'] ?>
                                        </option>
<?php } ?>
                                    </select>
                                    <p class="text-start"><small>type of agent (meet, jvb, jibri, all)</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="url" class="form-label">URL</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="url" value="<?= $agentDetails[0]['url'] ?>" required />
                                    <p class="text-start"><small>URL of the Jilo Agent API (https://example.com:8081)</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="secret_key" class="form-label">secret key</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="secret_key" value="<?= $agentDetails[0]['secret_key'] ?>" required />
                                    <p class="text-start"><small>secret key for generating the access JWT token</small></p>
                                </div>
                            </div>


                            <br />
                            <input type="hidden" name="agent" value="<?= $agentDetails[0]['id'] ?>" />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config#platform<?= $platform_id ?>agent<?= $agentDetails[0]['id'] ?>" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /agents -->
