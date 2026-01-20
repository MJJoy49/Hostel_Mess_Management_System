<?php
// Controller/pages/PaymentsController.php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../model/config/database.php';

$mysqli = db();

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$mess_id = isset($_SESSION['mess_id']) ? $_SESSION['mess_id'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'member';

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getData') {
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    
    getData($mysqli, $mess_id, $user_id, $user_role, $month, $year);

} elseif ($action === 'addPayment') {
    // Only Admin can add payment
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Access Denied: Only admin can add payments']);
        exit;
    }
    addPayment($mysqli, $mess_id, $user_id); // pass logged in admin id as 'added_by' logic if needed

} elseif ($action === 'deletePayment') {
    // Only Admin can delete (undo) payment
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Access Denied: Only admin can delete payments']);
        exit;
    }
    deletePayment($mysqli, $mess_id);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Main Data Fetcher
 */
function getData($mysqli, $mess_id, $user_id, $user_role, $month, $year)
{
    // 1. Stats Calculation (Logic Updated per requirement)
    $stats = getStats($mysqli, $mess_id, $user_id, $month, $year);

    // 2. Summary List (All members summary for this month)
    $summary = getSummaryByMember($mysqli, $mess_id, $month, $year);

    // 3. History List (Recent transactions)
    $history = getPaymentsHistory($mysqli, $mess_id, $month, $year);

    // 4. Member List for Dropdown (Only for Admin to select who is paying)
    $members = [];
    if ($user_role === 'admin') {
        $members = getMembersList($mysqli, $mess_id);
    }

    echo json_encode([
        'success'      => true,
        'user_role'    => $user_role,
        'current_user' => $user_id,
        'stats'        => $stats,
        'summary'      => $summary,
        'history'      => $history,
        'members'      => $members, 
    ]);
}

/**
 * Stats Logic:
 * Deposit = Total payment by ALL members (including admin) in this mess for this month.
 * Bill    = The LOGGED-IN user's bill for this month.
 * Net     = The LOGGED-IN user's (Total Paid - Total Bill).
 */
function getStats($mysqli, $mess_id, $user_id, $month, $year)
{
    // 1. Total Deposit (All Members + Admin)
    $sqlAll = "SELECT SUM(amount) AS total_dep 
               FROM payments 
               WHERE mess_id = ? AND payment_month = ? AND payment_year = ?";
    $stmt = $mysqli->prepare($sqlAll);
    $stmt->bind_param('sii', $mess_id, $month, $year);
    $stmt->execute();
    $resAll = $stmt->get_result()->fetch_assoc();
    $mess_total_deposit = $resAll['total_dep'] ? (float)$resAll['total_dep'] : 0.00;

    // 2. Logged-in User's Deposit (For Net calculation)
    $sqlUserDep = "SELECT SUM(amount) AS my_dep 
                   FROM payments 
                   WHERE mess_id = ? AND user_id = ? AND payment_month = ? AND payment_year = ?";
    $stmt = $mysqli->prepare($sqlUserDep);
    $stmt->bind_param('ssii', $mess_id, $user_id, $month, $year);
    $stmt->execute();
    $resUserDep = $stmt->get_result()->fetch_assoc();
    $my_deposit = $resUserDep['my_dep'] ? (float)$resUserDep['my_dep'] : 0.00;

    // 3. Logged-in User's Bill (From monthly_bills table)
    // Assuming 'monthly_bills' table holds the generated bill.
    $sqlBill = "SELECT total_amount 
                FROM monthly_bills 
                WHERE mess_id = ? AND user_id = ? AND bill_month = ? AND bill_year = ? 
                LIMIT 1";
    $stmt = $mysqli->prepare($sqlBill);
    $stmt->bind_param('ssii', $mess_id, $user_id, $month, $year);
    $stmt->execute();
    $resBill = $stmt->get_result()->fetch_assoc();
    
    $my_bill = 0.00;
    if ($resBill) {
        $my_bill = (float)$resBill['total_amount'];
    }

    // 4. Net Calculation
    $my_net = $my_deposit - $my_bill;

    return [
        'deposit_all' => $mess_total_deposit, // Card 1: Everyone's money
        'bill_user'   => $my_bill,            // Card 2: My Bill
        'net_user'    => $my_net              // Card 3: My Balance
    ];
}

/**
 * Summary Table: Row per active user (Admin + Member)
 */
function getSummaryByMember($mysqli, $mess_id, $month, $year)
{
    $data = [];

    // Retrieve all active users and their payments/bills for the month
    // Left joining payments and bills
    $sql = "
        SELECT 
            u.user_id, 
            u.full_name, 
            u.role,
            COALESCE(SUM(p.amount), 0) as paid_amount,
            (SELECT total_amount FROM monthly_bills mb 
             WHERE mb.mess_id = u.mess_id AND mb.user_id = u.user_id 
             AND mb.bill_month = ? AND mb.bill_year = ? LIMIT 1) as bill_amount
        FROM Users u
        LEFT JOIN payments p ON p.user_id = u.user_id 
            AND p.mess_id = u.mess_id 
            AND p.payment_month = ? 
            AND p.payment_year = ?
        WHERE u.mess_id = ? AND u.status = 'active'
        GROUP BY u.user_id, u.full_name, u.role
        ORDER BY u.full_name ASC
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iiiss', $month, $year, $month, $year, $mess_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bill = $row['bill_amount'] ? (float)$row['bill_amount'] : 0.00;
        $paid = (float)$row['paid_amount'];
        $net  = $paid - $bill;

        $data[] = [
            'user_id'   => $row['user_id'],
            'full_name' => $row['full_name'],
            'role'      => $row['role'],
            'bill'      => $bill,
            'deposit'   => $paid,
            'net'       => $net
        ];
    }
    return $data;
}

/**
 * Payments History List
 */
function getPaymentsHistory($mysqli, $mess_id, $month, $year)
{
    $data = [];

    $sql = "
        SELECT 
            p.payment_id,
            u.full_name,
            u.role,
            p.amount,
            p.payment_for,
            p.payment_method,
            p.transaction_id,
            p.paid_at
        FROM payments p
        JOIN Users u ON p.user_id = u.user_id
        WHERE p.mess_id = ? AND p.payment_month = ? AND p.payment_year = ?
        ORDER BY p.paid_at DESC
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'payment_id'     => (int)$row['payment_id'],
            'member_name'    => $row['full_name'],
            'member_role'    => $row['role'],
            'amount'         => (float)$row['amount'],
            'payment_for'    => $row['payment_for'],
            'payment_method' => $row['payment_method'],
            'transaction_id' => $row['transaction_id'],
            'date'           => date('d M, h:i A', strtotime($row['paid_at']))
        ];
    }
    return $data;
}

/**
 * Dropdown List for Admin to Add Payment
 */
function getMembersList($mysqli, $mess_id)
{
    $list = [];
    $sql = "SELECT user_id, full_name, role FROM Users WHERE mess_id = ? AND status='active' ORDER BY full_name ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $list[] = [
            'user_id' => $row['user_id'],
            'full_name' => $row['full_name'] . ' (' . ucfirst($row['role']) . ')'
        ];
    }
    return $list;
}

/**
 * Add Payment (Admin Only)
 */
function addPayment($mysqli, $mess_id, $admin_id)
{
    $target_user_id = $_POST['user_id'] ?? '';
    // If admin selects "You" (admin himself), value might be passed or handled in JS. 
    // Assuming dropdown sends user_id.

    $amount = (float)($_POST['amount'] ?? 0);
    $payFor = $_POST['payment_for'] ?? 'meal_bill';
    $method = $_POST['payment_method'] ?? 'cash';
    $txnId  = trim($_POST['transaction_id'] ?? '');
    
    $pMonth = (int)($_POST['month'] ?? date('n'));
    $pYear  = (int)($_POST['year'] ?? date('Y'));

    if (empty($target_user_id)) {
        echo json_encode(['success' => false, 'message' => 'Please select a member']);
        return;
    }
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
        return;
    }

    $sql = "INSERT INTO payments 
            (mess_id, user_id, amount, payment_for, payment_month, payment_year, payment_method, transaction_id, paid_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssdsiiss', $mess_id, $target_user_id, $amount, $payFor, $pMonth, $pYear, $method, $txnId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $mysqli->error]);
    }
}

/**
 * Delete Payment (Undo) - Admin Only
 */
function deletePayment($mysqli, $mess_id)
{
    $payment_id = (int)($_POST['payment_id'] ?? 0);

    if ($payment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid Payment ID']);
        return;
    }

    // Verify mess_id for security
    $sqlCheck = "SELECT payment_id FROM payments WHERE payment_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sqlCheck);
    $stmt->bind_param('is', $payment_id, $mess_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Payment not found or permission denied']);
        return;
    }

    // Delete
    $sqlDel = "DELETE FROM payments WHERE payment_id = ?";
    $stmt = $mysqli->prepare($sqlDel);
    $stmt->bind_param('i', $payment_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Payment undone/deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete']);
    }
}