<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name    = clean($conn,$_POST['product_name']??'');
    $cat     = clean($conn,$_POST['category']??'Container');
    $qty     = (int)($_POST['quantity']??0);
    $reorder = (int)($_POST['reorder_level']??20);
    $price   = (float)($_POST['unit_price']??0);
    $redir   = $_POST['redirect']??'../pages/inventory.php';
    if ($name) {
        mysqli_query($conn,"INSERT INTO inventory (product_name,category,quantity,reorder_level,unit_price) VALUES('$name','$cat',$qty,$reorder,$price)");
        $_SESSION['flash']="Product '$name' added!";
    }
    header("Location: $redir"); exit();
}
