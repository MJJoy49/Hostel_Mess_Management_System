<?php
// api/profileInfo.php

session_start();
header('Content-Type: application/json');

$response = [
    'success'  => false,
    'message'  => '',
    'is_admin' => false,
    'user'     => null,
    'mess'     => null
];

// -----------------------------
// login check
// -----------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    $response['message'] = 'Not logged in';
    echo json_encode($response);
    exit;
}

$userId      = $_SESSION['user_id'];
$sessionRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$sessionMess = isset($_SESSION['messId']) ? $_SESSION['messId'] : null;

// -----------------------------
// 2. Database con
// -----------------------------
include '../config/database.php'; 

if (!isset($mysqli) || $mysqli->connect_errno) {
    http_response_code(500);
    $response['message'] = 'Database connection failed';
    echo json_encode($response);
    exit;
}

// charset set 
$mysqli->set_charset('utf8mb4');

// -----------------------------
//  Users table -> user info
// -----------------------------

$sqlUser = "
    SELECT 
        user_id,
        full_name,
        gender,
        contact_number,
        email_id,
        blood_group,
        role,
        photo,          
        address,
        religion,
        profession,
        mess_id,
        created_at,
        status
    FROM Users
    WHERE user_id = ?
    LIMIT 1
";

$stmtUser = $mysqli->prepare($sqlUser);
if (!$stmtUser) {
    http_response_code(500);
    $response['message'] = 'User query prepare failed';
    echo json_encode($response);
    exit;
}

$stmtUser->bind_param("s", $userId);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userRow    = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$userRow) {
    http_response_code(404);
    $response['message'] = 'User not found';
    echo json_encode($response);
    exit;
}

// mess_id -> priority: sessionMess > userRow['mess_id']
$messId = $sessionMess ? $sessionMess : $userRow['mess_id'];

if (!$messId) {
    http_response_code(404);
    $response['message'] = 'User is not attached to any mess';
    echo json_encode($response);
    exit;
}

// -----------------------------
// Mess table -> mess info
// -----------------------------
$sqlMess = "
    SELECT
        mess_id,
        mess_name,
        address,
        capacity,
        admin_name,
        admin_email,
        admin_id,
        email_id,
        created_at,
        mess_description
    FROM Mess
    WHERE mess_id = ?
    LIMIT 1
";

$stmtMess = $mysqli->prepare($sqlMess);
if (!$stmtMess) {
    http_response_code(500);
    $response['message'] = 'Mess query prepare failed';
    echo json_encode($response);
    exit;
}

$stmtMess->bind_param("s", $messId);
$stmtMess->execute();
$resultMess = $stmtMess->get_result();
$messRow    = $resultMess->fetch_assoc();
$stmtMess->close();

if (!$messRow) {
    http_response_code(404);
    $response['message'] = 'Mess not found';
    echo json_encode($response);
    exit;
}

// -----------------------------
// BLOB → base64 image (photo)
// -----------------------------
$photoDataUrl = null;

// photo blob  image (mostly jpeg/png)
if (!empty($userRow['photo'])) {
    // BLOB → base64
    $base64 = base64_encode($userRow['photo']);

    
    $mimeType = 'image/jpeg';

    $photoDataUrl = 'data:' . $mimeType . ';base64,' . $base64;
}

// -----------------------------
// Front-end -> data
// -----------------------------

// User object (hostel.js er userData format )
$user = [
    'user_id'        => $userRow['user_id'],
    'full_name'      => $userRow['full_name'],
    'gender'         => $userRow['gender'],        
    'contact_number' => $userRow['contact_number'],
    'email_id'       => $userRow['email_id'],
    'blood_group'    => $userRow['blood_group'],
    'role'           => $userRow['role'],          // 'admin' / 'member'
    'photo'          => $photoDataUrl,             //  base64 data URL
    'address'        => $userRow['address'],
    'religion'       => $userRow['religion'],
    'profession'     => $userRow['profession'],
    'joined_date'    => $userRow['created_at'],    
    'created_at'     => $userRow['created_at'],
    'status'         => $userRow['status'],
    'mess_id'        => $userRow['mess_id']
];

// Mess object
$mess = [
    'mess_id'          => $messRow['mess_id'],
    'mess_name'        => $messRow['mess_name'],
    'address'          => $messRow['address'],
    'capacity'         => (int)$messRow['capacity'],
    'admin_name'       => $messRow['admin_name'],
    'admin_email'      => $messRow['admin_email'],
    'admin_id'         => $messRow['admin_id'],
    'mess_email_id'    => $messRow['email_id'],
    'created_at'       => $messRow['created_at'],
    'mess_description' => $messRow['mess_description']
];

// role check
$isAdmin = ($userRow['role'] === 'admin');

// -----------------------------
// 7. Final JSON response
// -----------------------------
$response['success']  = true;
$response['message']  = 'OK';
$response['is_admin'] = $isAdmin;
$response['user']     = $user;
$response['mess']     = $mess;

echo json_encode($response);