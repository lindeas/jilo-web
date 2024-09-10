
                <!-- user profile -->
                <div class="card text-center w-50 mx-auto">

                    <p class="h4 card-header">Profile of <?= $userDetails[0]['username'] ?></p>
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4 avatar-container">
                                <div>
                                    <img class="avatar-img" src="<?= $app_root . htmlspecialchars($avatar) ?>" alt="avatar" />
                                </div>
                            </div>

                            <div class="col-md-8">

                                <!--div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>username:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= $userDetails[0]['username'] ?>
                                    </div>
                                </div-->

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>name:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= $userDetails[0]['name'] ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>email:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= $userDetails[0]['email'] ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>bio:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <textarea class="scroll-box" rows="10" readonly><?= $userDetails[0]['bio'] ?? '' ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label"><small>rights:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <?= $userDetails[0]['rights'] ?? '' ?>
                                    </div>
                                </div>

                            </div>

                                <p>
                                    <a href="<?= $app_root ?>?page=profile&action=edit" class="btn btn-primary">Edit</a>
                                </p>

                        </div>

                    </div>
                </div>
                <!-- /user profile -->
