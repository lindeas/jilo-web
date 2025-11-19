
                <!-- user profile -->
                <div class="tm-profile-card mx-auto">
                    <div class="tm-profile-header">
                        <div>
                            <p class="tm-profile-eyebrow">Account</p>
                            <h2 class="tm-profile-title">Profile of <?= htmlspecialchars($userDetails[0]['username']) ?></h2>
                            <p class="tm-profile-subtitle">Update your personal details, avatar, and access rights in one streamlined view.</p>
                        </div>
                    </div>

                    <form method="POST" action="<?= htmlspecialchars($app_root) ?>?page=profile" enctype="multipart/form-data" class="tm-profile-form" novalidate>
<?php include CSRF_TOKEN_INCLUDE; ?>
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-4">
                                <div class="tm-profile-avatar card h-100">
                                    <div class="avatar-wrapper">
                                        <img class="avatar-img" src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="avatar" />
                                    </div>
                                    <div class="avatar-btn-group">
                                        <label for="avatar-upload" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-upload me-2"></i>Upload new
                                        </label>
                                        <input type="file" id="avatar-upload" name="avatar_file" accept="image/*" style="display:none;">
<?php if ($default_avatar) { ?>
                                        <button type="button" class="btn btn-outline-secondary w-100" data-toggle="modal" data-target="#confirmDeleteModal" disabled>
<?php } else { ?>
                                        <button type="button" class="btn btn-outline-danger w-100" data-toggle="modal" data-target="#confirmDeleteModal">
<?php } ?>
                                            <i class="fas fa-trash me-2"></i>Remove avatar
                                        </button>
                                    </div>
                                    <p class="avatar-hint">PNG, JPG up to 500 KB.</p>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="tm-profile-section">
                                    <h3 class="tm-profile-section-title">Personal info</h3>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="name" class="form-label">Full name</label>
                                            <input class="form-control" type="text" name="name" id="name" value="<?= htmlspecialchars($userDetails[0]['name'] ?? '') ?>" autofocus />
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email address</label>
                                            <input class="form-control" type="text" name="email" id="email" value="<?= htmlspecialchars($userDetails[0]['email'] ?? '') ?>" />
                                        </div>
                                    </div>
                                </div>

                                <div class="tm-profile-section">
                                    <h3 class="tm-profile-section-title">Timezone</h3>
                                    <label for="timezone" class="form-label">Preferred timezone</label>
                                    <select class="form-control" name="timezone" id="timezone">
<?php foreach ($allTimezones as $timezone) { ?>
                                        <option value="<?= htmlspecialchars($timezone) ?>" <?= $timezone === $userTimezone ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($timezone) ?>&nbsp;&nbsp;(<?= htmlspecialchars(getUTCOffset($timezone)) ?>)
                                        </option>
<?php } ?>
                                    </select>
                                </div>

                                <div class="tm-profile-section">
                                    <h3 class="tm-profile-section-title">Bio</h3>
                                    <textarea class="form-control" name="bio" rows="6" placeholder="Share something about yourself, your role, or preferences."><?= htmlspecialchars($userDetails[0]['bio'] ?? '') ?></textarea>
                                </div>

                                <div class="tm-profile-section">
                                    <h3 class="tm-profile-section-title">Rights</h3>
                                    <p class="tm-profile-section-helper">Toggle the permissions that should be associated with this user.</p>
                                    <div class="tm-rights-grid">
<?php foreach ($allRights as $right) {
    $isChecked = false;
    foreach ($userRights as $userRight) {
        if ($userRight['right_id'] === $right['right_id']) {
            $isChecked = true;
            break;
        }
    } ?>
                                        <div class="form-check tm-right-item">
                                            <input class="form-check-input" type="checkbox" name="rights[]" value="<?= htmlspecialchars($right['right_id']) ?>" id="right_<?= htmlspecialchars($right['right_id']) ?>" <?= $isChecked ? 'checked' : '' ?> />
                                            <label class="form-check-label" for="right_<?= htmlspecialchars($right['right_id']) ?>"><?= htmlspecialchars($right['right_name']) ?></label>
                                        </div>
<?php } ?>
                                    </div>
                                </div>

                                <div class="tm-profile-actions">
                                    <a href="<?= htmlspecialchars($app_root) ?>?page=profile" class="btn btn-light tm-contact-back">Cancel</a>
                                    <button type="submit" class="btn btn-primary tm-contact-submit">Save changes</button>
                                </div>
                            </div>
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
                                <div class="modal-body text-center">
                                    <img class="avatar-img" src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="avatar" />
                                    <p class="mt-3 mb-0">Are you sure you want to delete your avatar?<br />This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <form id="remove-avatar-form" data-action="remove-avatar" method="POST" action="<?= htmlspecialchars($app_root) ?>?page=profile&action=remove&item=avatar">
<?php include CSRF_TOKEN_INCLUDE; ?>
                                        <button type="button" class="btn btn-danger" id="confirm-delete">Delete Avatar</button>
                                    </form>
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
