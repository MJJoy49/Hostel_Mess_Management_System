<?php
// Controller/pages/BillsController.php

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

$mess_id  = $_SESSION['mess_id'] ?? null;
$user_id  = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role']    ?? 'member';

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

$action = $_GET['action'] ?? '';

// --- ACTIONS ---

if ($action === 'getData') {
    getData($mysqli, $mess_id, $user_id, $user_role);
    exit;

} elseif ($action === 'getUsers') {
    getUsers($mysqli, $mess_id);
    exit;

} elseif ($action === 'addBazar') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add bazar']);
        exit;
    }
    addBazar($mysqli, $mess_id, $user_id);
    exit;

} elseif ($action === 'deleteBazar') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can delete bazar']);
        exit;
    }
    deleteBazar($mysqli, $mess_id);
    exit;

} elseif ($action === 'addExpense') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add expense']);
        exit;
    }
    addExpense($mysqli, $mess_id, $user_id);
    exit;

} elseif ($action === 'deleteExpense') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can delete expense']);
        exit;
    }
    deleteExpense($mysqli, $mess_id);
    exit;

} elseif ($action === 'addRoomRent') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add rent']);
        exit;
    }
    addRoomRent($mysqli, $mess_id);
    exit;

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// --- FUNCTIONS ---

function getData($mysqli, $mess_id, $user_id, $user_role)
{
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

    // Stats variables
    $bazarTotal = 0.0;
    $expenseTotal = 0.0;
    
    // 1. Bazar
    $bazar = [];
    $sql = "SELECT bazar_id, bazar_date, items, total_amount, bazaar_by 
            FROM daily_bazar 
            WHERE mess_id = ? AND YEAR(bazar_date) = ? AND MONTH(bazar_date) = ?
            ORDER BY bazar_date ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $year, $month);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $amount = (float)$row['total_amount'];
        $bazarTotal += $amount;
        $bazar[] = [
            'bazar_id'    => (int)$row['bazar_id'],
            'bazar_date'  => $row['bazar_date'],
            'items'       => $row['items'],
            'total_amount'=> $amount,
            'bazaar_by'   => $row['bazaar_by'],
        ];
    }

    // 2. Expenses
    $expenses = [];
    $sql = "SELECT expense_id, expense_date, category, description, amount, added_by 
            FROM expenses 
            WHERE mess_id = ? AND YEAR(expense_date) = ? AND MONTH(expense_date) = ?
            ORDER BY expense_date ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $year, $month);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $amount = (float)$row['amount'];
        $expenseTotal += $amount;
        $expenses[] = [
            'expense_id'   => (int)$row['expense_id'],
            'expense_date' => $row['expense_date'],
            'category'     => $row['category'],
            'description'  => $row['description'],
            'amount'       => $amount,
            'added_by'     => $row['added_by'],
        ];
    }

    // 3. My Bills
    $myBills = [];
    $sql = "SELECT bill_month, bill_year, total_meals, meal_rate, total_amount, paid_amount, due_amount, status
            FROM monthly_bills
            WHERE mess_id = ? AND user_id = ?
            ORDER BY bill_year DESC, bill_month DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $mess_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $bm = (int)$row['bill_month'];
        $by = (int)$row['bill_year'];
        $myBills[] = [
            'month_label' => monthLabel($bm, $by),
            'total_meals' => (float)$row['total_meals'],
            'meal_rate'   => (float)$row['meal_rate'],
            'total_amount'=> (float)$row['total_amount'],
            'paid_amount' => (float)$row['paid_amount'],
            'due_amount'  => (float)$row['due_amount'],
            'status'      => $row['status'],
        ];
    }

    // 4. All Bills
    $allBills = [];
    $totalBillMonth = 0;
    $totalPaidMonth = 0;
    $totalDueMonth = 0;

    $sql = "SELECT mb.total_meals, mb.meal_rate, mb.total_amount, 
                   mb.paid_amount, mb.due_amount, mb.status, 
                   u.full_name
            FROM monthly_bills mb
            JOIN Users u ON mb.user_id = u.user_id
            WHERE mb.mess_id = ? AND mb.bill_month = ? AND mb.bill_year = ?
            ORDER BY u.full_name ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $month, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $tAmt = (float)$row['total_amount'];
        $pAmt = (float)$row['paid_amount'];
        $dAmt = (float)$row['due_amount'];

        $totalBillMonth += $tAmt;
        $totalPaidMonth += $pAmt;
        $totalDueMonth += $dAmt;

        $allBills[] = [
            'member_name' => $row['full_name'],
            'total_meals' => (float)$row['total_meals'],
            'meal_rate'   => (float)$row['meal_rate'],
            'total_amount'=> $tAmt,
            'paid_amount' => $pAmt,
            'due_amount'  => $dAmt,
            'status'      => $row['status'],
        ];
    }

    // 5. Payments
    $payments = [];
    $sql = "SELECT p.amount, p.payment_for, p.payment_month, p.payment_year, 
                   p.payment_method, p.transaction_id, p.paid_at, 
                   u.full_name
            FROM payments p
            JOIN Users u ON p.user_id = u.user_id
            WHERE p.mess_id = ? AND p.payment_month = ? AND p.payment_year = ?
            ORDER BY p.paid_at DESC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $year, $month);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $payments[] = [
            'member_name'   => $row['full_name'],
            'amount'        => (float)$row['amount'],
            'payment_for'   => $row['payment_for'],
            'payment_method'=> $row['payment_method'],
            'transaction_id'=> $row['transaction_id'],
            'paid_at'       => $row['paid_at'],
            'month_label'   => monthLabel((int)$row['payment_month'], (int)$row['payment_year']),
        ];
    }

    echo json_encode([
        'success'     => true,
        'role'        => $user_role,
        'year'        => $year,
        'month'       => $month,
        'stats'       => [
            'top_total' => $totalBillMonth,
            'top_paid'  => $totalPaidMonth,
            'top_due'   => $totalDueMonth,
            'bazar_total'   => $bazarTotal,
            'expense_total' => $expenseTotal,
        ],
        'bazar'       => $bazar,
        'expenses'    => $expenses,
        'myBills'     => $myBills,
        'allBills'    => $allBills,
        'payments'    => $payments,
    ]);
}

/**
 * Get Active Users + Room Rent info
 */
function getUsers($mysqli, $mess_id) {
    // Join with room_members and rooms to get 'rent_per_seat'
    $sql = "SELECT u.user_id, u.full_name, r.rent_per_seat
            FROM Users u
            LEFT JOIN room_members rm ON u.user_id = rm.user_id AND rm.is_current = 1
            LEFT JOIN rooms r ON rm.room_id = r.room_id
            WHERE u.mess_id = ? AND u.status = 'active'
            ORDER BY u.full_name ASC";
            
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $users = [];
    while($row = $res->fetch_assoc()){
        // Ensure numeric rent value
        $row['rent_per_seat'] = $row['rent_per_seat'] ? (float)$row['rent_per_seat'] : 0;
        $users[] = $row;
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
}

function addRoomRent($mysqli, $mess_id) {
    $target_user_id = trim($_POST['user_id'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $bill_month = (int)date('n');
    $bill_year = (int)date('Y');

    if (!$target_user_id || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user or amount']);
        return;
    }
    ensureBillExists($mysqli, $mess_id, $target_user_id, $bill_month, $bill_year);

    $sql = "UPDATE monthly_bills 
            SET total_amount = total_amount + ?, 
                due_amount = due_amount + ? 
            WHERE mess_id = ? AND user_id = ? AND bill_month = ? AND bill_year = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ddssii', $amount, $amount, $mess_id, $target_user_id, $bill_month, $bill_year);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Rent added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

function addBazar($mysqli, $mess_id, $user_id) {
    $date = trim($_POST['bazar_date'] ?? '');
    $items = trim($_POST['items'] ?? '');
    $amt = (float)($_POST['total_amount'] ?? 0);
    $by = trim($_POST['bazar_by'] ?? '');
    if (!$date || !$items || $amt <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
        return;
    }
    $sql = "INSERT INTO daily_bazar (mess_id, bazar_date, items, total_amount, bazaar_by) VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sssds', $mess_id, $date, $items, $amt, $by);
    if($stmt->execute()) {
        distributeCost($mysqli, $mess_id, $date, $amt);
        echo json_encode(['success' => true, 'message' => 'Bazar added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add']);
    }
}

function deleteBazar($mysqli, $mess_id) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid Id']); return; }
    $sqlGet = "SELECT total_amount, bazar_date FROM daily_bazar WHERE bazar_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sqlGet);
    $stmt->bind_param('is', $id, $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if ($row) {
        $amount = (float)$row['total_amount'];
        $date = $row['bazar_date'];
        $sql = "DELETE FROM daily_bazar WHERE bazar_id = ? AND mess_id = ?";
        $stmtDel = $mysqli->prepare($sql);
        $stmtDel->bind_param('is', $id, $mess_id);
        if($stmtDel->execute()){
             distributeCost($mysqli, $mess_id, $date, -$amount);
             echo json_encode(['success' => true, 'message' => 'Bazar deleted']);
        } else {
             echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function addExpense($mysqli, $mess_id, $user_id) {
    $date = trim($_POST['expense_date'] ?? '');
    $cat = trim($_POST['category'] ?? '');
    $amt = (float)($_POST['amount'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    if (!$date || !$cat || $amt <= 0) {
        echo json_encode(['success' => false, 'message' => 'Missing fields']);
        return;
    }
    $sql = "INSERT INTO expenses (mess_id, expense_date, category, description, amount, added_by) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssssds', $mess_id, $date, $cat, $desc, $amt, $user_id);
    if($stmt->execute()) {
        distributeCost($mysqli, $mess_id, $date, $amt);
        echo json_encode(['success' => true, 'message' => 'Expense added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed']);
    }
}

function deleteExpense($mysqli, $mess_id) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid Id']); return; }
    $sqlGet = "SELECT amount, expense_date FROM expenses WHERE expense_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sqlGet);
    $stmt->bind_param('is', $id, $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    if($row){
        $amount = (float)$row['amount'];
        $date = $row['expense_date'];
        $sql = "DELETE FROM expenses WHERE expense_id = ? AND mess_id = ?";
        $stmtDel = $mysqli->prepare($sql);
        $stmtDel->bind_param('is', $id, $mess_id);
        if($stmtDel->execute()){
            distributeCost($mysqli, $mess_id, $date, -$amount);
            echo json_encode(['success' => true, 'message' => 'Expense deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Delete failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function distributeCost($mysqli, $mess_id, $date, $total_amount) {
    $time = strtotime($date);
    $month = (int)date('n', $time);
    $year = (int)date('Y', $time);
    $sqlUsers = "SELECT user_id FROM Users WHERE mess_id = ? AND status = 'active'";
    $stmt = $mysqli->prepare($sqlUsers);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $users = [];
    while($u = $res->fetch_assoc()){ $users[] = $u['user_id']; }
    $count = count($users);
    if ($count === 0) return;
    $split_amount = $total_amount / $count;
    foreach($users as $uid) {
        ensureBillExists($mysqli, $mess_id, $uid, $month, $year);
        $sqlUpdate = "UPDATE monthly_bills SET total_amount = total_amount + ?, due_amount = due_amount + ? WHERE mess_id = ? AND user_id = ? AND bill_month = ? AND bill_year = ?";
        $stmtUp = $mysqli->prepare($sqlUpdate);
        $stmtUp->bind_param('ddssii', $split_amount, $split_amount, $mess_id, $uid, $month, $year);
        $stmtUp->execute();
    }
}

function ensureBillExists($mysqli, $mess_id, $user_id, $month, $year) {
    $sql = "INSERT IGNORE INTO monthly_bills (mess_id, user_id, bill_month, bill_year, total_meals, meal_rate, total_amount, paid_amount, due_amount, status) VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, 'pending')";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssii', $mess_id, $user_id, $month, $year);
    $stmt->execute();
}

function monthLabel($m, $y) {
    $names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return ($names[$m - 1] ?? $m) . '-' . $y;
}
?>