<?php
require_once __DIR__ . '/../Controller/MainController.php';
ensureAuthenticated();

$allowed_pages = [
    'dashboard',
    'bills',
    'meals',
    'members',
    'notices',
    'payments',
    'profile',
    'rooms',
];

$page = resolvePage($allowed_pages, 'profile');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainApp</title>

    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/main.css">

    <?php if ($page === 'dashboard'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/dashboard.css">
    <?php endif; ?>
    <?php if ($page === 'profile'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/profile.css">
    <?php endif; ?>
    <?php if ($page === 'meals'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/meals.css">
    <?php endif; ?>
    <?php if ($page === 'members'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/members.css">
    <?php endif; ?>
    <?php if ($page === 'rooms'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/rooms.css">
    <?php endif; ?>
    <?php if ($page === 'notices'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/notices.css">
    <?php endif; ?>
    <?php if ($page === 'bills'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/bills.css">
    <?php endif; ?>
    <?php if ($page === 'payments'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/payments.css">
    <?php endif; ?>
</head>
<body>

<header class="app-header">
    <!-- <div class="app-header-left">
        <span class="app-logo">🏠</span>
    </div> -->

    <!-- mobile icon bar -->
    <div class="mobile-icon-bar">
        <button onclick="location.href='main.php?page=profile'">🏨</button>
        <button onclick="location.href='main.php?page=dashboard'">🏠</button>
        <button onclick="location.href='main.php?page=meals'">🍽️</button>
        <button onclick="location.href='main.php?page=members'">👥</button>
        <button onclick="location.href='main.php?page=bills'">🧾</button>
        <button onclick="location.href='main.php?page=notices'">📢</button>
        <button onclick="location.href='main.php?page=payments'">💳</button>
        <button onclick="location.href='main.php?page=rooms'">🛏️</button>
    </div>

    <div class="app-header-right">
        <button class="settings-btn" id="settingsToggleBtn">⚙</button>
    </div>
</header>

<!-- Settings panel (overlay style) -->
<div class="settings-panel-overlay" id="settingsPanelOverlay"></div>

<div class="settings-panel" id="settingsPanel">
    <div class="settings-panel-header">
        <span>Quick Settings</span>
        <button type="button" class="settings-close-btn" id="settingsCloseBtn">✕</button>
    </div>
    <div class="settings-panel-body">
        <div class="settings-panel-item">
            <span>Home</span>
            <a href="index1.php" class="settings-link">Go</a>
        </div>

        <div class="settings-panel-item">
            <span>Theme</span>
            <button type="button" id="dayNightMoodBtn" class="settings-theme-btn">🌙</button>
        </div>

        <div class="settings-panel-item">
            <span>Logout</span>
            <a href="../Controller/logout.php" class="settings-link">Logout</a>
        </div>
    </div>
</div>

<div class="app-layout">
    <?php include __DIR__ . '/includes/sidebar_admin.php'; ?>

    <main class="main-content">
        <?php include __DIR__ . "/pages/{$page}.php"; ?>
    </main>
</div>

<script src="./assets/js/toggleTheme.js"></script>
<script src="./assets/js/main.js"></script>

<?php if ($page === 'dashboard'): ?>
    <script src="./assets/js/pagesPartJs/dashboard.js"></script>
<?php endif; ?>
<?php if ($page === 'profile'): ?>
    <script src="./assets/js/pagesPartJs/profile.js"></script>
<?php endif; ?>
<?php if ($page === 'meals'): ?>
    <script src="./assets/js/pagesPartJs/meals.js"></script>
<?php endif; ?>
<?php if ($page === 'members'): ?>
    <script src="./assets/js/pagesPartJs/members.js"></script>
<?php endif; ?>
<?php if ($page === 'rooms'): ?>
    <script src="./assets/js/pagesPartJs/rooms.js"></script>
<?php endif; ?>
<?php if ($page === 'notices'): ?>
    <script src="./assets/js/pagesPartJs/notices.js"></script>
<?php endif; ?>
<?php if ($page === 'bills'): ?>
    <script src="./assets/js/pagesPartJs/bills.js"></script>
<?php endif; ?>
<?php if ($page === 'payments'): ?>
    <script src="./assets/js/pagesPartJs/payments.js"></script>
<?php endif; ?>
</body>
</html>