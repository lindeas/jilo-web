<?php
/**
 * Theme switcher template
 *
 * Displays available themes and allows the user to switch between them.
 *
 * @var array $themes List of available themes with their data
 *   - name: Display name
 *   - screenshotUrl: URL to the screenshot (or null if not available)
 *   - isActive: Whether this is the current theme
 */
?>
                <div class="container mt-4">
                    <h2>Theme switcher</h2>
                    <p class="text-muted">Select a theme to change the appearance of the application.</p>
                    <div class="row mt-4">
                        <?php foreach ($themes as $themeId => $theme): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 <?= $theme['isActive'] ? 'border-primary' : '' ?>">
                                <!-- Theme screenshot -->
                                <div class="theme-screenshot" style="height: 150px; background-size: cover; background-position: center; background-color: #f8f9fa; <?= $theme['screenshotUrl'] ? 'background-image: url(' . htmlspecialchars($theme['screenshotUrl']) . ')' : '' ?>">
                                    <?php if (!$theme['screenshotUrl']): ?>
                                        <div class="h-100 d-flex align-items-center justify-content-center text-muted">No preview available</div>
                                    <?php endif; ?>
                                </div>
                                <?php if ($theme['isActive']): ?>
                                <div class="card-header bg-primary text-white">Current theme</div>
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($theme['name']) ?></h5>
                                    <p class="card-text text-muted">Theme ID: <code><?= htmlspecialchars($themeId) ?></code></p>
                                    <div class="mt-auto">
                                        <?php if (!$theme['isActive']): ?>
                                        <a href="?page=theme&switch_to=<?= urlencode($themeId) ?>&csrf_token=<?= $csrf_token ?>" class="btn btn-primary">Switch to this theme</a>
                                        <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled>Currently active</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
