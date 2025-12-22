<?php

include '../config/database.php'; // database connection ($mysqli)

// Browser ke boli je JSON response pathabo
header("Content-Type: application/json");

// Frontend theke data receive kori
$data = json_decode(file_get_contents("php://input"), true);

// Jodi type ba year na thake, tahole error
if (!isset($data['type']) || !isset($data['year'])) {
    echo json_encode([
        "success" => false,
        "msg" => "give here type and year!"
    ]);
    exit;
}

// Type clean kori (ADMIN / MEMBER / MESS)
$type = strtoupper(trim($data['type']));

// Year theke sudhu last 2 digit nei (2025 → 25)
$year = substr(trim($data['year']), -2);

// Sudhu ei 3 ta type allow
if ($type !== "ADMIN" && $type !== "MEMBER" && $type !== "MESS") {
    echo json_encode([
        "success" => false,
        "msg" => "vul type pathano hoise"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Type onujayi rule set kori
|--------------------------------------------------------------------------
*/
if ($type === "ADMIN") {

    $prefix   = "AD";
    $maxMain  = 99;
    $table    = "users";
    $column   = "user_id";
    $subLimit = 999;

} elseif ($type === "MEMBER") {

    $prefix   = "MEM";
    $maxMain  = 9999;
    $table    = "users";
    $column   = "user_id";
    $subLimit = 9999;

} else { // MESS

    $prefix   = "MES";
    $maxMain  = 9999;
    $table    = "mess";
    $column   = "mess_id";
    $subLimit = 9999;
}


$likePattern = $prefix . "%-%-" . $year;

$sql = "
    SELECT $column 
    FROM $table 
    WHERE $column LIKE '$likePattern'
    ORDER BY $column DESC 
    LIMIT 1
";

$result = mysqli_query($mysqli, $sql);

// Default value
$newMainNumber = 1;
$newSubNumber  = 1;

if (mysqli_num_rows($result) > 0) {

    $row    = mysqli_fetch_assoc($result);
    $lastId = $row[$column]; // example: AD01-015-25

    // ID ke part e vag kori
    $parts = explode("-", $lastId);

    $mainPart = $parts[0]; // AD01
    $subPart  = $parts[1]; // 015

    // AD01 → 01
    $mainNumber = (int) str_replace($prefix, "", $mainPart);

    // 015 → 15
    $subNumber = (int) $subPart;

    // Sub limit cross korle main barai
    if ($subNumber >= $subLimit) {
        $newMainNumber = $mainNumber + 1;
        $newSubNumber  = 1;
    } else {
        $newMainNumber = $mainNumber;
        $newSubNumber  = $subNumber + 1;
    }

    // Main limit cross korle stop
    if ($newMainNumber > $maxMain) {
        echo json_encode([
            "success" => false,
            "msg" => "ID generate limit cross"
        ]);
        exit;
    }
}



// Main number (2 digit)
if ($newMainNumber < 10) {
    $mainWithZero = "0" . $newMainNumber;
} else {
    $mainWithZero = $newMainNumber;
}

// Sub number
if ($type === "ADMIN") {

    if ($newSubNumber < 10) {
        $subWithZero = "00" . $newSubNumber;
    } elseif ($newSubNumber < 100) {
        $subWithZero = "0" . $newSubNumber;
    } else {
        $subWithZero = $newSubNumber;
    }

} else { 

    if ($newSubNumber < 10) {
        $subWithZero = "000" . $newSubNumber;
    } elseif ($newSubNumber < 100) {
        $subWithZero = "00" . $newSubNumber;
    } elseif ($newSubNumber < 1000) {
        $subWithZero = "0" . $newSubNumber;
    } else {
        $subWithZero = $newSubNumber;
    }
}

// final ID
// example: AD01-001-25
$newId = $prefix . $mainWithZero . "-" . $subWithZero . "-" . $year;

// Frontend e response pathai
echo json_encode([
    "success" => true,
    "id" => $newId
]);

// database connection close
$mysqli->close();
?>
