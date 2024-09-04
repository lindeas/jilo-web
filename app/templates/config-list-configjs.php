
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Configuration of the Jitsi platform <strong><?= htmlspecialchars($platformDetails[0]['name']) ?></strong></p>
                    <div class="card-body">
                        <p class="card-text">
                            <span class="m-3">URL: <?= htmlspecialchars($platformDetails[0]['jitsi_url']) ?></span>
                            <span class="m-3">FILE: config.js</span>
<?php if ($mode === 'raw') { ?>
                            <span class="m-3"><a class="btn btn-light" href="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=configjs">view only active lines</a></span>
<?php } else { ?>
                            <span class="m-3"><a class="btn btn-light" href="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=config&item=configjs&mode=raw">view raw file contents</a></span>
<?php } ?>
                        </p>
<pre style="text-align: left;">
<?php
echo htmlspecialchars($platformConfigjs);
?>
</pre>
                    </div>
                </div>
                <!-- /widget "config" -->
