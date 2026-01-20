<?php
// All auth-related Ajax: login + create hostel
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Model/config/database.php';
require_once __DIR__ . '/../Model/helper/IdGenerator.php'; 

$action = $_GET['action'] ?? '';

if ($action === 'login') {
    loginAction();
} elseif ($action === 'create_hostel') {
    createHostelAction();
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Handle login
 */
function loginAction()
{
    $mysqli = db();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        return;
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    $sql = "SELECT user_id, full_name, email_id, password, role, mess_id, status
            FROM Users
            WHERE email_id = ?
            LIMIT 1";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    if ($user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Account is not active']);
        return;
    }

    // NOTE: sample data er password dummy; new create hole password_hash use korbo
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Wrong password']);
        return;
    }

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['mess_id']   = $user['mess_id'];

    echo json_encode([
        'success'  => true,
        'message'  => 'Login successful',
        'redirect' => 'main.php'
    ]);
}

/**
 * Handle create hostel + admin
 */
function createHostelAction()
{
    $mysqli = db();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        return;
    }

    $get = fn($k) => trim($_POST[$k] ?? '');

    $adminName       = $get('adminName');
    $adminGender     = $get('adminGender');
    $adminEmail      = $get('adminEmail');
    $adminPassword   = $_POST['adminPassword'] ?? '';
    $adminRePassword = $_POST['adminRePassword'] ?? '';
    $adminPhone      = $get('adminPhone');
    $adminBloodGroup = $get('adminBloodGroup');
    $adminReligion   = $get('adminReligion');
    $adminProfession = $get('adminProfession');
    $adminAddress    = $get('adminAddress');

    $hostelName          = $get('hostelName');
    $hostelAddress       = $get('hostelAddress');
    $hostelSeats         = (int)($_POST['hostelSeats'] ?? 0);
    $hostelOfficialEmail = $get('hostelOfficialEmail');
    $hostelDescription   = $get('hostelDescription');

    if (
        $adminName === '' || $adminEmail === '' || $adminPassword === '' ||
        $adminPhone === '' || $hostelName === '' || $hostelAddress === '' || $hostelSeats <= 0
    ) {
        echo json_encode(['success' => false, 'message' => 'Please fill all required fields']);
        return;
    }

    if ($adminPassword !== $adminRePassword) {
        echo json_encode(['success' => false, 'message' => 'Password not matched']);
        return;
    }

    // photo
    $photoData = null;
    if (!empty($_FILES['adminPhoto']['tmp_name'])) {
        $photoData = file_get_contents($_FILES['adminPhoto']['tmp_name']);
    }

    try {
        $mysqli->begin_transaction();

        // check email
        $stmt = $mysqli->prepare("SELECT 1 FROM Users WHERE email_id=? LIMIT 1");
        $stmt->bind_param("s", $adminEmail);
        $stmt->execute();
        if ($stmt->get_result()->fetch_row()) {
            $mysqli->rollback();
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            return;
        }

        // IDs
        $messId  = 'MESS' . time();
        $adminId = 'USER' . time();

        $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $role = 'admin';

        // ✅ STEP 1: insert ADMIN (without mess_id first)
        $sqlUser = "INSERT INTO Users
        (user_id, full_name, gender, contact_number, email_id, blood_group, role, photo, address, religion, profession, password, status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'active')";

        $stmt = $mysqli->prepare($sqlUser);
        $stmt->bind_param(
            "ssssssssssss",
            $adminId,
            $adminName,
            $adminGender,
            $adminPhone,
            $adminEmail,
            $adminBloodGroup,
            $role,
            $photoData,
            $adminAddress,
            $adminReligion,
            $adminProfession,
            $hash
        );
        $stmt->send_long_data(7, $photoData);
        $stmt->execute();

        //insert MESS
        $sqlMess = "INSERT INTO Mess
        (mess_id, mess_name, address, capacity, admin_name, admin_email, admin_id, email_id, mess_description)
        VALUES (?,?,?,?,?,?,?,?,?)";

        $stmt = $mysqli->prepare($sqlMess);
        $stmt->bind_param(
            "sssisssss",
            $messId,
            $hostelName,
            $hostelAddress,
            $hostelSeats,
            $adminName,
            $adminEmail,
            $adminId,
            $hostelOfficialEmail,
            $hostelDescription
        );
        $stmt->execute();

        //update admin → mess_id
        $stmt = $mysqli->prepare("UPDATE Users SET mess_id=? WHERE user_id=?");
        $stmt->bind_param("ss", $messId, $adminId);
        $stmt->execute();

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Hostel created successfully. You can now login.'
        ]);

    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
}
