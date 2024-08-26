
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Add new Jitsi platform</p>
                    <div class="card-body">
                        <!--p class="card-text">add new platform:</p-->
                        <form method="POST" action="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="name" class="form-label">name</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="name" value="" required />
                                    <p class="text-start"><small>descriptive name for the platform</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="name" class="form-label">jilo_database</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="jilo_database" value="" required />
                                    <p class="text-start"><small>path to the database file (relative to the app root)</small></p>
                                </div>
                            </div>

                            <input type="hidden" name="new" value="true" />

                            <br />
                            <a class="btn btn-secondary" href="<?= $app_root ?>?page=config" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
