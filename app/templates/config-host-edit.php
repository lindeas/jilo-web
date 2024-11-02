
                <!-- widget "hosts" -->
                <div class="card text-center w-50 mx-lef">
                    <p class="h4 card-header">Jilo configuration for Jitsi platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">edit host details:</p>
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=config&item=host">

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="address" class="form-label">address</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="address" value="<?= htmlspecialchars($hostDetails[0]['address'] ?? '') ?>" required autofocus />
                                    <p class="text-start"><small>DNS name or IP address of the machine</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="port" class="form-label">port</label>
                                    <span class="text-danger" style="margin-right: -12px;">*</span>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="port" value="<?= htmlspecialchars($hostDetails[0]['port'] ?? '') ?>" required />
                                    <p class="text-start"><small>port on which the Jilo Agent is listening</small></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4 text-end">
                                    <label for="name" class="form-label">name</label>
                                </div>
                                <div class="col-md-8">
                                    <input class="form-control" type="text" name="name" value="<?= htmlspecialchars($hostDetails[0]['name'] ?? '') ?>" />
                                    <p class="text-start"><small>description or name of the host (optional)</small></p>
                                </div>
                            </div>

                            <input type="hidden" name="platform" value="<?= htmlspecialchars($platform_id) ?>" />
                            <input type="hidden" name="item" value="host" />
                            <input type="hidden" name="host" value="<?= htmlspecialchars($hostDetails[0]['id']) ?>" />

                            <br />
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=host&platform=<?= htmlspecialchars($platform_id) ?>&host=<?= htmlspecialchars($host) ?>#platform<?= htmlspecialchars($platform_id) ?>host<?= htmlspecialchars($host) ?>" />Cancel</a>
                            &nbsp;&nbsp;
                            <input type="submit" class="btn btn-primary btn-sm" value="Save" />
                        </form>
                    </div>
                </div>
                <!-- /widget "hosts" -->
