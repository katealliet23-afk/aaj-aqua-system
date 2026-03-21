<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['product_id']??0);
    $r  = $_POST['redirect']??'../pages/inventory.php';
    if ($id) {
        $chk = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM orders WHERE product_id=$id"));
        if ($chk['c'] > 0) {
            $_SESSION['flash_err'] = 'Cannot delete: product has linked orders.';
        } else {
            mysqli_query($conn,"DELETE FROM inventory WHERE id=$id");
            $_SESSION['flash'] = 'Product deleted.';
        }
    }
    header("Location: $r"); exit();
}
