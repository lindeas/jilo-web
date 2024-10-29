
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo configuration</p>
                    <p class="h6 card-header">
                        <span class="btn btn-outline-primary btn-sm active" aria-pressed="true" style="cursor: default;">platforms</span>
                        <a href="" class="btn btn-outline-primary btn-sm">hosts</a>
                        <a href="" class="btn btn-outline-primary btn-sm">endpoints</a>
                        &nbsp;&nbsp;
                        <a href="" class="btn btn-outline-primary btn-sm">config file</a>
                    </p>
                    <div class="card-body">
                        <p class="card-text">main variables</p>
<?php
include '../app/helpers/render.php';
renderConfig($config, '0');
echo "\n";
?>

                    </div>
                </div>
                <!-- /widget "config" -->
