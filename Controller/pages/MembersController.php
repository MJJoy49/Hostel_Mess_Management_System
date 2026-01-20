<?php
// Controller/pages/MembersController.php

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Model/config/database.php';
require_once __DIR__ . '/../../Model/helper/IdGenerator.php';

$mysqli = db();

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$mess_id   = isset($_SESSION['mess_id']) ? $_SESSION['mess_id'] : null;
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'member';
$user_id   = $_SESSION['user_id'];

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

// লগইনকৃত admin-এর Users.mess_id ensure করা
ensureCurrentAdminMessLinked($mysqli, $mess_id, $user_id, $user_role);

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getData') {
    getData($mysqli, $mess_id);
} elseif ($action === 'getMember') {
    getMember($mysqli, $mess_id);
} elseif ($action === 'addMember') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add member']);
        exit;
    }
    addMember($mysqli, $mess_id);
} elseif ($action === 'toggleStatus') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can change status']);
        exit;
    }
    toggleStatus($mysqli, $mess_id);
} elseif ($action === 'toggleRole') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can change role']);
        exit;
    }
    toggleRole($mysqli, $mess_id);
} elseif ($action === 'removeMember') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can remove member']);
        exit;
    }
    removeMember($mysqli, $mess_id);
} elseif ($action === 'changeRoom') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can change room']);
        exit;
    }
    changeRoom($mysqli, $mess_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * লগইনকৃত admin-এর Users রেকর্ডে mess_id না থাকলে, current mess_id সেট করা
 */
function ensureCurrentAdminMessLinked($mysqli, $mess_id, $user_id, $user_role)
{
    if ($user_role !== 'admin') {
        return;
    }

    $sql = "SELECT mess_id FROM Users WHERE user_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        return;
    }

    if ($row['mess_id'] === null || $row['mess_id'] === '') {
        $sqlUp = "UPDATE Users SET mess_id = ? WHERE user_id = ?";
        $stmt = $mysqli->prepare($sqlUp);
        $stmt->bind_param('ss', $mess_id, $user_id);
        $stmt->execute();
    }
}

/**
 * getData: Members list + rooms list + stats
 */
function getData($mysqli, $mess_id)
{
    $data = [
        'success'      => true,
        'stats'        => [],
        'members'      => [],
        'rooms'        => [],
        'current_role' => isset($_SESSION['role']) ? $_SESSION['role'] : 'member',
    ];

    // Stats: total, active, on_leave, admins
    $sqlStats = "
        SELECT
            COUNT(*)                                                        AS total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END)             AS active,
            SUM(CASE WHEN status = 'on_leave' THEN 1 ELSE 0 END)           AS on_leave,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END)                AS admins
        FROM Users
        WHERE mess_id = ?
    ";
    $stmt = $mysqli->prepare($sqlStats);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    $data['stats'] = [
        'total'    => (int)$res['total'],
        'active'   => (int)$res['active'],
        'on_leave' => (int)$res['on_leave'],
        'admins'   => (int)$res['admins'],
    ];

    // All members for this mess (including admins)
    $sqlMembers = "
        SELECT
            u.user_id,
            u.full_name,
            u.role,
            u.status,
            u.contact_number,
            u.email_id,
            u.blood_group,
            u.profession,
            u.address,
            u.created_at,
            rm.room_id,
            r.room_number
        FROM Users u
        LEFT JOIN room_members rm
            ON u.user_id = rm.user_id AND rm.is_current = 1
        LEFT JOIN rooms r
            ON rm.room_id = r.room_id
        WHERE u.mess_id = ?
        ORDER BY u.full_name ASC
    ";
    $stmt = $mysqli->prepare($sqlMembers);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $currentUser = $_SESSION['user_id'];

    while ($row = $result->fetch_assoc()) {
        $data['members'][] = [
            'user_id'       => $row['user_id'],
            'full_name'     => $row['full_name'],
            'role'          => $row['role'],
            'status'        => $row['status'],
            'contact'       => $row['contact_number'],
            'email'         => $row['email_id'],
            'blood_group'   => $row['blood_group'],
            'profession'    => $row['profession'],
            'address'       => $row['address'],
            'joined_date'   => $row['created_at'],
            'room_id'       => $row['room_id'],
            'room_number'   => $row['room_number'],
            'is_me'         => ($row['user_id'] === $currentUser),
        ];
    }

    // Rooms for dropdown
    $sqlRooms = "
        SELECT
            r.room_id,
            r.room_number,
            r.capacity,
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
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $vacant = (int)$row['capacity'] - (int)$row['occupancy'];
        if ($vacant < 0) {
            $vacant = 0;
        }
        $data['rooms'][] = [
            'room_id'      => (int)$row['room_id'],
            'room_number'  => $row['room_number'],
            'capacity'     => (int)$row['capacity'],
            'occupancy'    => (int)$row['occupancy'],
            'vacant'       => $vacant,
            'is_active'    => (int)$row['is_active'],
        ];
    }

    echo json_encode($data);
}

/**
 * getMember: single member details
 */
function getMember($mysqli, $mess_id)
{
    $user_id = isset($_GET['user_id']) ? trim($_GET['user_id']) : '';

    if ($user_id === '') {
        echo json_encode(['success' => false, 'message' => 'user_id is required']);
        return;
    }

    $sql = "
        SELECT
            u.user_id,
            u.full_name,
            u.gender,
            u.contact_number,
            u.email_id,
            u.blood_group,
            u.role,
            u.religion,
            u.profession,
            u.address,
            u.created_at,
            u.status,
            rm.room_id,
            r.room_number
        FROM Users u
        LEFT JOIN room_members rm
            ON u.user_id = rm.user_id AND rm.is_current = 1
        LEFT JOIN rooms r
            ON rm.room_id = r.room_id
        WHERE u.user_id = ? AND u.mess_id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }

    echo json_encode([
        'success' => true,
        'member'  => [
            'user_id'     => $res['user_id'],
            'full_name'   => $res['full_name'],
            'gender'      => $res['gender'],
            'contact'     => $res['contact_number'],
            'email'       => $res['email_id'],
            'blood_group' => $res['blood_group'],
            'role'        => $res['role'],
            'religion'    => $res['religion'],
            'profession'  => $res['profession'],
            'address'     => $res['address'],
            'joined_date' => $res['created_at'],
            'status'      => $res['status'],
            'room_id'     => $res['room_id'],
            'room_number' => $res['room_number'],
        ]
    ]);
}

/**
 * addMember: create new member with room assignment
 */
function addMember($mysqli, $mess_id)
{
    $full_name  = trim($_POST['new_full_name'] ?? '');
    $contact    = trim($_POST['new_contact'] ?? '');
    $email      = trim($_POST['new_email'] ?? '');
    $password   = trim($_POST['new_password'] ?? '');
    $blood      = trim($_POST['new_blood'] ?? '');
    $role       = trim($_POST['new_role'] ?? 'member');
    $room_id    = (int)($_POST['new_room_id'] ?? 0);
    $profession = trim($_POST['new_profession'] ?? '');
    $address    = trim($_POST['new_address'] ?? '');

    if ($full_name === '' || $contact === '' || $email === '' || $password === '' || $room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please fill required fields']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email']);
        return;
    }

    if ($role !== 'admin' && $role !== 'member') {
        $role = 'member';
    }

    $sql = "SELECT 1 FROM Users WHERE email_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }

    $sqlRoom = "
        SELECT r.room_id, r.room_number, r.capacity, COALESCE(occ.cnt, 0) AS occ
        FROM rooms r
        LEFT JOIN (
            SELECT room_id, COUNT(*) AS cnt
            FROM room_members
            WHERE is_current = 1
            GROUP BY room_id
        ) AS occ
        ON r.room_id = occ.room_id
        WHERE r.room_id = ? AND r.mess_id = ? AND r.is_active = 1
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sqlRoom);
    $stmt->bind_param('is', $room_id, $mess_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found or not active']);
        return;
    }

    $vacant = (int)$room['capacity'] - (int)$room['occ'];
    if ($vacant <= 0) {
        echo json_encode(['success' => false, 'message' => 'Room has no free seat']);
        return;
    }

    $user_id = generate_new_id('MEMBER');
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate user ID']);
        return;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $mysqli->begin_transaction();

        $status = 'active';
        $sqlUser = "
            INSERT INTO Users
            (user_id, full_name, contact_number, email_id, blood_group, role, address, profession, password, mess_id, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)
        ";
        $stmt = $mysqli->prepare($sqlUser);
        $stmt->bind_param(
            'sssssssssss',
            $user_id,
            $full_name,
            $contact,
            $email,
            $blood,
            $role,
            $address,
            $profession,
            $hash,
            $mess_id,
            $status
        );
        $stmt->execute();

        $today = date('Y-m-d');
        $sqlRm = "
            INSERT INTO room_members (room_id, user_id, joined_date, is_current)
            VALUES (?,?,?,1)
        ";
        $stmt = $mysqli->prepare($sqlRm);
        $stmt->bind_param('iss', $room_id, $user_id, $today);
        $stmt->execute();

        $sqlMess = "SELECT mess_name FROM Mess WHERE mess_id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sqlMess);
        $stmt->bind_param('s', $mess_id);
        $stmt->execute();
        $messRow = $stmt->get_result()->fetch_assoc();
        $messName = $messRow ? $messRow['mess_name'] : $mess_id;

        $mysqli->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Member created',
            'member'  => [
                'user_id'   => $user_id,
                'full_name' => $full_name,
                'email'     => $email,
                'password'  => $password,
                'room_id'   => $room_id,
                'room'      => ($room['room_number'] ?: ('Room ' . $room['room_id'])) .
                               ' (capacity ' . $room['capacity'] . ')',
                'mess'      => $messName
            ]
        ]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error while creating member']);
    }
}

/**
 * toggleStatus: active <-> on_leave, inactive -> active
 */
function toggleStatus($mysqli, $mess_id)
{
    $user_id = trim($_POST['user_id'] ?? '');
    if ($user_id === '') {
        echo json_encode(['success' => false, 'message' => 'user_id required']);
        return;
    }

    $sql = "SELECT status FROM Users WHERE user_id = ? AND mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }

    $current = $row['status'];
    if ($current === 'active') {
        $new = 'on_leave';
    } elseif ($current === 'on_leave') {
        $new = 'active';
    } else {
        $new = 'active';
    }

    $sql = "UPDATE Users SET status = ? WHERE user_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $new, $user_id, $mess_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'new_status' => $new]);
}

/**
 * toggleRole: member <-> admin
 */
function toggleRole($mysqli, $mess_id)
{
    $user_id = trim($_POST['user_id'] ?? '');
    if ($user_id === '') {
        echo json_encode(['success' => false, 'message' => 'user_id required']);
        return;
    }

    $sql = "SELECT role FROM Users WHERE user_id = ? AND mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Member not found']);
        return;
    }

    $current = $row['role'];
    if ($current === 'admin') {
        $new = 'member';
    } else {
        $new = 'admin';
    }

    $sql = "UPDATE Users SET role = ? WHERE user_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('sss', $new, $user_id, $mess_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'new_role' => $new]);
}

/**
 * removeMember: mark inactive + remove from current room
 */
function removeMember($mysqli, $mess_id)
{
    $user_id = trim($_POST['user_id'] ?? '');
    if ($user_id === '') {
        echo json_encode(['success' => false, 'message' => 'user_id required']);
        return;
    }

    try {
        $mysqli->begin_transaction();

        $sql = "UPDATE Users SET status = 'inactive' WHERE user_id = ? AND mess_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss', $user_id, $mess_id);
        $stmt->execute();

        $sql = "UPDATE room_members SET is_current = 0 WHERE user_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();

        $mysqli->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error while removing member']);
    }
}

/**
 * changeRoom: move member to another room (with capacity check)
 */
function changeRoom($mysqli, $mess_id)
{
    $user_id = trim($_POST['user_id'] ?? '');
    $room_id = (int)($_POST['room_id'] ?? 0);

    if ($user_id === '' || $room_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'user_id and room_id required']);
        return;
    }

    $sqlUser = "SELECT 1 FROM Users WHERE user_id = ? AND mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sqlUser);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Member not found in this mess']);
        return;
    }

    $sqlRoom = "
        SELECT r.room_id, r.room_number, r.capacity, COALESCE(occ.cnt, 0) AS occ
        FROM rooms r
        LEFT JOIN (
            SELECT room_id, COUNT(*) AS cnt
            FROM room_members
            WHERE is_current = 1
            GROUP BY room_id
        ) AS occ
        ON r.room_id = occ.room_id
        WHERE r.room_id = ? AND r.mess_id = ? AND r.is_active = 1
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sqlRoom);
    $stmt->bind_param('is', $room_id, $mess_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found or not active']);
        return;
    }

    $vacant = (int)$room['capacity'] - (int)$room['occ'];
    if ($vacant <= 0) {
        echo json_encode(['success' => false, 'message' => 'Room has no free seat']);
        return;
    }

    try {
        $mysqli->begin_transaction();

        $sql = "UPDATE room_members SET is_current = 0 WHERE user_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $user_id);
        $stmt->execute();

        $today = date('Y-m-d');
        $sql = "INSERT INTO room_members (room_id, user_id, joined_date, is_current)
                VALUES (?,?,?,1)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iss', $room_id, $user_id, $today);
        $stmt->execute();

        $mysqli->commit();
        echo json_encode([
            'success'     => true,
            'room_id'     => $room['room_id'],
            'room_number' => $room['room_number']
        ]);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error while changing room']);
    }
}