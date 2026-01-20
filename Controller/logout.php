<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

// login page e pathai
header('Location: ../View/login.php');
exit;