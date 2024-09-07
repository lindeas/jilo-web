
                <!-- user profile -->
                <div class="card text-center w-50 mx-auto">

                    <p class="h4 card-header">Profile of <?= $userDetails[0]['username'] ?></p>
                    <div class="card-body">

                        <div class="row">
                            <p class="border rounded bg-light mb-4"><small>edit the profile fields</small></p>

                            <div class="col-md-4">
                                <div class="border" style="width: 200px; height: 200px;"><img src="" alt="avatar" /></div>
                            </div>

                            <div class="col-md-8">

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label for="username" class="form-label"><small>username:</small></label>
                                        <span class="text-danger" style="margin-right: -12px;">*</span>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <input class="form-control" type="text" name="username" value="<?= $userDetails[0]['username'] ?>" required />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label for="name" class="form-label"><small>name:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <input class="form-control" type="text" name="name" value="<?= $userDetails[0]['name'] ?>" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label for="email" class="form-label"><small>email:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <input class="form-control" type="text" name="email" value="<?= $userDetails[0]['email'] ?>" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label for="bio" class="form-label"><small>bio:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <textarea class="form-control" name="bio" rows="10"><?= $userDetails[0]['bio'] ?? '' ?></textarea>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 text-end">
                                        <label for="rights" class="form-label"><small>rights:</small></label>
                                    </div>
                                    <div class="col-md-8 text-start bg-light">
                                        <input class="form-control" type="text" name="rights" value="<?= $userDetails[0]['rights'] ?? '' ?>" />
                                    </div>
                                </div>

                            </div>

                                <p>
                                    <a href="<?= $app_root ?>?page=profile" class="btn btn-primary">Cancel</a>
                                    <a href="<?= $app_root ?>?page=profile&action=edit" class="btn btn-danger">Save</a>
                                </p>

                        </div>

                    </div>
                </div>
                <!-- /user profile -->
