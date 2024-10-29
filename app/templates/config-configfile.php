
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration file</p>
                    <div class="card-body">

<?php
include '../app/helpers/render.php';
renderConfig($config, '0');
echo "\n";
?>

                    </div>
                </div>
                <!-- /widget "config" -->
