<?php
session_start();
require "../config/database.php";


$_SESSION['admin_id'] = 1;


if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$admin_id = $_SESSION['admin_id'];


header('Content-Type: application/json');


$sqlQuery = "SELECT * FROM hostels WHERE admin_id = $admin_id";
$result = mysqli_query($mysqli, $sqlQuery);

$hostel = [];  

if ($result && mysqli_num_rows($result) > 0) {
    $hostel= mysqli_fetch_assoc($result);
}

echo json_encode($hostel);


exit(); 
?>


