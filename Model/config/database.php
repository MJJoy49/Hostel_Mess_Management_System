<?php
require_once __DIR__ . '/config.php';

//MySQLi connection
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($mysqli->connect_error) {
    die('Database connection failed.');
}

// Helper to get connection
function db()
{
    global $mysqli;
    return $mysqli;
}

