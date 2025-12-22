<!-- this is main.php file -->

<?php
// ==============================================================
// with this php get admin panel pages
$page = $_GET["page"] ?? "dashboard";

// $allowed_pages = [
//     'dashboard',
//     'applications',
//     'bills',
//     'hostel',
//     'meals',
//     'members',
//     'notices',
//     'notifications',
//     'payments',
//     'rooms',
//     'seat_ads'
// ];
//
// if (!in_array($page, $allowed_pages)) {
//     $page = 'dashboard';
// }
// ==============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainApp</title>

    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/main.css"><!-- layout + sidebar CSS -->

    <!-- ============================================================= -->
    <!-- here add all css of all pages with if else condition  -->
    <?php if ($page === 'dashboard'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/dashboard.css">
    <?php endif; ?>

    <?php if ($page === 'hostel'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/hostel.css">
    <?php endif; ?>

    <?php if ($page === 'meals'): ?>
        <link rel="stylesheet" href="./assets/css/pagesPartCss/meals.css">
    <?php endif; ?>
    <!-- ============================================================= -->
</head>
<body>

    

    
    <!-- Desktop header -->
    <header class="app-header">
        <div class="app-header-left">
            <span class="app-logo">🏠</span>
        </div>

        <!-- mobile icon bar -->
        <div class="mobile-icon-bar">
            <button onclick="location.href='main.php?page=dashboard'">🏠</button>
            <button onclick="location.href='main.php?page=hostel'">🏨</button>
            <button onclick="location.href='main.php?page=applications'">📄</button>
            <button onclick="location.href='main.php?page=bills'">🧾</button>
            <button onclick="location.href='main.php?page=meals'">🍽️</button>
            <button onclick="location.href='main.php?page=members'">👥</button>
        </div>

        <div class="app-header-right">
            <button class="settings-btn">⚙</button>
        </div>
    </header>

    <div class="app-layout">

        <?php include "./includes/sidebar_admin.php"; ?>

        <main class="main-content">
            <?php include "./admin/pages/{$page}.php"; ?>
        </main>
    </div>

    <!-- Global layout JS (sidebar toggle, responsive behaviour) -->
    <script src="./assets/js/layout.js"></script>

    <!-- ============================================================= -->
    <!-- here add all js of all pages with if else condition  -->
    <?php if ($page === 'dashboard'): ?>
        <script src="./assets/js/pagesPartJs/dashboard.js"></script>
    <?php endif; ?>

    <?php if ($page === 'hostel'): ?>
        <script src="./assets/js/pagesPartJs/hostel.js"></script>
    <?php endif; ?>

    <?php if ($page === 'meals'): ?>
        <script src="./assets/js/pagesPartJs/meals.js"></script>
    <?php endif; ?>
    <!-- ============================================================= -->
</body>
</html>