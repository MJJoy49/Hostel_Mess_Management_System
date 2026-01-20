<?php
require_once __DIR__ . '/../Controller/MainController.php';
redirectIfLoggedIn();

require_once __DIR__ . '/../Model/config/database.php';
$mysqli = db();

/* ----------------- Active seat ads ----------------- */

$sql = "SELECT 
            sa.ad_id,
            sa.vacant_seats,
            sa.rent_per_seat,
            sa.contact_person,
            sa.contact_number,
            sa.mess_address,
            sa.ad_title,
            sa.ad_description,
            sa.posted_at,
            sa.expires_at,
            m.mess_name
        FROM seat_ads sa
        JOIN Mess m ON sa.mess_id = m.mess_id
        WHERE sa.is_active = 1
        ORDER BY sa.posted_at DESC
        LIMIT 50";

$result = $mysqli->query($sql);
$ads = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ads[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mess Management System</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <link rel="stylesheet" href="./assets/css/landing.css">
</head>
<body>

    <!-- HEADER -->
    <header class="header">
        <div class="logo">Mess Door
        </div>

        <div class="header-actions">
            <a href="./login.php" class="login-btn">Login / Create Mess</a>
            <a href="#seatAdsSection" class="login-btn">Find Seat</a>
            <button class="mode-btn" id="dayNightMoodBtn">🌙</button>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="landing-hero">
        <div class="hero-left">
            <h1 class="hero-title">Manage Your Mess in One Place</h1>
            <p class="hero-subtitle">
                Web based Hostel / Mess Management System for owners, members and visitors.
                Track meals, bazar, bills, payments and seat ads from a single dashboard.
            </p>

            <div class="hero-actions">
                <a href="./login.php" class="hero-btn hero-btn-primary">Create / Login as Admin</a>
                <a href="#howItWorks" class="hero-btn hero-btn-secondary">How it works</a>
            </div>

            <ul class="hero-bullets">
                <li>Mess Owner: manage rooms, members, meals, bazar, bills and payments.</li>
                <li>Members: see your meals, monthly bill, payments and notices.</li>
                <li>Visitors: check available seat ads and apply without login.</li>
            </ul>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="landing-section" id="howItWorks">
        <h2 class="section-title">How This App Works</h2>
        <p class="section-subtitle">
            3 simple roles – 1 complete mess management system.
        </p>

        <div class="how-grid">
            <article class="how-card">
                <h3>1. Mess Owner (Admin)</h3>
                <ul>
                    <li>Create mess and admin account from <strong>Login / Create Mess</strong>.</li>
                    <li>Add rooms, members, daily bazar and other expenses.</li>
                    <li>Set meal rate, generate monthly bills, manage payments.</li>
                    <li>Publish notices &amp; seat ads so visitors can apply.</li>
                </ul>
            </article>

            <article class="how-card">
                <h3>2. Member</h3>
                <ul>
                    <li>Login with your email and password given by admin.</li>
                    <li>Check your meals, monthly bill, payments and room info.</li>
                    <li>See notices &amp; announcements from mess admin.</li>
                </ul>
            </article>

            <article class="how-card">
                <h3>3. Visitor (No Login)</h3>
                <ul>
                    <li>Scroll down to <strong>Available Seat Ads</strong>.</li>
                    <li>Read mess name, address, rent and facilities.</li>
                    <li>Apply for a seat directly from here; request goes to mess admin.</li>
                </ul>
            </article>
        </div>
    </section>

    <!-- SEAT ADS SECTION -->
    <section class="landing-section" id="seatAdsSection">
        <div class="section-header-row">
            <div>
                <h2 class="section-title">Available Seat Ads</h2>
                <p class="section-subtitle">
                    Only active seat advertisements from admins are shown here.
                </p>
            </div>
            <div class="ads-search-wrapper">
                <input
                    type="text"
                    id="adSearchInput"
                    class="ads-search-input"
                    placeholder="Search by location (address)...">
            </div>
        </div>

        <?php if (empty($ads)): ?>
            <div class="no-ads-card">
                <h3>No seat ads available right now</h3>
                <p>
                    Currently there are no active seat advertisements in the system.
                    Please check again later or contact mess owners directly.
                </p>
            </div>
        <?php else: ?>
            <!-- Column of cards, 1 per row. More than ~5 -> vertical scroll -->
            <div class="ads-column" id="adsColumn">
                <?php foreach ($ads as $ad): ?>
                    <?php
                        $locationText = strtolower($ad['mess_address'] ?? '');
                        $postedAt  = $ad['posted_at']  ? date('M j, Y', strtotime($ad['posted_at']))  : '-';
                        $expiresAt = $ad['expires_at'] ? date('M j, Y', strtotime($ad['expires_at'])) : '-';
                    ?>
                    <article class="ad-card" data-location="<?= htmlspecialchars($locationText) ?>">
                        <h3 class="ad-title"><?= htmlspecialchars($ad['ad_title']) ?></h3>
                        <p class="ad-mess-name"><?= htmlspecialchars($ad['mess_name']) ?></p>
                        <p class="ad-address">
                            <?= htmlspecialchars($ad['mess_address']) ?>
                        </p>

                        <div class="ad-meta-row">
                            <div class="ad-meta-block">
                                <span class="ad-meta-label">Vacant</span>
                                <span class="ad-meta-value"><?= (int)$ad['vacant_seats'] ?> seat(s)</span>
                            </div>
                            <div class="ad-meta-block">
                                <span class="ad-meta-label">Rent/Seat</span>
                                <span class="ad-meta-value">৳<?= number_format((float)$ad['rent_per_seat'], 0) ?></span>
                            </div>
                        </div>

                        <p class="ad-description">
                            <?= nl2br(htmlspecialchars($ad['ad_description'] ?: 'No extra description.')) ?>
                        </p>

                        <div class="ad-footer-row">
                            <div class="ad-contact">
                                <span class="ad-meta-label">Contact</span>
                                <span class="ad-meta-value">
                                    <?= htmlspecialchars($ad['contact_person']) ?>
                                    (<?= htmlspecialchars($ad['contact_number']) ?>)
                                </span>
                            </div>
                            <div class="ad-dates">
                                <span class="ad-date-line">Posted: <?= $postedAt ?></span>
                                <span class="ad-date-line">Expires: <?= $expiresAt ?></span>
                            </div>
                        </div>

                        <a href="./visitor/apply.php?ad_id=<?= (int)$ad['ad_id'] ?>" class="hero-btn hero-btn-primary ad-apply-btn">
                            Apply for this seat
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- FOOTER SECTION: SIMPLE STEPS -->
    <section class="landing-section landing-footer-info">
        <h2 class="section-title">Quick Start Guide</h2>
        <div class="footer-grid">
            <div class="footer-step">
                <h3>For Mess Owner</h3>
                <p>Click <strong>Login / Create Mess</strong> → create hostel → login as admin → manage your mess from dashboard.</p>
            </div>
            <div class="footer-step">
                <h3>For Members</h3>
                <p>Ask admin to create a member account for you. Use that email &amp; password to login and see all info.</p>
            </div>
            <div class="footer-step">
                <h3>For Visitors</h3>
                <p>Use the <strong>Available Seat Ads</strong> section, filter and apply – no login required.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        © <?= date('Y') ?> Hostel / Mess Management System
    </footer>

    <script src="./assets/js/toggleTheme.js"></script>
    <script src="./assets/js/landing.js"></script>

</body>
</html>