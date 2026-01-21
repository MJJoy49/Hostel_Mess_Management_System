<?php
// Controller/pages/RoomsController.php

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

$mess_id   = $_SESSION['mess_id'] ?? null;
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role']    ?? 'member';

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

$action = $_GET['action'] ?? 'getData';

switch ($action) {
    case 'getData':
        getData($mysqli, $mess_id, $user_id, $user_role);
        break;

    case 'getRoom':
        getRoom($mysqli, $mess_id);
        break;

    case 'addRoom':
        if ($user_role !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admin can add room']);
            exit;
        }
        addRoom($mysqli, $mess_id);
        break;

    case 'updateRoom':
        if ($user_role !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admin can update room']);
            exit;
        }
        updateRoom($mysqli, $mess_id);
        break;

    case 'removeRoom':
        if ($user_role !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admin can remove room']);
            exit;
        }
        removeRoom($mysqli, $mess_id);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * List rooms + stats
 */
function getData($mysqli, $mess_id, $user_id, $user_role)
{
    // Stats
    $sql = "SELECT 
                COUNT(*) AS total_rooms,
                COALESCE(SUM(capacity), 0) AS total_beds,
                COALESCE(SUM(current_occupancy), 0) AS occupied
            FROM rooms
            WHERE mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $totalRooms = (int)($row['total_rooms'] ?? 0);
    $totalBeds  = (int)($row['total_beds'] ?? 0);
    $occupied   = (int)($row['occupied'] ?? 0);
    $vacant     = $totalBeds - $occupied;
    if ($vacant < 0) $vacant = 0;

    // Current user's room (if any)
    $myRoomId = null;
    $sql = "SELECT rm.room_id 
            FROM room_members rm
            JOIN rooms r ON rm.room_id = r.room_id
            WHERE rm.user_id = ? AND r.mess_id = ? AND rm.is_current = 1
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $myRoomId = (int)$res['room_id'];
    }

    // Room list
    $rooms = [];
    $sql = "SELECT room_id, room_number, capacity, current_occupancy, rent_per_seat, facilities, is_active
            FROM rooms
            WHERE mess_id = ?
            ORDER BY room_number ASC, room_id ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $capacity = (int)$row['capacity'];
        $occ      = (int)$row['current_occupancy'];
        $vac      = $capacity - $occ;
        if ($vac < 0) $vac = 0;

        $rooms[] = [
            'room_id'   => (int)$row['room_id'],
            'room_number' => $row['room_number'],
            'capacity'  => $capacity,
            'occupancy' => $occ,
            'vacant'    => $vac,
            'rent'      => (float)$row['rent_per_seat'],
            'is_active' => (int)$row['is_active'],
        ];
    }

    echo json_encode([
        'success'   => true,
        'user_role' => $user_role,
        'my_room_id'=> $myRoomId,
        'stats'     => [
            'total_rooms' => $totalRooms,
            'total_beds'  => $totalBeds,
            'occupied'    => $occupied,
            'vacant'      => $vacant,
        ],
        'rooms'     => $rooms,
    ]);
}

/**
 * Single room details
 */
function getRoom($mysqli, $mess_id)
{
    $room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid room id']);
        return;
    }

    $sql = "SELECT room_id, room_number, capacity, current_occupancy, rent_per_seat, facilities, is_active
            FROM rooms
            WHERE mess_id = ? AND room_id = ?
            LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('si', $mess_id, $room_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        return;
    }

    $capacity = (int)$row['capacity'];
    $occ      = (int)$row['current_occupancy'];
    $vac      = $capacity - $occ;
    if ($vac < 0) $vac = 0;

    // Members
    $members = [];
    $sql = "SELECT u.full_name, u.role
            FROM room_members rm
            JOIN Users u ON rm.user_id = u.user_id
            WHERE rm.room_id = ? AND rm.is_current = 1
            ORDER BY u.full_name ASC";
    $stmt2 = $mysqli->prepare($sql);
    $stmt2->bind_param('i', $room_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($m = $res2->fetch_assoc()) {
        $members[] = [
            'full_name' => $m['full_name'],
            'role'      => $m['role'],
        ];
    }

    $room = [
        'room_id'     => (int)$row['room_id'],
        'room_number' => $row['room_number'],
        'capacity'    => $capacity,
        'occupancy'   => $occ,
        'vacant'      => $vac,
        'rent'        => (float)$row['rent_per_seat'],
        'is_active'   => (int)$row['is_active'],
        'facilities'  => $row['facilities'],
        'members'     => $members,
    ];

    echo json_encode([
        'success' => true,
        'room'    => $room,
    ]);
}

/**
 * Add new room (admin)
 */
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

    $sql = "INSERT INTO rooms
                (mess_id, room_number, capacity, current_occupancy,
                 rent_per_seat, facilities, is_active)
            VALUES
                (?, ?, ?, 0, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssidsi', $mess_id, $room_number, $capacity, $rent, $facilities, $is_active);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Room added']);
    } else {
        // Duplicate room number?
        if ($stmt->errno == 1062) {
            echo json_encode([
                'success' => false,
                'message' => 'Room number already exists for this mess'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error while adding room'
            ]);
        }
    }
}

/**
 * Update existing room (admin)
 */
function updateRoom($mysqli, $mess_id)
{
    $room_id     = (int)($_POST['room_id'] ?? 0);
    $room_number = trim($_POST['new_room_number'] ?? '');
    $capacity    = (int)($_POST['new_capacity'] ?? 0);
    $rent        = (float)($_POST['new_rent'] ?? 0);
    $is_active   = (int)($_POST['new_is_active'] ?? 1);
    $facilities  = trim($_POST['new_facilities'] ?? '');

    if ($room_id <= 0 || $room_number === '' || $capacity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid room data']);
        return;
    }

    $sql = "UPDATE rooms
            SET room_number = ?, capacity = ?, rent_per_seat = ?,
                facilities = ?, is_active = ?
            WHERE room_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sidsiis',
        $room_number, $capacity, $rent, $facilities, $is_active, $room_id, $mess_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Room updated']);
    } else {
        if ($stmt->errno == 1062) {
            echo json_encode([
                'success' => false,
                'message' => 'Room number already exists for this mess'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error while updating room'
            ]);
        }
    }
}

/**
 * Remove/close room (admin)
 */
function removeRoom($mysqli, $mess_id)
{
    $room_id = (int)($_POST['room_id'] ?? 0);
    if ($room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid room id']);
        return;
    }

    // Soft delete – close room
    $sql = "UPDATE rooms SET is_active = 0 WHERE room_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $room_id, $mess_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Room closed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to close room']);
    }
}
?>