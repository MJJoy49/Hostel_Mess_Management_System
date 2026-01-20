<?php
// Controller/pages/MealsController.php

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

if ($action === 'getData') {
    getData($mysqli, $mess_id, $user_id, $user_role);

} elseif ($action === 'toggleAttendance') {
    // Only Admin can toggle
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can change attendance']);
        exit;
    }
    toggleAttendance($mysqli, $mess_id);

} elseif ($action === 'addMeal') {
    // Only Admin can add meal
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add meal']);
        exit;
    }
    addMeal($mysqli, $mess_id);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Load full meals dashboard data
 */
function getData($mysqli, $mess_id, $user_id, $user_role)
{
    $date = $_GET['date'] ?? date('Y-m-d');
    // Basic date validation
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        $date = date('Y-m-d');
    }

    $bazarDateFilter = $_GET['bazar_date'] ?? '';

    $tmDay = $date; 
    $mMonth = (int) substr($tmDay, 5, 2);
    $mYear = (int) substr($tmDay, 0, 4);

    // 1. Get Total Bazar Cost for this Month
    $sqlBazar = "SELECT SUM(total_amount) as total_cost 
                 FROM daily_bazar 
                 WHERE mess_id = ? AND MONTH(bazar_date) = ? AND YEAR(bazar_date) = ?";
    $stmt = $mysqli->prepare($sqlBazar);
    $stmt->bind_param('sii', $mess_id, $mMonth, $mYear);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $bazarTotal = (float)($row['total_cost'] ?? 0);

    // 2. Today meals count (Specific Date)
    $sql = "SELECT COUNT(*) as c FROM Meals WHERE mess_id = ? AND meal_date = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $mess_id, $tmDay);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $todayMealsCount = (int)$row['c'];

    // 3. My meals this month (attendance)
    $sql = "
        SELECT COUNT(*) as c 
        FROM meal_attendances ma
        JOIN Meals m ON ma.meal_id = m.meal_id
        WHERE ma.user_id = ? AND m.mess_id = ?
          AND ma.attended = 1
          AND YEAR(m.meal_date) = ? AND MONTH(m.meal_date) = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssii', $user_id, $mess_id, $mYear, $mMonth);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $myMealsMonth = (int)$row['c'];

    // 4. Total meals this month (All members)
    $sql = "
        SELECT COUNT(*) as c
        FROM meal_attendances ma
        JOIN Meals m ON ma.meal_id = m.meal_id
        WHERE m.mess_id = ?
          AND ma.attended = 1
          AND YEAR(m.meal_date) = ? AND MONTH(m.meal_date) = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sii', $mess_id, $mYear, $mMonth);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalMealsMonth = (int)$row['c'];

    // --- NEW LOGIC: Calculate Meal Rate Dynamic ---
    // Rate = Total Bazar / Total Meals
    $calculatedRate = 0;
    if ($totalMealsMonth > 0) {
        $calculatedRate = $bazarTotal / $totalMealsMonth;
    }

    // Calculate Estimated Cost (Basically just the bazar total)
    $estimatedCost = $bazarTotal;

    // Calculate My Due based on dynamic rate
    $myDue = $myMealsMonth * $calculatedRate;


    // 5. Attendance - Load Users (Active)
    $users = [];
    $sql = "SELECT user_id, full_name FROM Users WHERE mess_id = ? AND status = 'active' ORDER BY full_name ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($u = $res->fetch_assoc()) {
        $users[] = $u;
    }

    // 6. Meals for the specific date
    $mealTypes = ['breakfast', 'lunch', 'dinner'];
    $mealsForDate = [];
    $sql = "SELECT meal_id, meal_type, menu FROM Meals WHERE mess_id = ? AND meal_date = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $mess_id, $tmDay);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($m = $res->fetch_assoc()) {
        $mType = strtolower($m['meal_type']);
        $mealsForDate[$mType] = [
            'meal_id' => $m['meal_id'],
            'meal_type' => $mType,
            'menu' => $m['menu'],
        ];
    }

    // Today meals list for table (Flattened)
    $todayMealsList = array_values($mealsForDate);

    // 7. Attendance rows (Map by MealID + UserID)
    $attendanceMap = [];
    if (!empty($mealsForDate)) {
        $mealIds = array_column($mealsForDate, 'meal_id');
        // Create placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($mealIds), '?'));
        $types = str_repeat('i', count($mealIds));
        
        $sql = "SELECT meal_id, user_id, attended FROM meal_attendances WHERE meal_id IN ($placeholders)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$mealIds); // Unpack array
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $attendanceMap[$row['meal_id'] . '_' . $row['user_id']] = (bool)$row['attended'];
        }
    }

    $attendanceRows = [];
    foreach ($users as $u) {
        $row = [
            'user_id' => $u['user_id'],
            'full_name' => $u['full_name'],
            'breakfast' => ['meal_id' => null, 'attended' => false, 'info_text' => 'No Meal'],
            'lunch'     => ['meal_id' => null, 'attended' => false, 'info_text' => 'No Meal'],
            'dinner'    => ['meal_id' => null, 'attended' => false, 'info_text' => 'No Meal'],
        ];

        foreach ($mealTypes as $mt) {
            if (isset($mealsForDate[$mt])) {
                $mId = $mealsForDate[$mt]['meal_id'];
                $key = $mId . '_' . $u['user_id'];
                $att = isset($attendanceMap[$key]) ? $attendanceMap[$key] : false; // Default false if not found
                
                $row[$mt] = [
                    'meal_id' => $mId,
                    'attended' => $att
                ];
            } else {
                 // No meal scheduled for this type
                 $row[$mt] = ['meal_id' => null];
            }
        }
        $attendanceRows[] = $row;
    }

    // 8. Bazar history
    $bazarRows = [];
    if ($bazarDateFilter && preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $bazarDateFilter)) {
        // Filter by specific date
        $sql = "SELECT bazar_id, bazar_date, items, total_amount, bazaar_by 
                FROM daily_bazar 
                WHERE mess_id = ? AND bazar_date = ?
                ORDER BY bazar_date DESC";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss', $mess_id, $bazarDateFilter);
    } else {
        // Default: Last 7 days
        $from = date('Y-m-d', strtotime('-6 days')); 
        $sql = "SELECT bazar_id, bazar_date, items, total_amount, bazaar_by 
                FROM daily_bazar 
                WHERE mess_id = ? AND bazar_date BETWEEN ? AND ?
                ORDER BY bazar_date DESC";
        $stmt = $mysqli->prepare($sql);
        $today = date('Y-m-d');
        $stmt->bind_param('sss', $mess_id, $from, $today);
    }

    $stmt->execute();
    $res = $stmt->get_result();
    while ($b = $res->fetch_assoc()) {
        $bazarRows[] = [
            'bazar_date'  => $b['bazar_date'],
            'items'       => $b['items'],
            'total_amount'=> (float)$b['total_amount'],
            'bazaar_by'   => $b['bazaar_by'],
        ];
    }

    echo json_encode([
        'success'     => true,
        'role'        => $user_role,
        'date'        => $date,
        'attendance_date' => $tmDay,
        'stats'       => [
            'today_meals'   => $todayMealsCount,
            'my_meals_month'=> $myMealsMonth,
            'my_meal_due'   => $myDue,
        ],
        'summary' => [
            'total_meals'   => $totalMealsMonth,
            'meal_rate'     => $calculatedRate, // Dynamic Rate
            'estimated_cost'=> $estimatedCost,
        ],
        'attendance_rows' => $attendanceRows,
        'todayMeals'      => $todayMealsList,
        'bazar'           => [
            'rows' => $bazarRows,
        ]
    ]);
}

/**
 * Toggle attendance (Admin Only)
 */
function toggleAttendance($mysqli, $mess_id)
{
    $user_id = trim($_POST['user_id'] ?? '');
    // $meal_type is inside button dataset usually, but here we receive date & type? 
    // Wait, the View/JS sends 'meal_type' string but we need meal_id actually or find it.
    // The JS sends: user_id, type, date.
    
    $meal_type = strtolower(trim($_POST['type'] ?? ''));
    $date = trim($_POST['date'] ?? '');

    if ($user_id == '' || $date == '' || !in_array($meal_type, ['breakfast', 'lunch', 'dinner'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    // Find meal_id
    $sql = "SELECT meal_id FROM Meals WHERE mess_id = ? AND meal_date = ? AND meal_type = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $mess_id, $date, $meal_type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        // Meal doesn't exist yet, can't toggle attendance
        echo json_encode(['success' => false, 'message' => 'Meal not created yet']);
        return;
    }

    $meal_id = (int)$row['meal_id'];

    // Check attendance row
    $sql = "SELECT attended FROM meal_attendances WHERE meal_id = ? AND user_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $meal_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $rowAtt = $res->fetch_assoc();

    if ($rowAtt) {
        // Toggle
        $newAtt = $rowAtt['attended'] ? 0 : 1;
        $sqlUp = "UPDATE meal_attendances SET attended = ? WHERE meal_id = ? AND user_id = ?";
        $stmt = $mysqli->prepare($sqlUp);
        $stmt->bind_param('iis', $newAtt, $meal_id, $user_id);
        $stmt->execute();
    } else {
        // Insert (First time toggle to ON)
        $newAtt = 1;
        $sqlIns = "INSERT INTO meal_attendances (meal_id, user_id, attended) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sqlIns);
        $stmt->bind_param('isi', $meal_id, $user_id, $newAtt);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'attended' => (bool)$newAtt]);
}

/**
 * Add or update meal (Admin Only)
 */
function addMeal($mysqli, $mess_id)
{
    $meal_type = strtolower(trim($_POST['meal_type'] ?? ''));
    $menu = trim($_POST['menu'] ?? '');
    $date = trim($_POST['date'] ?? '') ? trim($_POST['date']) : date('Y-m-d');

    if (!in_array($meal_type, ['breakfast', 'lunch', 'dinner'], true)) {
        echo json_encode(['success' => false, 'message' => 'Invalid meal type']);
        return;
    }
    
    if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) {
        $date = date('Y-m-d');
    }

    // Check if exists -> update menu, else insert
    $sql = "SELECT meal_id FROM Meals WHERE mess_id = ? AND meal_date = ? AND meal_type = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $mess_id, $date, $meal_type);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row) {
        $meal_id = (int)$row['meal_id'];
        $sqlUp = "UPDATE Meals SET menu = ? WHERE meal_id = ?";
        $stmt = $mysqli->prepare($sqlUp);
        $stmt->bind_param('si', $menu, $meal_id);
        $stmt->execute();
        $msg = 'Meal updated.';
    } else {
        $sqlIns = "INSERT INTO Meals (mess_id, meal_date, meal_type, menu) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sqlIns);
        $stmt->bind_param('ssss', $mess_id, $date, $meal_type, $menu);
        $stmt->execute();
        $msg = 'Meal added.';
    }

    echo json_encode(['success' => true, 'message' => $msg]);
}
?>