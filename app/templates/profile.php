
<?php
$user = $userDetails[0] ?? [];
$username = $user['username'] ?? '';
$name = $user['name'] ?? '';
$email = $user['email'] ?? '';
$timezoneName = $user['timezone'] ?? '';
$timezoneOffset = $timezoneName ? getUTCOffset($timezoneName) : '';
$bio = trim($user['bio'] ?? '');
$rightsNames = array_map(function ($right) {
    return trim($right['right_name'] ?? '');
}, $userRights);
$rightsNames = array_filter($rightsNames, function ($label) {
    return $label !== '';
});
$rightsCount = count($rightsNames);
$displayName = $name ?: $username ?: 'User profile';
$timezoneDisplay = '';
if ($timezoneName) {
    if ($timezoneOffset !== '') {
        $offsetLabel = stripos($timezoneOffset, 'UTC') === 0 ? $timezoneOffset : 'UTC' . $timezoneOffset;
        $timezoneDisplay = sprintf('%s (%s)', $timezoneName, $offsetLabel);
    } else {
        $timezoneDisplay = $timezoneName;
    }
}

?>

                <section class="tm-directory tm-profile-view">
                    <div class="tm-hero-card tm-hero-card--stacked tm-profile-hero">
                        <div class="tm-profile-hero-main">
                            <div class="tm-profile-avatar-frame">
                                <img src="<?= htmlspecialchars($app_root) . htmlspecialchars($avatar) ?>" alt="Avatar of <?= htmlspecialchars($displayName) ?>" />
                            </div>
                            <div class="tm-profile-hero-body">
                                <h1 class="tm-profile-title"><?= htmlspecialchars($displayName) ?></h1>
                                <p class="tm-profile-subtitle">Personal details and access summary for this TotalMeet account.</p>
                                <div class="tm-profile-hero-meta">
<?php if ($username): ?>
                                    <span class="tm-hero-pill pill-neutral">
                                        <i class="fas fa-user"></i>
                                        @<?= htmlspecialchars($username) ?>
                                    </span>
<?php endif; ?>
<?php if ($timezoneDisplay): ?>
                                    <span class="tm-hero-pill pill-primary">
                                        <i class="fas fa-clock"></i>
                                        <?= htmlspecialchars($timezoneDisplay) ?>
                                    </span>
<?php endif; ?>
                                    <span class="tm-hero-pill pill-accent">
                                        <i class="fas fa-shield-alt"></i>
                                        <?= $rightsCount ?> <?= $rightsCount === 1 ? 'Right' : 'Rights' ?>
                                    </span>
                                </div>
                            </div>
                            <div class="tm-profile-hero-actions">
                                <a class="btn btn-primary" href="<?= htmlspecialchars($app_root) ?>?page=profile&amp;action=edit">
                                    <i class="fas fa-edit"></i> Edit profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="tm-profile-panels">
                        <article class="tm-profile-panel">
                            <header>
                                <h3>Account details</h3>
                            </header>
                            <dl class="tm-profile-detail-list">
                                <div class="tm-profile-detail-item">
                                    <dt>Full name</dt>
                                    <dd><?= $name ? htmlspecialchars($name) : '<span class="tm-profile-placeholder">Not provided</span>' ?></dd>
                                </div>
                                <div class="tm-profile-detail-item">
                                    <dt>Email</dt>
                                    <dd><?= $email ? htmlspecialchars($email) : '<span class="tm-profile-placeholder">Not provided</span>' ?></dd>
                                </div>
                                <div class="tm-profile-detail-item">
                                    <dt>Username</dt>
                                    <dd><?= $username ? htmlspecialchars($username) : '<span class="tm-profile-placeholder">Not provided</span>' ?></dd>
                                </div>
                                <div class="tm-profile-detail-item">
                                    <dt>Timezone</dt>
                                    <dd><?= $timezoneDisplay ? htmlspecialchars($timezoneDisplay) : '<span class="tm-profile-placeholder">Not set</span>' ?></dd>
                                </div>
                            </dl>
                        </article>

                        <article class="tm-profile-panel">
                            <header>
                                <h3>Bio</h3>
                            </header>
<?php if ($bio !== ''): ?>
                            <p class="tm-profile-bio"><?= nl2br(htmlspecialchars($bio)) ?></p>
<?php else: ?>
                            <p class="tm-profile-placeholder">This user hasn’t added a bio yet.</p>
<?php endif; ?>
                        </article>

                        <article class="tm-profile-panel">
                            <header>
                                <h3>User rights</h3>
                            </header>
<?php if ($rightsCount): ?>
                            <ul class="tm-profile-rights">
<?php foreach ($rightsNames as $rightLabel): ?>
                                <li>
                                    <i class="fas fa-check"></i>
                                    <?= htmlspecialchars($rightLabel) ?>
                                </li>
<?php endforeach; ?>
                            </ul>
<?php else: ?>
                            <p class="tm-profile-placeholder">No rights assigned yet.</p>
<?php endif; ?>
                        </article>

<?php do_hook('profile.additional_panels', [
    'subscription' => $subscription ?? null,
    'app_root' => $app_root,
    'userId' => $user['id'] ?? null,
]); ?>
                    </div>
                </section>
