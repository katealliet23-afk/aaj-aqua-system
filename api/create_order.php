<?php
session_start();
require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type    = clean($conn, $_POST['order_type']       ?? 'Walk-in');
    $cust    = clean($conn, $_POST['customer_name']    ?? '') ?: 'Walk-in';
    $contact = clean($conn, $_POST['contact_number']   ?? '');
    $addr    = clean($conn, $_POST['delivery_address'] ?? '');
    $pid     = (int)($_POST['product_id'] ?? 0);
    $qty     = (int)($_POST['quantity']   ?? 1);
    $redir   = $_POST['redirect'] ?? '../pages/orders.php';

    if ($pid < 1 || $qty < 1) { $_SESSION['flash'] = 'Invalid product or quantity.'; header("Location: $redir"); exit(); }

    $pr = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_name,unit_price FROM inventory WHERE id=$pid"));
    if (!$pr) { $_SESSION['flash'] = 'Product not found.'; header("Location: $redir"); exit(); }

    $onum   = generateOrderNumber($conn);
    $price  = (float)$pr['unit_price'];
    $total  = $price * $qty;
    $pname  = clean($conn, $pr['product_name']);
    $status = $type === 'Delivery' ? 'Pending' : 'Completed';
    $src    = $type === 'Walk-in'  ? 'walk-in' : 'admin';

    $sql = "INSERT INTO orders (order_number,order_type,customer_name,contact_number,delivery_address,product_id,product_name,quantity,unit_price,total_amount,status,source)
            VALUES ('$onum','$type','$cust','$contact','$addr',$pid,'$pname',$qty,$price,$total,'$status','$src')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['flash'] = "Order $onum placed!";
    } else {
        $_SESSION['flash'] = 'Error: ' . mysqli_error($conn);
    }
    header("Location: $redir"); exit();
}
