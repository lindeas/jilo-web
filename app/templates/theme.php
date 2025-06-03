<?php
/**
 * Theme switcher template
 *
 * Displays available themes and allows the user to switch between them.
 *
 * @var array $themes List of available themes
 * @var string $currentTheme Currently active theme ID
 */
?>
                <div class="container mt-4">
                    <h2>Theme Switcher</h2>
                    <p class="text-muted">Select a theme to change the appearance of the application.</p>
                    <div class="row mt-4">
<?php foreach ($themes as $themeId => $themeName): ?>
<?php $isActive = $themeId === $currentTheme; ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 <?= $isActive ? 'border-primary' : '' ?>">
<?php if ($isActive) { ?>
                                <div class="card-header bg-primary text-white">Current Theme</div>
<?php } ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($themeName) ?></h5>
                                    <p class="card-text text-muted">Theme ID: <code><?= htmlspecialchars($themeId) ?></code></p>
                                    <div class="mt-auto">
<?php if (!$isActive) { ?>
                                        <a href="?page=theme&switch_to=<?= urlencode($themeId) ?>&csrf_token=<?= $csrf_token ?>" class="btn btn-primary">Switch to this theme</a>
<?php } else { ?>
                                        <button class="btn btn-outline-secondary" disabled>Currently active</button>
<?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
<?php endforeach; ?>
                    </div>
                </div>
