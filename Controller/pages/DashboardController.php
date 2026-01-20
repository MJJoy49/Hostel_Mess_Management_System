<?php
// Controller/pages/DashboardController.php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Model/config/database.php';

$mysqli = db();

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$mess_id   = $_SESSION['mess_id'] ?? null;
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role']    ?? 'member';

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'getData') {
    getData($mysqli, $mess_id, $user_id, $user_role);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Main dashboard data
 */
function getData($mysqli, $mess_id, $user_id, $user_role)
{
    $today = date('Y-m-d');
    $month = (int)date('n');
    $year  = (int)date('Y');

    // -----------------------------
    // 1) Basic counts & totals
    // -----------------------------

    // Active members
    $activeMembers = 0;
    $sql = "SELECT COUNT(*) AS c 
            FROM Users 
            WHERE mess_id = ? AND status = 'active'";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $activeMembers = (int)$row['c'];
    }

    // Total bazar cost (this month)
    $bazarTotal = 0.0;
    $sql = "SELECT SUM(total_amount) AS total_cost
            FROM daily_bazar
            WHERE mess_id = ?
              AND MONTH(bazar_date) = ?
              AND YEAR(bazar_date) = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $month, $year);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && $row['total_cost'] !== null) {
        $bazarTotal = (float)$row['total_cost'];
    }

    // Total meals (attendances) this month
    $totalMealsMonth = 0;
    $sql = "SELECT COUNT(*) AS c
            FROM meal_attendances ma
            JOIN Meals m ON ma.meal_id = m.meal_id
            WHERE m.mess_id = ?
              AND ma.attended = 1
              AND YEAR(m.meal_date) = ?
              AND MONTH(m.meal_date) = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $year, $month);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $totalMealsMonth = (int)$row['c'];
    }

    // My total meals this month
    $myMealsMonth = 0;
    $sql = "SELECT COUNT(*) AS c
            FROM meal_attendances ma
            JOIN Meals m ON ma.meal_id = m.meal_id
            WHERE ma.user_id = ?
              AND m.mess_id = ?
              AND ma.attended = 1
              AND YEAR(m.meal_date) = ?
              AND MONTH(m.meal_date) = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssii', $user_id, $mess_id, $year, $month);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $myMealsMonth = (int)$row['c'];
    }

    // Dynamic meal rate: total bazar / total meals
    $mealRate = 0.0;
    if ($totalMealsMonth > 0) {
        $mealRate = $bazarTotal / $totalMealsMonth;
    }
    $myMealCost = $myMealsMonth * $mealRate;

    // My bill (monthly_bills) - this month
    $myBillTotal = 0.0;
    $myPaid      = 0.0;
    $myDue       = 0.0;

    $sql = "SELECT total_amount, paid_amount, due_amount
            FROM monthly_bills
            WHERE mess_id = ?
              AND user_id = ?
              AND bill_month = ?
              AND bill_year = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssii', $mess_id, $user_id, $month, $year);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $myBillTotal = (float)$row['total_amount'];
        $myPaid      = (float)$row['paid_amount'];
        $myDue       = (float)$row['due_amount'];
    }

    // Admin: total bills/paid/due this month
    $totalBill = 0.0;
    $totalPaid = 0.0;
    $totalDue  = 0.0;

    $sql = "SELECT SUM(total_amount) AS t_bill,
                   SUM(paid_amount)  AS t_paid,
                   SUM(due_amount)   AS t_due
            FROM monthly_bills
            WHERE mess_id = ?
              AND bill_month = ?
              AND bill_year = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $month, $year);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $totalBill = (float)($row['t_bill'] ?? 0);
        $totalPaid = (float)($row['t_paid'] ?? 0);
        $totalDue  = (float)($row['t_due']  ?? 0);
    }

    // -----------------------------
    // 2) Stats (top 4 cards)
    // -----------------------------
    $stats = [
        [
            'label' => 'Active members',
            'value' => $activeMembers,
            'sub'   => 'Currently in this mess',
        ],
        [
            'label' => 'Total meals',
            'value' => $totalMealsMonth,
            'sub'   => 'All members · this month',
        ],
        [
            'label' => 'Total bazar',
            'value' => '৳ ' . fmtMoney($bazarTotal),
            'sub'   => 'This month',
        ],
        [
            'label' => 'My due (bill)',
            'value' => '৳ ' . fmtMoney($myDue),
            'sub'   => 'Current month',
        ],
    ];

    // -----------------------------
    // 3) Overview (left column chips)
    // -----------------------------
    $overview = [
        [
            'label' => 'My meals',
            'value' => $myMealsMonth,
            'sub'   => 'This month',
        ],
        [
            'label' => 'Meal rate',
            'value' => '৳ ' . fmtMoney($mealRate),
            'sub'   => 'Bazar / total meals',
        ],
        [
            'label' => 'My meal cost',
            'value' => '৳ ' . fmtMoney($myMealCost),
            'sub'   => 'Rate × my meals',
        ],
        [
            'label' => 'My total bill',
            'value' => '৳ ' . fmtMoney($myBillTotal),
            'sub'   => 'All charges',
        ],
        [
            'label' => 'My paid',
            'value' => '৳ ' . fmtMoney($myPaid),
            'sub'   => '',
        ],
        [
            'label' => 'My due',
            'value' => '৳ ' . fmtMoney($myDue),
            'sub'   => '',
        ],
    ];

    // -----------------------------
    // 4) Today meals table
    // -----------------------------
    $todayMeals = [];
    $sql = "SELECT meal_type, menu
            FROM Meals
            WHERE mess_id = ?
              AND meal_date = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $mess_id, $today);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $todayMeals[] = [
            'type'   => ucfirst($row['meal_type']),
            'menu'   => $row['menu'] ?: '-',
            'status' => 'Available',
        ];
    }

    // -----------------------------
    // 5) Recent activity
    // -----------------------------
    $recentAnnouncements = [];
    $sql = "SELECT title, created_at
            FROM announcements
            WHERE mess_id = ?
            ORDER BY created_at DESC
            LIMIT 5";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $recentAnnouncements[] = [
            'title'    => $row['title'],
            'subtitle' => timeAgo($row['created_at']),
        ];
    }

    $recentPayments = [];
    $sql = "SELECT p.amount, p.payment_for, p.paid_at, u.full_name
            FROM payments p
            JOIN Users u ON p.user_id = u.user_id
            WHERE p.mess_id = ?
            ORDER BY p.paid_at DESC
            LIMIT 5";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $title = '৳ ' . fmtMoney($row['amount']) . ' · ' . ucfirst($row['payment_for']);
        $subtitle = $row['full_name'] . ' · ' . timeAgo($row['paid_at']);
        $recentPayments[] = [
            'title'    => $title,
            'subtitle' => $subtitle,
        ];
    }

    $recentBazar = [];
    $sql = "SELECT bazar_date, items, total_amount, bazaar_by
            FROM daily_bazar
            WHERE mess_id = ?
            ORDER BY bazar_date DESC
            LIMIT 5";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $title = '৳ ' . fmtMoney($row['total_amount']) . ' · ' . $row['items'];
        $subtitle = ($row['bazaar_by'] ?: 'Unknown') .
                    ' · ' . timeAgo($row['bazar_date']);
        $recentBazar[] = [
            'title'    => $title,
            'subtitle' => $subtitle,
        ];
    }

    // -----------------------------
    // 6) Financial overview (admin block)
    // -----------------------------
    $finances = [
        [
            'label' => 'Total bill',
            'value' => '৳ ' . fmtMoney($totalBill),
            'sub'   => 'All members · this month',
        ],
        [
            'label' => 'Paid',
            'value' => '৳ ' . fmtMoney($totalPaid),
            'sub'   => 'Recorded payments',
        ],
        [
            'label' => 'Due',
            'value' => '৳ ' . fmtMoney($totalDue),
            'sub'   => 'Total bill - paid',
        ],
        [
            'label' => 'Bazar cost',
            'value' => '৳ ' . fmtMoney($bazarTotal),
            'sub'   => 'This month',
        ],
    ];

    $periodLabel = 'This month · ' . date('M Y');

    echo json_encode([
        'success'           => true,
        'role'              => $user_role,
        'period_label'      => $periodLabel,
        'stats'             => $stats,
        'overview'          => $overview,
        'today_meals_col'   => 'Status',
        'today_meals'       => $todayMeals,
        'finances'          => $finances,
        'recent'            => [
            'announcements' => $recentAnnouncements,
            'payments'      => $recentPayments,
            'bazar'         => $recentBazar,
        ],
    ]);
}

/**
 * Format money with 2 decimal places
 */
function fmtMoney($amount)
{
    return number_format((float)$amount, 2, '.', '');
}

/**
 * Simple "time ago" helper
 */
function timeAgo($datetime)
{
    if (!$datetime) return '';
    $ts = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    if (!$ts) return '';

    $diff = time() - $ts;
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $m = floor($diff / 60);
        return $m . ' min ago';
    } elseif ($diff < 86400) {
        $h = floor($diff / 3600);
        return $h . 'h ago';
    } elseif ($diff < 86400 * 7) {
        $d = floor($diff / 86400);
        return $d . 'd ago';
    } else {
        return date('d M Y', $ts);
    }
}
?>