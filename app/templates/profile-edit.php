
                <!-- user profile -->
                <div class="card text-center w-50 mx-auto">

                    <p class="h4 card-header">Profile of <?= $userDetails[0]['username'] ?></p>
                    <div class="card-body">

                        <div class="row">
                            <p class="border rounded bg-light mb-4"><small>edit the profile fields</small></p>

                            <div class="col-md-4 avatar-container">
                                <div>
                                    <img class="avatar-img" src="<?= $app_root . htmlspecialchars($avatar) ?>" alt="avatar" />

                                    <form method="POST" action="<?= $app_root ?>?page=profile&action=edit&item=avatar" enctype="multipart/form-data">
                                        <label for="avatar-upload" class="avatar-btn btn btn-primary"><small>select avatar</small></label>
                                        <input type="file" id="avatar-upload" name="avatar_file" accept="image/*" style="display:none;">
                                        <input type="submit" class="avatar-btn btn btn-success" value="upload new avatar">
                                    </form>

                                    <form method="POST" action="<?= $app_root ?>?page=profile&action=remove&item=avatar">
                                        <input type="submit" id="avatar-remove" class="avatar-btn btn btn-danger" value="remove avatar" />
                                    </form>
                                </div>
                            </div>

                            <div class="col-md-8">

                                <form method="POST" action="<?= $app_root ?>?page=profile">

                                    <!--div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="username" class="form-label"><small>username:</small></label>
                                            <span class="text-danger" style="margin-right: -12px;">*</span>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <input class="form-control" type="text" name="username" value="<?= $userDetails[0]['username'] ?>" required />
                                        </div>
                                    </div-->

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
                                        <a href="<?= $app_root ?>?page=profile" class="btn btn-secondary">Cancel</a>
                                        <input type="submit" class="btn btn-primary" value="Save" />
                                    </p>

                            </div>
                        </form>
                    </div>
                </div>
                <!-- /user profile -->

<script>
document.getElementById('avatar-upload').addEventListener('change', function(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.querySelector('.avatar-img').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
});
</script>
