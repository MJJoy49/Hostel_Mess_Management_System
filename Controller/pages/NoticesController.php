<?php
// Controller/pages/NoticesController.php

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

$action = $_GET['action'] ?? '';

if ($action === 'getData') {
    getData($mysqli, $mess_id, $user_role);
} elseif ($action === 'addAnnouncement') {
    addAnnouncement($mysqli, $mess_id, $user_id);
} elseif ($action === 'addSeatAd') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can add seat ad']);
        exit;
    }
    addSeatAd($mysqli, $mess_id);
} elseif ($action === 'deleteSeatAd') {
    if ($user_role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Only admin can delete seat ad']);
        exit;
    }
    deleteSeatAd($mysqli, $mess_id);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Get stats + lists for notices page
 */
function getData($mysqli, $mess_id, $user_role)
{
    $today  = date('Y-m-d');
    $last10 = date('Y-m-d', strtotime($today . ' -9 days'));

    $filterDate = $_GET['filter_date'] ?? '';

    // Mess info (for Location / UI)
    $messName    = '';
    $messAddress = '';
    $sql = "SELECT mess_name, address FROM Mess WHERE mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $messName    = $row['mess_name'] ?? '';
        $messAddress = $row['address']    ?? '';
    }

    // Stats
    // Announcements (last 10 days)
    $sql = "SELECT COUNT(*) AS c
            FROM announcements
            WHERE mess_id = ? AND DATE(created_at) >= ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $mess_id, $last10);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalAnns = (int)$row['c'];

    // Active seat ads
    $sql = "SELECT COUNT(*) AS c FROM seat_ads WHERE mess_id = ? AND is_active = 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $activeAds = (int)$row['c'];

    // Pending seat requests
    $sql = "
        SELECT COUNT(*) AS c
        FROM request_seat r
        JOIN seat_ads s ON r.ad_id = s.ad_id
        WHERE s.mess_id = ? AND r.status = 'pending'
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $pendingReq = (int)$row['c'];

    // Announcements list
    if ($filterDate && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filterDate)) {
        $sql = "
            SELECT a.announce_id, a.title, a.message, a.posted_by, a.created_at,
                   u.role
            FROM announcements a
            LEFT JOIN Users u ON a.posted_by = u.user_id
            WHERE a.mess_id = ? AND DATE(a.created_at) = ?
            ORDER BY a.created_at DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss', $mess_id, $filterDate);
    } else {
        $sql = "
            SELECT a.announce_id, a.title, a.message, a.posted_by, a.created_at,
                   u.role
            FROM announcements a
            LEFT JOIN Users u ON a.posted_by = u.user_id
            WHERE a.mess_id = ? AND DATE(a.created_at) >= ?
            ORDER BY a.created_at DESC
        ";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ss', $mess_id, $last10);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $anns = [];
    $postedByNames = [];

    while ($row = $res->fetch_assoc()) {
        $postedBy = $row['posted_by'];
        if ($postedBy && !isset($postedByNames[$postedBy])) {
            $sql2 = "SELECT full_name FROM Users WHERE user_id = ? LIMIT 1";
            $st2  = $mysqli->prepare($sql2);
            $st2->bind_param('s', $postedBy);
            $st2->execute();
            $r2 = $st2->get_result()->fetch_assoc();
            $postedByNames[$postedBy] = $r2 ? $r2['full_name'] : $postedBy;
        }

        $typeLabel = ($row['role'] === 'admin') ? 'Admin' : 'Member';

        $anns[] = [
            'announce_id'    => (int)$row['announce_id'],
            'title'          => $row['title'],
            'message'        => $row['message'],
            'posted_by'      => $postedBy,
            'posted_by_name' => $postedBy ? $postedByNames[$postedBy] : '-',
            'created_at'     => $row['created_at'],
            'type_label'     => $typeLabel,
        ];
    }

    // Seat ads list (admin sees only own mess)
    $seatAds = [];
    $sql = "
        SELECT s.ad_id, s.vacant_seats, s.rent_per_seat,
               s.contact_person, s.contact_number, s.ad_title,
               s.mess_address, s.room_id,
               r.room_number
        FROM seat_ads s
        LEFT JOIN rooms r ON s.room_id = r.room_id
        WHERE s.mess_id = ? AND s.is_active = 1
        ORDER BY s.posted_at DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $roomLabel = '-';
        if (!empty($row['room_number'])) {
            $roomLabel = 'Room ' . $row['room_number'];
        }
        $seatAds[] = [
            'ad_id'         => (int)$row['ad_id'],
            'ad_title'      => $row['ad_title'],
            'vacant_seats'  => (int)$row['vacant_seats'],
            'rent_per_seat' => (float)$row['rent_per_seat'],
            'contact_person'=> $row['contact_person'],
            'contact_number'=> $row['contact_number'],
            'room_label'    => $roomLabel,
            'mess_address'  => $row['mess_address'],
        ];
    }

    // Seat requests
    $requests = [];
    $sql = "
        SELECT r.request_id, r.name, r.contact_number, r.profession,
               r.description, r.status, r.request_date,
               s.ad_title
        FROM request_seat r
        JOIN seat_ads s ON r.ad_id = s.ad_id
        WHERE s.mess_id = ?
        ORDER BY r.request_date DESC
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $requests[] = [
            'request_id'     => (int)$row['request_id'],
            'name'           => $row['name'],
            'contact_number' => $row['contact_number'],
            'profession'     => $row['profession'],
            'description'    => $row['description'],
            'status'         => $row['status'],
            'request_date'   => $row['request_date'],
            'ad_title'       => $row['ad_title'],
        ];
    }

    echo json_encode([
        'success' => true,
        'role'    => $user_role,
        'mess'    => [
            'name'    => $messName,
            'address' => $messAddress,
        ],
        'stats'   => [
            'total_announcements' => $totalAnns,
            'active_ads'          => $activeAds,
            'pending_requests'    => $pendingReq,
        ],
        'announcements' => $anns,
        'seat_ads'      => $seatAds,
        'requests'      => $requests,
    ]);
}

/**
 * Add new announcement (admin + member both)
 */
function addAnnouncement($mysqli, $mess_id, $user_id)
{
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($title === '' || $message === '') {
        echo json_encode(['success' => false, 'message' => 'Title and message are required']);
        return;
    }

    $sql = "INSERT INTO announcements (mess_id, title, message, posted_by)
            VALUES (?,?,?,?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ssss', $mess_id, $title, $message, $user_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Announcement added']);
}

/**
 * Add new seat ad (admin only)
 * Location = Mess.address, Room text description e add kortechi
 */
function addSeatAd($mysqli, $mess_id)
{
    $title    = trim($_POST['ad_title'] ?? '');
    $roomText = trim($_POST['room_text'] ?? '');
    $vacant   = (int)($_POST['vacant_seats'] ?? 0);
    $rent     = (float)($_POST['rent_per_seat'] ?? 0);
    $person   = trim($_POST['contact_person'] ?? '');
    $number   = trim($_POST['contact_number'] ?? '');
    $desc     = trim($_POST['ad_description'] ?? '');

    if ($title === '' || $vacant <= 0 || $rent <= 0 || $person === '' || $number === '') {
        echo json_encode(['success' => false, 'message' => 'Please fill required fields']);
        return;
    }

    // get mess address to store in seat_ads.mess_address
    $sql = "SELECT address FROM Mess WHERE mess_id = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $messAddress = $row ? $row['address'] : '';

    // roomText ke extra info hisebe description e jog kora
    if ($roomText !== '') {
        $prefix = 'Room: ' . $roomText;
        if ($desc !== '') {
            $desc = $prefix . ' | ' . $desc;
        } else {
            $desc = $prefix;
        }
    }

    $sql = "INSERT INTO seat_ads
            (mess_id, room_id, vacant_seats, rent_per_seat,
             contact_person, contact_number, ad_title, mess_address, ad_description, is_active)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        'sidsssss',
        $mess_id,
        $vacant,
        $rent,
        $person,
        $number,
        $title,
        $messAddress,
        $desc
    );
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Seat ad added']);
}

/**
 * Delete seat ad (admin only) – for (x) action
 */
function deleteSeatAd($mysqli, $mess_id)
{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid id']);
        return;
    }

    $sql = "DELETE FROM seat_ads WHERE ad_id = ? AND mess_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('is', $id, $mess_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Seat ad deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
}
?>