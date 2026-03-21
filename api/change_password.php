<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $cur  = $_POST['current_password']??'';
    $new  = $_POST['new_password']??'';
    $conf = $_POST['confirm_password']??'';
    $r    = $_POST['redirect']??'../pages/settings.php';
    $uid  = (int)$_SESSION['user_id'];
    $row  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT password FROM users WHERE id=$uid"));
    if ($row['password'] !== md5($cur)) {
        $_SESSION['flash_err']='Current password is incorrect.';
    } elseif ($new !== $conf) {
        $_SESSION['flash_err']='New passwords do not match.';
    } elseif (strlen($new) < 6) {
        $_SESSION['flash_err']='Password must be at least 6 characters.';
    } else {
        $h = md5($new);
        mysqli_query($conn,"UPDATE users SET password='$h' WHERE id=$uid");
        $_SESSION['flash']='Password changed! 🔒';
    }
    header("Location: $r"); exit();
}
