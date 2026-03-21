<?php // api/update_status.php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id     = (int)($_POST['order_id']??0);
    $status = clean($conn, $_POST['status']??'');
    $redir  = $_POST['redirect']??'../pages/orders.php';
    $valid  = ['Pending','In Process','Out for Delivery','Completed','Cancelled'];
    if ($id && in_array($status,$valid)) {
        mysqli_query($conn,"UPDATE orders SET status='$status' WHERE id=$id");
        $_SESSION['flash']="Order status → $status";
    }
    header("Location: $redir"); exit();
}
