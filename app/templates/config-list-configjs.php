
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Configuration of the Jitsi platform <strong><?= htmlspecialchars($platformDetails['name']) ?></strong></p>
                    <div class="card-body">
                        <p class="card-text">URL: <?= htmlspecialchars($platformDetails['jitsi_url']) ?></p>
                        <p class="card-text">config.js</p>
<pre style="text-align: left;">
<?php
echo htmlspecialchars($platformConfigjs);
?>
</pre>
                    </div>
                </div>
                <!-- /widget "config" -->
