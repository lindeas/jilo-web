
                <!-- user profile -->
                <div class="card text-center w-50 mx-auto">

                    <p class="h4 card-header">Profile of <?= htmlspecialchars($userDetails[0]['username']) ?></p>
                    <div class="card-body">

                        <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=profile" enctype="multipart/form-data">
                            <div class="row">
                                <p class="border rounded bg-light mb-4"><small>edit the profile fields</small></p>
                                <div class="col-md-4 avatar-container">
                                    <div class="avatar-wrapper">
                                        <img class="avatar-img" src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="avatar" />
        <div class="avatar-btn-container">

                                        <label for="avatar-upload" class="avatar-btn avatar-btn-select btn btn-primary">
                                            <i class="fas fa-folder" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="select new avatar"></i>
                                        </label>
                                        <input type="file" id="avatar-upload" name="avatar_file" accept="image/*" style="display:none;">

<?php if ($default_avatar) { ?>
                                        <button type="button" class="avatar-btn avatar-btn-remove btn btn-secondary" data-toggle="modal" data-target="#confirmDeleteModal" disabled>
<?php } else { ?>
                                        <button type="button" class="avatar-btn avatar-btn-remove btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal">
<?php } ?>
                                        <i class="fas fa-trash" data-toggle="tooltip" data-placement="right" data-offset="30.0" title="remove current avatar"></i>
                                        </button>
        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <!--div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="username" class="form-label"><small>username:</small></label>
                                            <span class="text-danger" style="margin-right: -12px;">*</span>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <input class="form-control" type="text" name="username" value="<?= htmlspecialchars($userDetails[0]['username']) ?>" required />
                                        </div>
                                    </div-->

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="name" class="form-label"><small>name:</small></label>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <input class="form-control" type="text" name="name" value="<?= htmlspecialchars($userDetails[0]['name'] ?? '') ?>" autofocus />
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="email" class="form-label"><small>email:</small></label>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <input class="form-control" type="text" name="email" value="<?= htmlspecialchars($userDetails[0]['email'] ?? '') ?>" />
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="timezone" class="form-label"><small>timezone:</small></label>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <select class="form-control" name="timezone" id="timezone">
<?php foreach ($allTimezones as $timezone) { ?>
                                                <option value="<?= htmlspecialchars($timezone) ?>" <?= $timezone === $userTimezone ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($timezone) ?>&nbsp;&nbsp;(<?= htmlspecialchars(getUTCOffset($timezone)) ?>)
                                                </option>
<?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="bio" class="form-label"><small>bio:</small></label>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
                                            <textarea class="form-control" name="bio" rows="10"><?= htmlspecialchars($userDetails[0]['bio'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-4 text-end">
                                            <label for="rights" class="form-label"><small>rights:</small></label>
                                        </div>
                                        <div class="col-md-8 text-start bg-light">
<?php foreach ($allRights as $right) {
    // Check if the current right exists in $userRights
    $isChecked = false;
    foreach ($userRights as $userRight) {
        if ($userRight['right_id'] === $right['right_id']) {
            $isChecked = true;
            break;
        }
    } ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="rights[]" value="<?= htmlspecialchars($right['right_id']) ?>" id="right_<?= htmlspecialchars($right['right_id']) ?>" <?= $isChecked ? 'checked' : '' ?> />
                                                <label class="form-check-label" for="right_<?= htmlspecialchars($right['right_id']) ?>"><?= htmlspecialchars($right['right_name']) ?></label>
                                            </div>
<?php } ?>
                                        </div>
                                    </div>

                                </div>

                                <p>
                                    <a href="<?= htmlspecialchars($app_root) ?>?page=profile" class="btn btn-secondary">Cancel</a>
                                    <input type="submit" class="btn btn-primary" value="Save" />
                                </p>

                            </div>
                        </form>

                                        <!-- avatar removal modal confirmation -->
                                        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Avatar Deletion</h5>
                                                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img class="avatar-img" src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="avatar" />
                                                        <br />
                                                        Are you sure you want to delete your avatar?
                                                        <br />
                                                        This action cannot be undone.
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <form id="remove-avatar-form" data-action="remove-avatar" method="POST" action="<?= htmlspecialchars($app_root) ?>?page=profile&action=remove&item=avatar">
                                                            <button type="button" class="btn btn-danger" id="confirm-delete">Delete Avatar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                    </div>
                </div>
                <!-- /user profile -->

<script>
// Preview the uploaded avatar
document.getElementById('avatar-upload').addEventListener('change', function(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.querySelector('.avatar-img').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
});

// Avatar file size and type control
document.getElementById('avatar-upload').addEventListener('change', function() {
    const maxFileSize = 500 * 1024; // 500 KB in bytes
    const currentAvatar = '<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>'; // current avatar
    const file = this.files[0];

    if (file) {
        // Check file size
        if (file.size > maxFileSize) {
            alert('File size exceeds 500 KB. Please select a smaller file.');
            this.value = '';  // Clear the file input
            document.querySelector('.avatar-img').src = currentAvatar;
        }
    }
});

// Submitting the avatar deletion confirmation modal form
document.getElementById('confirm-delete').addEventListener('click', function(event) {
    event.preventDefault();  // Prevent the outer form from submitting
    document.getElementById('remove-avatar-form').submit();
});

// Function to detect user's timezone and select it in the dropdown
function setTimezone() {
    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const timezoneSelect = document.getElementById("timezone");
    timezoneSelect.className = 'form-control border border-danger';

    // Loop through the options to find and select the user's timezone
    for (let i = 0; i < timezoneSelect.options.length; i++) {
        if (timezoneSelect.options[i].value === userTimezone) {
            timezoneSelect.selectedIndex = i;
            break;
        }
    }
}
// Run the function on page load
window.onload = function() {
    const isTimezoneSet = <?php echo json_encode($isTimezoneSet); ?>; // Pass PHP flag to JavaScript
    // If timezone is not set, run setTimezone()
    if (!isTimezoneSet) {
        setTimezone();
    }
};

</script>
