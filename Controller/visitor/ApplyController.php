<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../Model/config/database.php';

$mysqli = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$ad_id         = (int)($_POST['ad_id'] ?? 0);
$name          = trim($_POST['name'] ?? '');
$contact       = trim($_POST['contact_number'] ?? '');
$profession    = trim($_POST['profession'] ?? '');
$description   = trim($_POST['description'] ?? '');

if ($ad_id <= 0 || $name === '' || $contact === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill required fields']);
    exit;
}

// Simple phone length check
if (strlen($contact) < 6) {
    echo json_encode(['success' => false, 'message' => 'Contact number looks invalid']);
    exit;
}

// Check ad exists and active
$sqlAd = "SELECT ad_id FROM seat_ads WHERE ad_id = ? AND is_active = 1 LIMIT 1";
$stmt  = $mysqli->prepare($sqlAd);
$stmt->bind_param('i', $ad_id);
$stmt->execute();
if (!$stmt->get_result()->fetch_row()) {
    echo json_encode(['success' => false, 'message' => 'Seat ad not found or inactive']);
    exit;
}

// Insert request
$sqlReq = "INSERT INTO request_seat (ad_id, name, contact_number, profession, description)
           VALUES (?,?,?,?,?)";
$stmt = $mysqli->prepare($sqlReq);
$stmt->bind_param('issss', $ad_id, $name, $contact, $profession, $description);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Request submitted. Mess owner will contact you.']);