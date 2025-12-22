<!-- ============================================================== -->
<!-- with this php get admin planel pages -->
<?php

$page =$_GET["page"]  ??  "dashboard";

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

// if (!in_array($page, $allowed_pages)) {
//     $page = 'dashboard';
// }

?>
<!-- ============================================================== -->



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainApp</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/main.css">

    <!-- ============================================================== -->
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

    <!-- ============================================================== -->


</head>
<body>
    <div class="app-layout">
        
        <?php
            include "./includes/sidebar_admin.php";
        ?>

        <main class="main-content">
            <?php include "./admin/pages/{$page}.php"; ?>
        </main>

    </div>



    <!-- ============================================================== -->
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

        
    <!-- ============================================================== -->
</body>
</html>


