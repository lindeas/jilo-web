
                <!-- widget "agents" -->
                <div class="card text-center w-75 mx-lef">
                    <p class="h4 card-header">Jilo Agents on platform <strong>"<?= htmlspecialchars($platformDetails[0]['name']) ?>"</strong></p>
                    <div class="card-body">
                        <p class="card-text">agents configuration &nbsp;<a class="btn btn-secondary" style="padding: 0px;" href="<?= $app_root ?>?platform=<?= htmlspecialchars($platform_id) ?>&page=agents&action=add">add</a></p>

<?php foreach ($agentDetails as $agent_array) { ?>

                        <div class="row mb-3" style="padding-left: 0px;">
                            <div class="border bg-light" style="padding-left: 50px; padding-bottom: 20px; padding-top: 20px;">
                                <div class="row mb-1" style="padding-left: 0px;">
                                    <div class="col-md-4 text-end">
                                        agent id <?= $agent_array['id'] ?>:
                                    </div>
                                    <div class="col-md-8 text-start">
                                        <a class="btn btn-secondary" style="padding: 2px;" href="<?= $app_root ?>?platform=<?= htmlspecialchars($agent_array['platform_id']) ?>&page=agents&agent=<?= htmlspecialchars($agent_array['id']) ?>&action=edit">edit</a>
                                        <a class="btn btn-danger" style="padding: 2px;" href="<?= $app_root ?>?platform=<?= htmlspecialchars($agent_array['platform_id'])?>&page=agents&agent=<?= htmlspecialchars($agent_array['id']) ?>&action=delete">delete</a>
                                    </div>
                                    <div style="padding-left: 100px; padding-bottom: 20px;">
<?php foreach ($agent_array as $key => $value) {
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
                <!-- /widget "agents" -->
