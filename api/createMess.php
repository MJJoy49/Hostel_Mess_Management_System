<?php

// Database connection
include '../config/database.php';
header("Content-Type: application/json");

// Check POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "msg" => "Use POST method"]);
    exit;
}

// Read JSON data
$input = json_decode(file_get_contents("php://input"), true);
if (!$input) {
    echo json_encode(["success" => false, "msg" => "Invalid JSON data"]);
    exit;
}

// Get all fields
$adminName         = trim($input['adminName']);
$adminGender       = $input['adminGender'];
$adminEmail        = trim($input['adminEmail']);
$adminPassword     = $input['adminPassword'];
$adminPhone        = trim($input['adminPhone']);
$adminBloodGroup   = strtoupper(trim($input['adminBloodGroup']));
$adminReligion     = trim($input['adminReligion']);
$adminProfession   = trim($input['adminProfession']);
$adminAddress      = trim($input['adminAddress']);
$hostelName        = trim($input['hostelName']);
$hostelAddress     = trim($input['hostelAddress']);
$hostelSeats       = (int)$input['hostelSeats'];
$hostelEmail       = trim($input['hostelOfficialEmail']);
$hostelDescription = trim($input['hostelDescription']);
$adminId           = $input['adminId'];
$messId            = $input['messId'];
$photoBase64       = $input['adminPhotoBase64'];

// ---------- Photo required ----------
if (!$photoBase64) {
    echo json_encode(["success" => false, "msg" => "Photo required"]);
    exit;
}

// Base64 header remove korte (data:image/jpeg;base64, ...)
$commaPos = strpos($photoBase64, ',');
if ($commaPos !== false) {
    $photoBase64 = substr($photoBase64, $commaPos + 1);
}

// Base64 decode kore binary 
$photoData = base64_decode($photoBase64);

// ---------- Hash Password ----------
$hashedPass = password_hash($adminPassword, PASSWORD_DEFAULT);

// ================== INSERT ADMIN (users) ==================
// users: user_id, full_name, gender, contact_number, email_id,
//        blood_group, role, photo, address, religion, profession,
//        password, mess_id

$sql1 = "INSERT INTO users 
(user_id, full_name, gender, contact_number, email_id, blood_group, role, photo, address, religion, profession, password, mess_id)
VALUES (?, ?, ?, ?, ?, ?, 'admin', ?, ?, ?, ?, ?, ?)";

$stmt1 = $mysqli->prepare($sql1);
if (!$stmt1) {
    echo json_encode(["success" => false, "msg" => "Admin prepare failed"]);
    exit;
}

// important: user insert করার সময় mess_id = NULL দেবো, 
// কারণ এখনো mess টেবিলে ওই messId নেই
$nullMessId = null;

$stmt1->bind_param(
    "ssssssssssss",  // 12 ta string
    $adminId,        // user_id
    $adminName,      // full_name
    $adminGender,    // gender
    $adminPhone,     // contact_number
    $adminEmail,     // email_id
    $adminBloodGroup,// blood_group
    $photoData,      // photo (BLOB)
    $adminAddress,   // address
    $adminReligion,  // religion
    $adminProfession,// profession
    $hashedPass,     // password
    $nullMessId      // mess_id = NULL (FK error এড়াতে)
);

// Execute admin insert
if (!$stmt1->execute()) {
    echo json_encode([
        "success" => false,
        "msg" => "Admin save failed: " . $stmt1->error  
    ]);
    $stmt1->close();
    exit;
}
$stmt1->close();

// ================== INSERT MESS ==================
$sql2 = "INSERT INTO mess 
(mess_id, mess_name, address, capacity, admin_name, admin_email, admin_id, email_id, created_at, mess_description)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

$stmt2 = $mysqli->prepare($sql2);
if (!$stmt2) {
    echo json_encode(["success" => false, "msg" => "Mess prepare failed"]);
    exit;
}

$stmt2->bind_param(
    "sssisssss",
    $messId,
    $hostelName,
    $hostelAddress,
    $hostelSeats,
    $adminName,
    $adminEmail,
    $adminId,       // ekhane admin_id directly user_id ke reference korbe (user already exists)
    $hostelEmail,
    $hostelDescription
);

if (!$stmt2->execute()) {
    echo json_encode([
        "success" => false,
        "msg" => "Mess save failed: " . $stmt2->error
    ]);
    $stmt2->close();
    $mysqli->close();
    exit;
}
$stmt2->close();

// ================== UPDATE ADMIN.mess_id ==================
// ekhane abar admin user ke oi mess er shathe link kore dilam
$sql3 = "UPDATE users SET mess_id = ? WHERE user_id = ?";
$stmt3 = $mysqli->prepare($sql3);
if ($stmt3) {
    $stmt3->bind_param("ss", $messId, $adminId);
    $stmt3->execute();
    $stmt3->close();
}

// sob ঠিকঠাক
echo json_encode(["success" => true, "msg" => "Hostel and admin created"]);

$mysqli->close();
?>