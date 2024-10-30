
                <!-- widget "config" -->
                <div class="card text-center w-50 mx-auto">
                    <p class="h4 card-header">Add new host in Jitsi platform <strong><?= htmlspecialchars($platformDetails[0]['name']) ?></strong></p>
                    <div class="card-body">
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=config&item=host">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="address" class="form-label">address</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="address" value="" required autofocus />
                                    <p class="text-start"><small>DNS name or IP address of the machine</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="port" class="form-label">port</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="port" value="" required />
                                    <p class="text-start"><small>port on which the Jilo Agent is listening</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="name" class="form-label">name</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="name" value="" />
                                    <p class="text-start"><small>description or name of the host (optional)</small></p>
                                </div>
                            </div>
                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platformDetails[0]['id'])?>" />
                            <input type="hidden" name="item" value="host" />
                            <input type="hidden" name="new" value="true" />

                            <br />
                            <a class="btn btn-secondary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "config" -->
