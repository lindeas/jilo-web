
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration file</p>
                    <div class="card-body">

<?php
include '../app/helpers/render.php';
renderConfig($config, '0');
echo "\n";
?>
                        <br />
                        <a class="btn btn-secondary" href="<?= htmlspecialchars($app_root) ?>?page=config&item=config_file&action=edit" />Edit</a>

                    </div>
                </div>
                <!-- /widget "config" -->
