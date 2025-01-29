
                <!-- remote config "<?= htmlspecialchars($livejsFile) ?>" -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-12 mb-4">
                            <h2 class="mb-0">Remote Jitsi config</h2>
                            <small>contents of the file "<strong><?= htmlspecialchars($livejsFile) ?></strong>"</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-4">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-file me-2 text-secondary"></i>
                                        <small><span class="text-muted"><?= htmlspecialchars($platformDetails[0]['jitsi_url']) ?>:</span> <?= htmlspecialchars($livejsFile) ?></small>
                                        <span class="card-text">
<?php if ($mode === 'raw') { ?>
                                            <span class="m-3"><a class="btn border btn-primary" href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=livejs&item=<?= htmlspecialchars($livejsFile) ?>">view only active lines</a></span>
<?php } else { ?>
                                            <span class="m-3"><a class="btn border btn-secondary" href="<?= htmlspecialchars($app_root) ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=livejs&item=<?= htmlspecialchars($livejsFile) ?>&mode=raw">view raw file contents</a></span>
<?php } ?>
                                        </span>
                                    </h5>

                                </div>
                                <div class="card-body">
                                    <pre class="results">
<?php
echo htmlspecialchars($livejsData);
?>
                                    </pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /remote config "<?= htmlspecialchars($livejsFile) ?>" -->
