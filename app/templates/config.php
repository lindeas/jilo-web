
                <!-- config file -->
                <div class="container-fluid mt-2">
                    <div class="row mb-4">
                        <div class="col-12 mb-4">
                            <h2>Configuration</h2>
                            <small>Jilo Web configuration file <?= htmlspecialchars($config_file) ?></small>
<?php if ($configMessage) { ?>
                                <?= $configMessage ?>
<?php } ?>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-wrench me-2 text-secondary"></i>
                                Jilo Web app configuration
                            </h5>
<?php if ($userObject->hasRight($user_id, 'edit config file')) { ?>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-sm toggle-edit" <?= !$isWritable ? 'disabled' : '' ?>>
                                    <i class="fas fa-edit me-2"></i>Edit
                                </button>
                                <div class="edit-controls d-none">
                                    <button type="button" class="btn btn-danger btn-sm save-config">
                                        <i class="fas fa-save me-2"></i>Save
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm ms-2 cancel-edit">
                                        Cancel
                                    </button>
                                </div>
                            </div>
<?php } ?>
                        </div>

                        <div class="card-body p-4">
                            <form id="configForm">
<?php
function renderConfigItem($key, $value, $path = '') {
    $fullPath = $path ? $path . '[' . $key . ']' : $key;
    // Only capitalize first letter, not every word
    $displayName = ucfirst(str_replace('_', ' ', $key));

    if (is_array($value)) {
        echo "\t\t\t\t\t\t\t\t<div class=\"config-section mb-4\">";
        echo "\n\t\t\t\t\t\t\t\t\t<h6 class=\"border-bottom pb-2 mb-3\">" . htmlspecialchars($displayName) . '</h6>';
        echo "\n\t\t\t\t\t\t\t\t\t<div class=\"ps-4\">\n";
        foreach ($value as $subKey => $subValue) {
            renderConfigItem($subKey, $subValue, $fullPath);
        }
        echo "\t\t\t\t\t\t\t\t\t</div>\n";
        echo "\t\t\t\t\t\t\t\t</div>\n";
    } else {
?>
                                <div class="config-item row mb-3 align-items-center">
                                    <div class="col-md-4 text-end">
                                        <label class="form-label mb-0"><?= htmlspecialchars($displayName) ?></label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="view-mode">
<?php if (is_bool($value) || $key === 'registration_enabled') { ?>
                                            <span class="badge <?= $value ? 'bg-success' : 'bg-secondary' ?>"><?= $value ? 'Enabled' : 'Disabled' ?></span>
<?php } elseif ($key === 'environment') { ?>
                                            <span class="badge <?= $value === 'production' ? 'bg-danger' : 'bg-info' ?>"><?= htmlspecialchars($value) ?></span>
<?php } else {
        if (empty($value) && $value !== '0') { ?>
                                            <span class="text-muted fst-italic">blank</span>
<?php   } else { ?>
                                            <span class="text-body"><?= htmlspecialchars($value) ?></span>
<?php   } ?>
<?php } ?>
                                        </div>
                                        <div class="edit-mode d-none">
<?php if (is_bool($value) || $key === 'registration_enabled') { ?>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="<?= htmlspecialchars($fullPath) ?>" <?= $value ? 'checked' : '' ?>>
                                            </div>
<?php } elseif ($key === 'environment') { ?>
                                            <select class="form-select form-select-sm" name="<?= htmlspecialchars($fullPath) ?>">
                                                <option value="development" <?= $value === 'development' ? 'selected' : '' ?>>Development</option>
                                                <option value="production" <?= $value === 'production' ? 'selected' : '' ?>>Production</option>
                                            </select>
<?php } else { ?>
                                            <input type="text" class="form-control form-control-sm" name="<?= htmlspecialchars($fullPath) ?>" value="<?= htmlspecialchars($value ?? '') ?>">
<?php } ?>
                                        </div>
                                    </div>
                                </div>
<?php }
    }
    foreach ($config as $key => $value) {
        renderConfigItem($key, $value);
    } ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

<script>
$(function() {
    // Toggle edit mode
    $('.toggle-edit').click(function() {
        $(this).hide();
        $('.edit-controls').removeClass('d-none');
        $('.view-mode').hide();
        $('.edit-mode').removeClass('d-none');
    });

    // Cancel edit
    $('.cancel-edit').click(function() {
        $('.toggle-edit').show();
        $('.edit-controls').addClass('d-none');
        $('.view-mode').show();
        $('.edit-mode').addClass('d-none');
    });

    // Save config
    $('.save-config').click(function() {
        const $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

        // Build form data object
        const data = {};

        // Handle text inputs
        $('#configForm input[type="text"]').each(function() {
            const name = $(this).attr('name');
            data[name] = $(this).val();
        });

        // Handle checkboxes
        $('#configForm input[type="checkbox"]').each(function() {
            const name = $(this).attr('name');
            data[name] = $(this).prop('checked') ? '1' : '0';
        });

        // Handle selects
        $('#configForm select').each(function() {
            const name = $(this).attr('name');
            data[name] = $(this).val();
        });

        $.ajax({
            url: '<?= htmlspecialchars($app_root) ?>?page=config',
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Show message first
                if (response.messageData) {
                    JsMessages.success(response.messageData['message']);
                }

                // Only update UI if save was successful
                if (response.success) {
                    // Update view mode values
                    Object.entries(data).forEach(([key, value]) => {
                        const $item = $(`[name="${key}"]`).closest('.config-item');
                        const $viewMode = $item.find('.view-mode');

                        if ($item.length) {
                            if ($item.find('input[type="checkbox"]').length) {
                                // Boolean value
                                const isEnabled = value === '1';
                                $viewMode.html(`
                                    <span class="badge ${isEnabled ? 'bg-success' : 'bg-secondary'}">
                                        ${isEnabled ? 'Enabled' : 'Disabled'}
                                    </span>
                                `);
                            } else if ($item.find('select').length) {
                                // Environment value
                                $viewMode.html(`
                                    <span class="badge ${value === 'production' ? 'bg-danger' : 'bg-info'}">
                                        ${value}
                                    </span>
                                `);
                            } else {
                                // Text value
                                if (value === '') {
                                    $viewMode.html('<span class="text-muted fst-italic">blank</span>');
                                } else {
                                    $viewMode.html(`<span class="text-body">${value}</span>`);
                                }
                            }
                        }
                    });
                    // Finally, exit edit mode
                    $('.toggle-edit').show();
                    $('.edit-controls').addClass('d-none');
                    $('.view-mode').show();
                    $('.edit-mode').addClass('d-none');
                }
            },
            error: function(xhr, status, error) {
                JsMessages.error('Error saving config: ' + error);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Save');
            }
        });
    });
});
</script>
<!-- /config file -->
