
                <!-- widget "config" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo web configuration</p>
                    <div class="card-body">
                        <p class="card-text">main variables</p>
<?php
include '../app/helpers/render.php';
renderConfig($config, '0');
echo "\n";
?>

<hr />
                        <p class="card-text">platforms configuration &nbsp;<a class="btn btn-secondary" style="padding: 0px;" href="/jilo-web/?page=config&action=add">add</a></p>

<?php foreach ($platformsAll as $platform_array) { ?>

                        <div class="row mb-3" style="padding-left: 0px;">
                            <div class="border bg-light" style="padding-left: 50px; padding-bottom: 20px; padding-top: 20px;">
                                <div class="row mb-1" style="padding-left: 0px;">
                                    <div class="col-md-4 text-end">
                                        <?= $platform_array['id'] ?>:
                                    </div>
                                    <div class="col-md-8 text-start">
                                        <a class="btn btn-secondary" style="padding: 2px;" href="/jilo-web/?platform=<?= htmlspecialchars($platform_array['id']) ?>&page=config&action=edit">edit</a>
<?php if (count($platformsAll) <= 1) { ?>
                                        <span class="btn btn-light" style="padding: 2px;" href="#" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="can't delete the last platform">delete</span>
<?php } else { ?>
                                        <a class="btn btn-danger" style="padding: 2px;" href="/jilo-web/?platform=<?= htmlspecialchars($platform_array['id'])?>&page=config&action=delete">delete</a>
<?php } ?>
                                    </div>
                                    <div style="padding-left: 100px; padding-bottom: 20px;">
<?php foreach ($platform_array as $key => $value) {
        if ($key === 'id') continue;
?>
                                        <div class="row mb-1" style="padding-left: 100px;">
                                            <div class="col-md-4 text-end">
                                                <?= $key ?>:
                                            </div>
                                            <div class="border col-md-8 text-start">
                                                <?= $value ?>
                                            </div>
                                        </div>
<?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php } ?>


                    </div>
                </div>
                <!-- /widget "config" -->
