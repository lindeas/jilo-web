
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
                                        <label for="avatar-upload" class="avatar-btn btn btn-primary"><small>select new</small></label>
                                        <input type="file" id="avatar-upload" name="avatar_file" accept="image/*" style="display:none;">
                                        <input type="submit" id="avatar-upload-button" class="avatar-btn btn btn-secondary" value="upload" disabled>
                                    </form>

                                    <form id="remove-avatar-form" method="POST" action="<?= $app_root ?>?page=profile&action=remove&item=avatar">
<?php if ($default_avatar) { ?>
                                        <button type="button" class="avatar-btn btn btn-secondary" data-toggle="modal" data-target="#confirmDeleteModal" disabled>
<?php } else { ?>
                                        <button type="button" class="avatar-btn btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal">
<?php } ?>
                                            <small>remove current avatar</small>
                                        </button>
                                    </form>
                                    <!-- avatar removal moda confirmation -->
                                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Avatar Deletion</h5>
                                                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete your avatar? This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-danger" id="confirm-delete">Delete Avatar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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

const maxFileSize = 500 * 1024; // 500 KB in bytes
const currentAvatar = '<?= $app_root . htmlspecialchars($avatar) ?>'; // current avatar
document.getElementById('avatar-upload').addEventListener('change', function() {
    const uploadButton = document.getElementById('avatar-upload-button');
    const file = this.files[0];

    if (file) {
        // Check file size
        if (file.size > maxFileSize) {
            alert('File size exceeds 500 KB. Please select a smaller file.');
            this.value = '';  // Clear the file input
            uploadButton.disabled = true;  // Keep the upload button disabled
            uploadButton.className = 'avatar-btn btn btn-secondary';
            document.querySelector('.avatar-img').src = currentAvatar;
        } else {
            // Enable the upload button if the file size is valid
            uploadButton.disabled = false;
            uploadButton.className = 'avatar-btn btn btn-success';
        }
    } else {
        uploadButton.disabled = true;  // Disable the button if no file is selected
        uploadButton.className = 'avatar-btn btn btn-secondary';
    }
});

document.getElementById('confirm-delete').addEventListener('click', function() {
    // Submit the form when the user confirms deletion
    document.getElementById('remove-avatar-form').submit();
});
</script>
