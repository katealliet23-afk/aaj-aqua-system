<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id      = (int)($_POST['product_id']??0);
    $name    = clean($conn,$_POST['product_name']??'');
    $cat     = clean($conn,$_POST['category']??'Container');
    $qty     = (int)($_POST['quantity']??0);
    $reorder = (int)($_POST['reorder_level']??20);
    $price   = (float)($_POST['unit_price']??0);
    $redir   = $_POST['redirect']??'../pages/inventory.php';
    if ($id && $name) {
        mysqli_query($conn,"UPDATE inventory SET product_name='$name',category='$cat',quantity=$qty,reorder_level=$reorder,unit_price=$price WHERE id=$id");
        $_SESSION['flash']='Product updated!';
    }
    header("Location: $redir"); exit();
}
