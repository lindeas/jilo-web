
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-lef">
                    <p class="h4 card-header">Add new Jitsi platform</p>
                    <div class="card-body">
                        <!--p class="card-text">add new platform:</p-->
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=platform">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="name" class="form-label">name</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="name" value="" required autofocus />
                                    <p class="text-start"><small>descriptive name for the platform</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="jitsi_url" class="form-label">Jitsi URL</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="jitsi_url" value="https://" required />
                                    <p class="text-start"><small>URL of the Jitsi Meet (used for checks and for loading config.js)</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="jilo_database" class="form-label">jilo_database</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="jilo_database" value="" required />
                                    <p class="text-start"><small>path to the database file (relative to the app root)</small></p>
                                </div>
                            </div>

                            <input type="hidden" name="new" value="true" />

                            <br />
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=platform" />Cancel</a>
                            &nbsp;&nbsp;
                            <input type="submit" class="btn btn-primary btn-sm" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
