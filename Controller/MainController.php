<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, go to main
function redirectIfLoggedIn()
{
    if (!empty($_SESSION['user_id'])) {
        header('Location: main.php');
        exit;
    }
}

// Protect pages (need login)
function ensureAuthenticated()
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Select current page from ?page=
function resolvePage($allowedPages, $default = 'profile')
{
    $page = isset($_GET['page']) ? $_GET['page'] : $default;
    if (!in_array($page, $allowedPages, true)) {
        $page = $default;
    }
    return $page;
}

// Simple helper: is admin?
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}