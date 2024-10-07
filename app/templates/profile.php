
                <!-- user profile -->
                <div class="card text-center w-50 mx-auto">

                    <p class="h4 card-header">Profile of <?= htmlspecialchars($userDetails[0]['username']) ?></p>
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4 avatar-container">
                                <div>
                                    <img class="avatar-img" src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="avatar" />
                                </div>
                            </div>

                            <div class="col-md-8">

                                <!--div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>username:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= htmlspecialchars($userDetails[0]['username']) ?>
                                    </div>
                                </div-->

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>name:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= htmlspecialchars($userDetails[0]['name']) ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>email:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= htmlspecialchars($userDetails[0]['email']) ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>timezone:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
<?php if (isset($userDetails[0]['timezone'])) { ?>
                                        <?= htmlspecialchars($userDetails[0]['timezone']) ?>&nbsp;&nbsp;<span style="font-size: 0.66em;">(<?= htmlspecialchars(getUTCOffset($userDetails[0]['timezone'])) ?>)</span>
<?php } ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>bio:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <textarea class="scroll-box" rows="10" readonly><?= htmlspecialchars($userDetails[0]['bio'] ?? '') ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>rights:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
<?php foreach ($userRights as $right) { ?>
                                        <?= htmlspecialchars($right['right_name'] ?? '') ?>
                                        <br />
<?php } ?>
                                    </div>
                                </div>

                            </div>

                                <p>
                                    <a href="<?= htmlspecialchars($app_root) ?>?page=profile&action=edit" class="btn btn-primary">Edit</a>
                                </p>

                        </div>

                    </div>
                </div>
                <!-- /user profile -->
