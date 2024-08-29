
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Configuration of the Jitsi platform <strong><?= htmlspecialchars($platformDetails['name']) ?></strong></p>
                    <div class="card-body">
                        <p class="card-text">URL: <?= htmlspecialchars($platformDetails['jitsi_url']) ?></p>
                        <p class="card-text">interface_config.js</p>
<pre style="text-align: left;">
<?php
echo htmlspecialchars($platformInterfaceConfigjs);
?>
</pre>
                    </div>
                </div>
                <!-- /widget "config" -->
