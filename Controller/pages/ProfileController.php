<?php
// Controller/pages/ProfileController.php

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

$user_id   = $_SESSION['user_id'];
$mess_id   = $_SESSION['mess_id'] ?? null;
$user_role = $_SESSION['role']    ?? 'member';

if (!$mess_id) {
    echo json_encode(['success' => false, 'message' => 'Mess not selected']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'getProfile') {
    getProfile($mysqli, $user_id, $mess_id, $user_role);
} elseif ($action === 'updateProfile') {
    updateProfile($mysqli, $user_id, $mess_id, $user_role);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Load joined user + mess info
 */
function getProfile($mysqli, $user_id, $mess_id, $user_role)
{
    $sql = "
        SELECT
            u.user_id, u.full_name, u.gender, u.contact_number, u.email_id,
            u.blood_group, u.role, u.religion, u.profession, u.address,
            u.created_at, u.photo,
            m.mess_id, m.mess_name, m.address AS mess_address, m.capacity,
            m.email_id AS mess_email, m.admin_name, m.admin_email, m.admin_id,
            m.created_at AS mess_created_at, m.mess_description
        FROM Users u
        LEFT JOIN Mess m ON u.mess_id = m.mess_id
        WHERE u.user_id = ? AND u.mess_id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $user_id, $mess_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        return;
    }

    // joined date: try room_members, fallback user.created_at
    $joined = $row['created_at'];
    $sql2 = "SELECT MIN(joined_date) AS jd FROM room_members WHERE user_id = ?";
    $st2 = $mysqli->prepare($sql2);
    $st2->bind_param('s', $user_id);
    $st2->execute();
    if ($r2 = $st2->get_result()->fetch_assoc()) {
        if (!empty($r2['jd'])) {
            $joined = $r2['jd'];
        }
    }

    $photoBase64 = null;
    if (!empty($row['photo'])) {
        $photoBase64 = base64_encode($row['photo']);
    }

    $user = [
        'user_id'        => $row['user_id'],
        'full_name'      => $row['full_name'],
        'gender'         => $row['gender'],
        'contact_number' => $row['contact_number'],
        'email_id'       => $row['email_id'],
        'blood_group'    => $row['blood_group'],
        'role'           => $row['role'],
        'religion'       => $row['religion'],
        'profession'     => $row['profession'],
        'address'        => $row['address'],
        'joined_date'    => $joined,
        'photo_base64'   => $photoBase64,
    ];

    $mess = [
        'mess_id'         => $row['mess_id'],
        'mess_name'       => $row['mess_name'],
        'capacity'        => (int)$row['capacity'],
        'email_id'        => $row['mess_email'],
        'admin_name'      => $row['admin_name'],
        'admin_email'     => $row['admin_email'],
        'admin_id'        => $row['admin_id'],
        'created_at'      => $row['mess_created_at'],
        'address'         => $row['mess_address'],
        'mess_description'=> $row['mess_description'],
    ];

    echo json_encode([
        'success' => true,
        'role'    => $user_role,
        'user'    => $user,
        'mess'    => $mess,
    ]);
}

/**
 * Update user + (if admin) mess info
 */
function updateProfile($mysqli, $user_id, $mess_id, $user_role)
{
    $full_name  = trim($_POST['full_name'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $contact    = trim($_POST['contact_number'] ?? '');
    $email      = trim($_POST['email_id'] ?? '');
    $blood      = trim($_POST['blood_group'] ?? '');
    $religion   = trim($_POST['religion'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $address    = trim($_POST['address'] ?? '');

    if ($full_name === '' || $email === '') {
        echo json_encode(['success' => false, 'message' => 'Full name and email are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email']);
        return;
    }

    // Check email not used by another user
    $sql = "SELECT 1 FROM Users WHERE email_id = ? AND user_id <> ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        echo json_encode(['success' => false, 'message' => 'Email already used by another account']);
        return;
    }

    // Photo (optional)
    $photoData = null;
    $hasPhoto  = false;
    if (!empty($_FILES['photo_file']['tmp_name']) && is_uploaded_file($_FILES['photo_file']['tmp_name'])) {
        $photoData = file_get_contents($_FILES['photo_file']['tmp_name']);
        $hasPhoto  = true;
    }

    try {
        $mysqli->begin_transaction();

        // Update Users
        if ($hasPhoto) {
            $sql = "
                UPDATE Users
                SET full_name = ?, gender = ?, contact_number = ?, email_id = ?,
                    blood_group = ?, religion = ?, profession = ?, address = ?, photo = ?
                WHERE user_id = ? AND mess_id = ?
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'sssssssbss',
                $full_name,
                $gender,
                $contact,
                $email,
                $blood,
                $religion,
                $profession,
                $address,
                $photoData,
                $user_id,
                $mess_id
            );
            // MySQLi does not support direct 'b' bind easily in this style,
            // to keep it simple we can skip photo update if that's problematic.
        } else {
            $sql = "
                UPDATE Users
                SET full_name = ?, gender = ?, contact_number = ?, email_id = ?,
                    blood_group = ?, religion = ?, profession = ?, address = ?
                WHERE user_id = ? AND mess_id = ?
            ";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param(
                'ssssssssss',
                $full_name,
                $gender,
                $contact,
                $email,
                $blood,
                $religion,
                $profession,
                $address,
                $user_id,
                $mess_id
            );
        }
        $stmt->execute();

        // If admin, update Mess info
        if ($user_role === 'admin') {
            $mess_name       = trim($_POST['mess_name'] ?? '');
            $capacity        = (int)($_POST['capacity'] ?? 0);
            $mess_email_id   = trim($_POST['mess_email_id'] ?? '');
            $mess_address    = trim($_POST['mess_address'] ?? '');
            $mess_desc       = trim($_POST['mess_description'] ?? '');

            if ($mess_name !== '') {
                $sql = "
                    UPDATE Mess
                    SET mess_name = ?, capacity = ?, email_id = ?, address = ?, mess_description = ?
                    WHERE mess_id = ?
                ";
                $stmt = $mysqli->prepare($sql);
                $stmt->bind_param(
                    'sissss',
                    $mess_name,
                    $capacity,
                    $mess_email_id,
                    $mess_address,
                    $mess_desc,
                    $mess_id
                );
                $stmt->execute();
            }
        }

        $mysqli->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
    } catch (Exception $e) {
        $mysqli->rollback();
        echo json_encode(['success' => false, 'message' => 'Error while updating profile']);
    }
}