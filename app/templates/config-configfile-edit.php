
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration file :: edit</p>
                    <div class="card-body">
                        <div class="card-text">
                            <p class="text-danger"><strong>this may break everything, use with extreme caution</strong></p>
                        </div>
                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=config_file">

<?php
include '../app/helpers/render.php';
editConfig($config, '0');
echo "\n";
?>

                            <p class="text-danger"><strong>this may break everything, use with extreme caution</strong></p>
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars($app_root) ?>?page=config&item=config_file" />Cancel</a>
                            &nbsp;&nbsp;
                            <input type="submit" class="btn btn-danger btn-sm" value="Save" />
                        </form>


                    </div>
                </div>
                <!-- /widget "config" -->
