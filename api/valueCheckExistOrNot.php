<?php

include '../config/database.php'; // $mysqli connection

header("Content-Type: application/json");

// Read JSON
$input = json_decode(file_get_contents("php://input"), true);

// Input valid কিনা দেখি
if (!is_array($input) || !isset($input['field']) || !isset($input['value'])) {
    echo json_encode(["success" => "error", "msg" => "Invalid request"]);
    exit;
}

$field = $input['field'];
$value = trim($input['value']);

if ($value === '') {
    echo json_encode(["success" => "error", "msg" => "Empty value"]);
    exit;
}

// Allowed fields
$allowed_fields = ['email', 'hostel_name', 'admin_name'];

if (!in_array($field, $allowed_fields)) {
    echo json_encode(["success" => "error", "msg" => "Invalid field"]);
    exit;
}

// field -> table + column ম্যাপ
$field_map = [
    'email'       => ['table' => 'users', 'column' => 'email_id'],
    'hostel_name' => ['table' => 'mess',  'column' => 'mess_name'],   // এখানেই মূল চেঞ্জ
    'admin_name'  => ['table' => 'mess',  'column' => 'admin_name']
];

$table  = $field_map[$field]['table'];
$column = $field_map[$field]['column'];

// Dynamic table/column but safe, কারণ value গুলো উপরের ম্যাপ থেকে আসছে
$sql = "SELECT 1 FROM {$table} WHERE {$column} = ? LIMIT 1";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => "error", "msg" => "Database error"]);
    exit;
}

$stmt->bind_param("s", $value);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => "exist"]);
} else {
    echo json_encode(["success" => "not-exist"]);
}

$stmt->close();
$mysqli->close();
?>