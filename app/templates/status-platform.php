
                <!-- jitsi platform status -->
                <br />
                <div class="card text-center w-75 mx-lef" style="padding-left: 40px;">
                    <div class="card-body">
                        <p class="card-text text-left" style="text-align: left;">
                            Jitsi Meet platform: <a href="<?= htmlspecialchars($app_root) ?>?page=config#platform<?= htmlspecialchars($platform['id']) ?>"><?= htmlspecialchars($platform['name']) ?></a>
                            <br />
                            jilo database: <strong><?= htmlspecialchars($platform['jilo_database']) ?></strong>,
                            status: <strong><?= $jilo_database_status ?></strong>
                        </p>
                    </div>
                </div>
