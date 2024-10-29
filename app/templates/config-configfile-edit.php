
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration file</p>
                    <div class="card-body">
                        <p class="card-text">editing the Jilo Server configuration file
                            <span class="text-danger">this may break everything, use with extreme caution</span>
                        </p>
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=config_file">

<?php
include '../app/helpers/render.php';
editConfig($config, '0');
echo "\n";
?>

                            <br />
                            <a class="btn btn-secondary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=config_file" />Cancel</a>
                            <input type="submit" class="btn btn-primary" value="Save" />
                        </form>


                    </div>
                </div>
                <!-- /widget "config" -->
