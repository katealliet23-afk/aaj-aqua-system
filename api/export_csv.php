<?php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';

$type = $_GET['type'] ?? 'sales';
$fn   = 'aaj_aqua_v2_' . $type . '_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'.$fn.'"');
$out = fopen('php://output','w');

if ($type === 'sales') {
    fputcsv($out,['Order #','Type','Customer','Contact','Product','Qty','Unit Price','Total','Status','Date']);
    $res = mysqli_query($conn,"SELECT * FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) ORDER BY created_at DESC");
    while ($r=mysqli_fetch_assoc($res)) fputcsv($out,[$r['order_number'],$r['order_type'],$r['customer_name'],$r['contact_number'],$r['product_name'],$r['quantity'],$r['unit_price'],$r['total_amount'],$r['status'],$r['created_at']]);
} elseif ($type === 'inventory') {
    fputcsv($out,['ID','Product Name','Category','Qty','Reorder Level','Unit Price','Status']);
    $res = mysqli_query($conn,"SELECT * FROM inventory ORDER BY product_name");
    while ($r=mysqli_fetch_assoc($res)) {
        $st = $r['quantity']<=0?'Out of Stock':($r['quantity']<=$r['reorder_level']?'Low Stock':'In Stock');
        fputcsv($out,[$r['id'],$r['product_name'],$r['category'],$r['quantity'],$r['reorder_level'],$r['unit_price'],$st]);
    }
} elseif ($type === 'orders_all') {
    fputcsv($out,['Order #','Type','Customer','Contact','Address','Product','Qty','Unit Price','Total','Status','Source','Date']);
    $res = mysqli_query($conn,"SELECT * FROM orders ORDER BY created_at DESC");
    while ($r=mysqli_fetch_assoc($res)) fputcsv($out,[$r['order_number'],$r['order_type'],$r['customer_name'],$r['contact_number'],$r['delivery_address'],$r['product_name'],$r['quantity'],$r['unit_price'],$r['total_amount'],$r['status'],$r['source'],$r['created_at']]);
}

fclose($out); exit();
