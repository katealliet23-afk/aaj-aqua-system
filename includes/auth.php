<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function requireLogin($root = '../') {
    if (!isLoggedIn()) {
        header("Location: {$root}login.php");
        exit();
    }
}

function getUser() {
    return [
        'id'        => $_SESSION['user_id']   ?? 0,
        'username'  => $_SESSION['username']  ?? '',
        'full_name' => $_SESSION['full_name'] ?? 'Admin',
        'role'      => $_SESSION['role']      ?? 'owner',
        'initial'   => strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)),
    ];
}
?>
