<?php
// Temporary script to fix QR URL
require_once 'includes/db.php';

// Fix the URL - remove duplicate paths and ensure proper .php extension
$correctUrl = '/customer/order.php';

// Check if qr_settings exists and update
$exists = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM qr_settings LIMIT 1"));

if ($exists) {
    mysqli_query($conn, "UPDATE qr_settings SET order_url='$correctUrl'");
} else {
    mysqli_query($conn, "INSERT INTO qr_settings (order_url, station_name) VALUES ('$correctUrl', 'AAJ AQUA')");
}

echo "QR URL fixed to: $correctUrl";
?>
