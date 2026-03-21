<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';

function normalizeOrderUrl($url) {
    $url = trim($url);
    if ($url === '') return '/customer/order.php';
    if (preg_match('#^https?://#i', $url)) return $url;
    $url = trim($url, '/');
    return '/'. $url;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $url  = normalizeOrderUrl(clean($conn,$_POST['order_url']??''));
    $name = clean($conn,$_POST['station_name']??'');
    $r    = $_POST['redirect']??'../pages/settings.php';
    $e    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM qr_settings LIMIT 1"));
    if ($e) { mysqli_query($conn,"UPDATE qr_settings SET order_url='$url',station_name='$name'"); }
    else    { mysqli_query($conn,"INSERT INTO qr_settings (order_url,station_name) VALUES('$url','$name')"); }
    $_SESSION['flash']='Settings saved! ✅';
    header("Location: $r"); exit();
}
