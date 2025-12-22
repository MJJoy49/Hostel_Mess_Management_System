<?php

include '../config/database.php';

header("Content-Type: application/json");

// POST check (optional but good)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "msg" => "Use POST method"
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

// Basic validation: check if email and password are sent
if (!is_array($input) || !isset($input['email']) || !isset($input['password'])) {
    echo json_encode([
        "success" => false,
        "msg" => "Email and password are required"
    ]);
    exit;
}

$email    = trim($input['email']);
$password = $input['password'];

if ($email === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "msg" => "Email and password cannot be empty"
    ]);
    exit;
}

// Prepare query to find user by email
// users table: user_id, full_name, gender, contact_number, email_id,
//              blood_group, role, photo, address, religion, profession,
//              password, mess_id, created_at, status
$stmt = $mysqli->prepare(
    "SELECT user_id, full_name, password, role, mess_id, status 
     FROM users 
     WHERE email_id = ? 
     LIMIT 1"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "msg" => "Database error occurred"
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // Email not found
    echo json_encode([
        "success" => false,
        "msg" => "Invalid email or password"
    ]);
    $stmt->close();
    exit;
}

// Email found, now fetch the data
$stmt->bind_result($user_id, $full_name, $hashed_password, $role, $mess_id, $status);
$stmt->fetch();


if ($status !== 'active') {
    echo json_encode([
        "success" => false,
        "msg" => "Account is not active"
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// Verify the password
if (password_verify($password, $hashed_password)) {
    // Login successful
    echo json_encode([
        "success" => true,
        "msg" => "Login successful",
        "user" => [
            "id"    => $user_id,
            "name"  => $full_name,
            "email" => $email,
            "role"  => $role,
            "messId"=> $mess_id
        ]
    ]);

    //Start session if needed
    session_start();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role']    = $role;

} else {
    // Wrong password
    echo json_encode([
        "success" => false,
        "msg" => "Invalid email or password"
    ]);
}

$stmt->close();
$mysqli->close();
?>