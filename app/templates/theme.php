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
<?php
$activeThemeName = 'Default';
foreach ($themes as $themeData) {
    if (!empty($themeData['isActive'])) {
        $activeThemeName = $themeData['name'];
        break;
    }
}
$userTimezone = \App\App::get('user_timezone') ?: 'UTC';
$totalThemes = count($themes);
?>

<section class="tm-directory tm-theme-directory">
    <div class="tm-hero-card tm-hero-card--stacked">
        <div class="tm-hero-head">
            <div class="tm-hero-body">
                <div class="tm-hero-heading">
                    <h1 class="tm-hero-title">Themes</h1>
                    <p class="tm-hero-subtitle">Personalize <?= htmlspecialchars($config['site_name']); ?> with custom visual styles.</p>
                </div>
                <div class="tm-hero-meta">
                    <span class="tm-hero-pill pill-neutral">
                        <i class="fas fa-layer-group"></i>
                        <?= $totalThemes ?> available
                    </span>
                    <span class="tm-hero-pill pill-primary">
                        <i class="fas fa-check-circle"></i>
                        Active: <?= htmlspecialchars($activeThemeName) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="tm-theme-gallery">
        <div class="tm-theme-grid">
<?php foreach ($themes as $themeId => $theme):
    $isActive = !empty($theme['isActive']);
    $screenshot = $theme['screenshotUrl'];
?>
            <article class="tm-theme-card<?= $isActive ? ' is-active' : '' ?>">
                <div class="tm-theme-preview" style="<?= $screenshot ? 'background-image: url(' . htmlspecialchars($screenshot) . ')' : '' ?>">
<?php if (!$screenshot): ?>
                    <span>No preview available</span>
<?php endif; ?>
                </div>
                <div class="tm-theme-body">
                    <div class="tm-theme-heading">
                        <p class="tm-theme-id">ID: <code><?= htmlspecialchars($themeId) ?></code></p>
                        <h3 class="tm-theme-name"><?= htmlspecialchars($theme['name']) ?></h3>
                    </div>
<?php if (!empty($theme['description'])): ?>
                    <p class="tm-theme-description">
                        <?= htmlspecialchars($theme['description']) ?>
                    </p>
<?php endif; ?>
                    <dl class="tm-theme-meta">
<?php if (!empty($theme['version'])): ?>
                        <div class="tm-theme-meta-item">
                            <dt>Version</dt>
                            <dd><?= htmlspecialchars($theme['version']) ?></dd>
                        </div>
<?php endif; ?>
<?php if (!empty($theme['author'])): ?>
                        <div class="tm-theme-meta-item">
                            <dt>Author</dt>
                            <dd><?= htmlspecialchars($theme['author']) ?></dd>
                        </div>
<?php endif; ?>
                    </dl>
<?php if (!empty($theme['tags'])): ?>
                    <ul class="tm-theme-tags">
<?php foreach ($theme['tags'] as $tag): $tagLabel = trim((string)$tag); if ($tagLabel === '') { continue; } ?>
                        <li><?= htmlspecialchars($tagLabel) ?></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
                    <dl class="tm-theme-stats">
<?php if (!empty($theme['type'])): ?>
                        <div class="tm-theme-stat">
                            <dt>Type</dt>
                            <dd><?= htmlspecialchars($theme['type']) ?></dd>
                        </div>
<?php endif; ?>
<?php if (!empty($theme['file_count'])): ?>
                        <div class="tm-theme-stat">
                            <dt>Files</dt>
                            <dd><?= number_format((int)$theme['file_count']) ?></dd>
                        </div>
<?php endif; ?>
<?php if (!empty($theme['path'])): ?>
                        <div class="tm-theme-stat">
                            <dt>Location</dt>
                            <dd><code><?= htmlspecialchars($theme['path']) ?></code></dd>
                        </div>
<?php endif; ?>
<?php if (!empty($theme['last_modified'])):
    $lastEditedRaw = is_numeric($theme['last_modified'])
        ? gmdate('Y-m-d H:i:s', (int)$theme['last_modified'])
        : $theme['last_modified'];
    $lastEdited = app_format_local_datetime($lastEditedRaw, 'M j, Y', $userTimezone) ?: $lastEditedRaw;
?>
                        <div class="tm-theme-stat">
                            <dt>Last edit</dt>
                            <dd><?= htmlspecialchars($lastEdited) ?></dd>
                        </div>
<?php endif; ?>
                    </dl>
                    <div class="tm-theme-actions">
<?php if ($isActive): ?>
                    <button class="btn btn-outline-secondary" disabled>
                        <i class="fas fa-check"></i> Active
                    </button>
<?php else: ?>
                    <a class="btn btn-primary" href="?page=theme&amp;switch_to=<?= urlencode($themeId) ?>&amp;csrf_token=<?= $csrf_token ?>">
                        <i class="fas fa-paint-brush"></i> Apply
                    </a>
<?php endif; ?>
                </div>
            </div>
        </article>
<?php endforeach; ?>
        </div>
    </div>
</section>
