<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Generate new ID for ADMIN / MEMBER / MESS
 * Format: 
 *   ADMIN  -> AD01-001-25
 *   MEMBER -> MN01-0001-25
 *   MESS   -> MS01-0001-25
 */
function generate_new_id($type, $year = null)
{
    $mysqli = db();

    if ($year === null) {
        $year = date('Y');
    }

    $type  = strtoupper(trim($type));
    $year2 = substr($year, -2); // 2025 -> 25

    if ($type !== 'ADMIN' && $type !== 'MEMBER' && $type !== 'MESS') {
        return false;
    }

    if ($type === 'ADMIN') {
        $prefix   = 'AD';
        $maxMain  = 99;
        $table    = 'Users';
        $column   = 'user_id';
        $subLimit = 999;   // 3 digit
    } elseif ($type === 'MEMBER') {
        $prefix   = 'MN';
        $maxMain  = 9999;
        $table    = 'Users';
        $column   = 'user_id';
        $subLimit = 9999;  // 4 digit
    } else { // MESS
        $prefix   = 'MS';
        $maxMain  = 9999;
        $table    = 'Mess';
        $column   = 'mess_id';
        $subLimit = 9999;  // 4 digit
    }

    $likePattern = $prefix . "%-" . $year2;

    $sql = "
        SELECT $column
        FROM $table
        WHERE $column LIKE ?
        ORDER BY $column DESC
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();

    // ==== IMPORTANT PART ====
    $newMainNumber = 1;
    $newSubNumber  = 1;

    if ($row = $result->fetch_assoc()) {
        
        $lastId = $row[$column]; // example: AD01-015-25

        $parts = explode('-', $lastId);
        if (count($parts) >= 2) {
            $mainPart = $parts[0]; // AD01
            $subPart  = $parts[1]; // 015

            $mainNumber = (int) str_replace($prefix, '', $mainPart);
            $subNumber  = (int) $subPart;

            if ($subNumber >= $subLimit) {
                $newMainNumber = $mainNumber + 1;
                $newSubNumber  = 1;
            } else {
                $newMainNumber = $mainNumber;
                $newSubNumber  = $subNumber + 1;
            }

            if ($newMainNumber > $maxMain) {
                return false;
            }
        }
        
    }

    // ==== main part ====
    if ($newMainNumber < 10) {
        $mainWithZero = '0' . $newMainNumber;
    } else {
        $mainWithZero = (string) $newMainNumber;
    }

    // ==== sub part ====
    if ($type === 'ADMIN') {
        // 3 digit
        if ($newSubNumber < 10) {
            $subWithZero = '00' . $newSubNumber;
        } elseif ($newSubNumber < 100) {
            $subWithZero = '0' . $newSubNumber;
        } else {
            $subWithZero = (string) $newSubNumber;
        }
    } else {
        // 4 digit
        if ($newSubNumber < 10) {
            $subWithZero = '000' . $newSubNumber;
        } elseif ($newSubNumber < 100) {
            $subWithZero = '00' . $newSubNumber;
        } elseif ($newSubNumber < 1000) {
            $subWithZero = '0' . $newSubNumber;
        } else {
            $subWithZero = (string) $newSubNumber;
        }
    }

    $newId = $prefix . $mainWithZero . '-' . $subWithZero . '-' . $year2;
    return $newId;
}