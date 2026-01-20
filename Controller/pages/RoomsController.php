<?php
// controller/pages/RoomsController.php

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
    // Pass user_role to the function so we can send it to frontend
    getData($mysqli, $mess_id, $user_id, $user_role);
} elseif ($action === 'getRoom') {
    getRoom($mysqli, $mess_id);
} elseif ($action === 'addRoom') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add room']);
        exit;
    }
    addRoom($mysqli, $mess_id);
} elseif ($action === 'updateRoom') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can update room']);
        exit;
    }
    updateRoom($mysqli, $mess_id);
} elseif ($action === 'removeRoom') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can remove room']);
        exit;
    }
    removeRoom($mysqli, $mess_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

// Updated getData to accept and return user_role
function getData($mysqli, $mess_id, $user_id, $user_role)
{
    $data = [
        'success' => true,
        'stats' => [],
        'rooms' => [],
        'my_room_id' => null,
        'user_role' => $user_role // Sending role to frontend
    ];

    $sql = "SELECT COUNT(*) AS total, SUM(nullif(capacity,0)) AS beds
            FROM rooms
            WHERE mess_id = ? AND is_active = 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $total_rooms = $res['total'];
    $total_beds = $res['beds'];

    $sqlOcc = "
        SELECT COUNT(DISTINCT rm.user_id) AS occupied
        FROM room_members rm
        JOIN rooms r ON rm.room_id = r.room_id
        WHERE r.mess_id = ? AND rm.is_current = 1
    ";
    $stmt = $mysqli->prepare($sqlOcc);
    $stmt->bind_param('i', $mess_id);
    $stmt->execute();
    $resOcc = $stmt->get_result()->fetch_assoc();
    $occupied = intval($resOcc['occupied']);

    $vacant = $total_beds - $occupied;
    if ($vacant < 0) {
        $vacant = 0;
    }

    $data['stats'] = [
        'total_rooms' => $total_rooms,
        'total_beds' => $total_beds,
        'occupied'   => $occupied,
        'vacant'     => $vacant,
    ];

    $sqlRooms = "
        SELECT 
            r.room_id,
            r.room_number,
            r.capacity,
            r.rent_per_seat,
            r.facilities,
            r.is_active,
            COALESCE(occ.cnt, 0) AS occupancy
        FROM rooms r
        LEFT JOIN (
            SELECT room_id, COUNT(*) AS cnt 
            FROM room_members
            WHERE is_current = 1
            GROUP BY room_id
        ) AS occ 
        ON r.room_id = occ.room_id
        WHERE r.mess_id = ?
        ORDER BY r.room_number ASC
    ";
    $stmt = $mysqli->prepare($sqlRooms);
    $stmt->bind_param('i', $mess_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $vac = (int)$row['capacity'] - (int)$row['occupancy'];
        if ($vac < 0) {
            $vac = 0;
        }
        $data['rooms'][] = [
            'room_id'     => (int)$row['room_id'],
            'room_number' => $row['room_number'],
            'capacity'    => (int)$row['capacity'],
            'occupancy'   => (int)$row['occupancy'],
            'vacant'      => $vac,
            'rent'        => (float)$row['rent_per_seat'],
            'facilities'  => $row['facilities'],
            'is_active'   => (int)$row['is_active'],
        ];
    }

    $sqlMy = "
        SELECT rm.room_id
        FROM room_members rm
        JOIN rooms r ON rm.room_id = r.room_id
        WHERE rm.user_id = ? AND rm.is_current = 1 AND r.mess_id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sqlMy);
    $stmt->bind_param('ii', $user_id, $mess_id);
    $stmt->execute();
    $resMy = $stmt->get_result()->fetch_assoc();
    if ($resMy) {
        $data['my_room_id'] = (int)$resMy['room_id'];
    }

    echo json_encode($data);
}

function getRoom($mysqli, $mess_id)
{
    $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'room_id required']);
        return;
    }

    $sql = "
        SELECT 
            r.room_id,
            r.room_number,
            r.capacity,
            r.rent_per_seat,
            r.facilities,
            r.is_active,
            COALESCE(occ.cnt, 0) AS occupancy
        FROM rooms r
        LEFT JOIN (
            SELECT room_id, COUNT(*) AS cnt 
            FROM room_members
            WHERE is_current = 1
            GROUP BY room_id
        ) AS occ 
        ON r.room_id = occ.room_id
        WHERE r.room_id = ? AND r.mess_id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $room_id, $mess_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        return;
    }

    $vac = (int)$res['capacity'] - (int)$res['occupancy'];
    if ($vac < 0) {
        $vac = 0;
    }

    $sqlM = "
        SELECT u.user_id, u.full_name, u.role, u.status
        FROM room_members rm
        JOIN users u ON rm.user_id = u.user_id
        WHERE rm.room_id = ? AND rm.is_current = 1
        ORDER BY u.full_name ASC
    ";
    $stmt = $mysqli->prepare($sqlM);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($m = $result->fetch_assoc()) {
        $members[] = [
            'user_id'   => (int)$m['user_id'],
            'full_name' => $m['full_name'],
            'role'      => $m['role'],
            'status'    => $m['status'],
        ];
    }

    echo json_encode([
        'success' => true,
        'room' => [
            'room_id'     => (int)$res['room_id'],
            'room_number' => $res['room_number'],
            'capacity'    => (int)$res['capacity'],
            'occupancy'   => (int)$res['occupancy'],
            'vacant'      => $vac,
            'rent'        => (float)$res['rent_per_seat'],
            'facilities'  => $res['facilities'],
            'is_active'   => (int)$res['is_active'],
            'members'     => $members,
        ]
    ]);
}

function addRoom($mysqli, $mess_id)
{
    $room_number = trim($_POST['new_room_number'] ?? '');
    $capacity    = (int)($_POST['new_capacity'] ?? 0);
    $rent        = (float)($_POST['new_rent'] ?? 0);
    $is_active   = (int)($_POST['new_is_active'] ?? 1);
    $facilities  = trim($_POST['new_facilities'] ?? '');

    if ($room_number === '' || $capacity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Room number and capacity are required']);
        return;
    }

    $sql = "SELECT 1 FROM rooms WHERE mess_id = ? AND room_number = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $mess_id, $room_number);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Room number already exists']);
        return;
    }

    $sql = "
        INSERT INTO rooms 
        (mess_id, room_number, capacity, rent_per_seat, facilities, is_active)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isidss',
        $mess_id,
        $room_number,
        $capacity,
        $rent,
        $facilities,
        $is_active
    );
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Room added']);
}

function updateRoom($mysqli, $mess_id)
{
    $room_id     = (int)($_POST['room_id'] ?? 0);
    $room_number = trim($_POST['new_room_number'] ?? '');
    $capacity    = (int)($_POST['new_capacity'] ?? 0);
    $rent        = (float)($_POST['new_rent'] ?? 0);
    $is_active   = (int)($_POST['new_is_active'] ?? 1);
    $facilities  = trim($_POST['new_facilities'] ?? '');

    if ($room_id <= 0 || $room_number === '' || $capacity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    $sql = "SELECT room_id FROM rooms WHERE room_id = ? AND mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $room_id, $mess_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        return;
    }

    $sql = "SELECT 1 FROM rooms WHERE mess_id = ? AND room_number = ? AND room_id <> ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isi', $mess_id, $room_number, $room_id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Room number already used by another room']);
        return;
    }

    $sqlOcc = "
        SELECT COUNT(*) AS cnt
        FROM room_members
        WHERE room_id = ? AND is_current = 1
    ";
    $stmt = $mysqli->prepare($sqlOcc);
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $resOcc = $stmt->get_result()->fetch_assoc();
    $occ = (int)$resOcc['cnt'];

    if ($capacity < $occ) {
        echo json_encode([
            'success' => false,
            'message' => 'Capacity cannot be less than current occupancy (' . $occ . ')'
        ]);
        return;
    }

    $sql = "
        UPDATE rooms
        SET room_number = ?, capacity = ?, rent_per_seat = ?, facilities = ?, is_active = ?
        WHERE room_id = ? AND mess_id = ?
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sidssii',
        $room_number,
        $capacity,
        $rent,
        $facilities,
        $is_active,
        $room_id,
        $mess_id
    );
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Room updated']);
}

function removeRoom($mysqli, $mess_id)
{
    $room_id = (int)($_POST['room_id'] ?? 0);
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'room_id required']);
        return;
    }

    $sqlOcc = "
        SELECT COUNT(*) AS occ
        FROM room_members rm
        JOIN rooms r ON rm.room_id = r.room_id
        WHERE r.mess_id = ? AND rm.room_id = ? AND rm.is_current = 1
    ";
    $stmt = $mysqli->prepare($sqlOcc);
    $stmt->bind_param('ii', $mess_id, $room_id);
    $stmt->execute();
    $resOcc = $stmt->get_result()->fetch_assoc();
    $occ = (int)$resOcc['occ'];

    if ($occ > 0) {
        echo json_encode(['success' => false, 'message' => 'Room has members, cannot remove']);
        return;
    }

    $sql = "UPDATE rooms SET is_active = 0 WHERE room_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ii', $room_id, $mess_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Room marked as closed']);
}