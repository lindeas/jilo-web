
                <!-- widget "agents" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Add new Jilo Agent to Jitsi platform "<strong><?= htmlspecialchars($platformDetails[0]['name']) ?></strong>"</p>
                    <div class="card-body">
                        <!--p class="card-text">add new platform:</p-->
                        <form method="POST" action="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="type" class="form-label">type</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="type" value="" required autofocus />
                                    <p class="text-start"><small>type of agent (meet, jvb, jibri, all)</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="url" class="form-label">URL</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="url" value="https://" required />
                                    <p class="text-start"><small>URL of the Jilo Agent API (https://example.com:8081)</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="secret_key" class="form-label">secret key</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="secret_key" value="" required />
                                    <p class="text-start"><small>secret key for generating the access JWT token</small></p>
                                </div>
                            </div>

                            <input type="hidden" name="new" value="true" />
                            <input type="hidden" name="item" value="agent" />

                            <br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "agents" -->
