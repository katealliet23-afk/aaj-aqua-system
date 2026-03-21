<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['order_id']??0);
    $r  = $_POST['redirect']??'../pages/orders.php';
    if ($id) { mysqli_query($conn,"DELETE FROM orders WHERE id=$id"); $_SESSION['flash']='Order deleted.'; }
    header("Location: $r"); exit();
}
