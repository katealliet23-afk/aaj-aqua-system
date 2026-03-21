<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id  = (int)($_POST['product_id']??0);
    $add = (int)($_POST['add_qty']??0);
    $r   = $_POST['redirect']??'../pages/inventory.php';
    if ($id && $add > 0) {
        mysqli_query($conn,"UPDATE inventory SET quantity=quantity+$add WHERE id=$id");
        $_SESSION['flash']="Added $add units to stock! ✅";
    }
    header("Location: $r"); exit();
}
