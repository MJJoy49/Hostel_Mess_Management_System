<?php
$current = $_GET['page'] ?? 'profile';

function activeClass($page, $current) {
    return $page === $current ? ' style="border-color: var(--accent); color: var(--primary-hover);"' : '';
}
?>
<aside class="sidebar">
    <button class="sidebar-btn" onclick="location.href='main.php?page=profile'"<?= activeClass('profile', $current) ?>>
        <span class="sidebar-btn-icon">🏨</span>
        <span class="sidebar-btn-label">Hostel Info</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=dashboard'"<?= activeClass('dashboard', $current) ?>>
        <span class="sidebar-btn-icon">🏠</span>
        <span class="sidebar-btn-label">Dashboard</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=meals'"<?= activeClass('meals', $current) ?>>
        <span class="sidebar-btn-icon">🍽️</span>
        <span class="sidebar-btn-label">Meals</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=members'"<?= activeClass('members', $current) ?>>
        <span class="sidebar-btn-icon">👥</span>
        <span class="sidebar-btn-label">Members</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=bills'"<?= activeClass('bills', $current) ?>>
        <span class="sidebar-btn-icon">🧾</span>
        <span class="sidebar-btn-label">Bills</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=payments'"<?= activeClass('payments', $current) ?>>
        <span class="sidebar-btn-icon">💳</span>
        <span class="sidebar-btn-label">Payments</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=rooms'"<?= activeClass('rooms', $current) ?>>
        <span class="sidebar-btn-icon">🛏️</span>
        <span class="sidebar-btn-label">Rooms</span>
    </button>

    <button class="sidebar-btn" onclick="location.href='main.php?page=notices'"<?= activeClass('notices', $current) ?>>
        <span class="sidebar-btn-icon">📢</span>
        <span class="sidebar-btn-label">Notices</span>
    </button>

</aside>