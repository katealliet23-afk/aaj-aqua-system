<?php // api/update_qr.php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';

function normalizeOrderUrl($url) {
    $url = trim($url);
    if ($url === '') return '/customer/order.php';

    // If user entered absolute URL, normalize to relative path to avoid duplication
    if (preg_match('#^https?://#i', $url)) {
        $parts = parse_url($url);
        $path  = $parts['path'] ?? '/customer/order.php';
        $idx   = strpos($path, '/customer/order.php');
        if ($idx !== false) {
            $path = substr($path, $idx);
        }
        if (isset($parts['query']) && $parts['query'] !== '') {
            $path .= '?' . $parts['query'];
        }
        return $path;
    }

    $url = trim($url, '/');
    return '/' . $url;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $url  = normalizeOrderUrl(clean($conn,$_POST['order_url']??''));
    $name = clean($conn,$_POST['station_name']??'AAJ AQUA');
    $r    = $_POST['redirect']??'../pages/qrmanage.php';
    if ($url) {
        $exists = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM qr_settings LIMIT 1"));
        if ($exists) { mysqli_query($conn,"UPDATE qr_settings SET order_url='$url',station_name='$name'"); }
        else         { mysqli_query($conn,"INSERT INTO qr_settings (order_url,station_name) VALUES('$url','$name')"); }
        $_SESSION['flash']='QR Code settings saved! 📲';
    }
    header("Location: $r"); exit();
}
