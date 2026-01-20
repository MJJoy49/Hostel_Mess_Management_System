<?php
require_once __DIR__ . '/../../Model/config/database.php';

$mysqli = db();

$sql = "SELECT sa.ad_id, sa.vacant_seats, sa.rent_per_seat,
               sa.contact_person, sa.contact_number,
               sa.ad_title, sa.mess_address,
               m.mess_name
        FROM seat_ads sa
        JOIN Mess m ON sa.mess_id = m.mess_id
        WHERE sa.is_active = 1
        ORDER BY sa.posted_at DESC
        LIMIT 20";

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
    <title>Seat Ads</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="logo">Hostel / Mess Seat Ads</div>
        <div class="header-actions">
            <a href="../index1.php" class="login-btn">Home</a>
        </div>
    </header>

    <main class="main">
        <h1>Available Seats</h1>
        <div class="card">
            <?php if (empty($ads)): ?>
                <p>No active seat ads right now.</p>
            <?php else: ?>
                <?php foreach ($ads as $ad): ?>
                    <div style="border-bottom:1px solid var(--border); padding:8px 0;">
                        <h3><?= htmlspecialchars($ad['ad_title']) ?></h3>
                        <p>
                            Mess: <strong><?= htmlspecialchars($ad['mess_name']) ?></strong><br>
                            Address: <?= htmlspecialchars($ad['mess_address']) ?><br>
                            Vacant seats: <?= (int)$ad['vacant_seats'] ?>,
                            Rent/seat: ৳<?= (float)$ad['rent_per_seat'] ?><br>
                            Contact: <?= htmlspecialchars($ad['contact_person']) ?>
                            (<?= htmlspecialchars($ad['contact_number']) ?>)
                        </p>
                        <a href="apply.php?ad_id=<?= (int)$ad['ad_id'] ?>" class="login-btn">Apply for this seat</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>