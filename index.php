<?php
// Simple router for Vercel
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

session_start();
$is_logged_in = isset($_SESSION['user_id']);

// Handle different paths
switch ($path) {
    case '/':
    case '/dashboard':
        include $is_logged_in ? __DIR__ . '/pages/dashboard.php' : __DIR__ . '/login.php';
        break;
    case '/login.php':
        include __DIR__ . '/login.php';
        break;
    case '/pages/qrmanage.php':
        include $is_logged_in ? __DIR__ . '/pages/qrmanage.php' : __DIR__ . '/login.php';
        break;
    case '/customer/order.php':
        include __DIR__ . '/customer/order.php';
        break;
    default:
        if (strpos($path, '/pages/') === 0) {
            include $is_logged_in ? __DIR__ . '/pages/dashboard.php' : __DIR__ . '/login.php';
        } elseif (strpos($path, '/customer/') === 0) {
            include __DIR__ . '/customer/order.php';
        } else {
            include $is_logged_in ? __DIR__ . '/pages/dashboard.php' : __DIR__ . '/login.php';
        }
        break;
}
?>
