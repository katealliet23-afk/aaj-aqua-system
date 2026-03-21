<?php
// includes/db.php — Database Connection
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // XAMPP default: no password
define('DB_NAME', 'aaj_aqua_v2');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('<div style="font-family:sans-serif;padding:40px;color:#991b1b;background:#fee2e2;border-radius:12px;margin:40px auto;max-width:500px;">
        <h2>⚠️ Database Connection Failed</h2>
        <p>' . mysqli_connect_error() . '</p>
        <p style="font-size:13px;margin-top:10px;">Make sure XAMPP MySQL is running and you have imported <b>database.sql</b>.</p>
    </div>');
}

mysqli_set_charset($conn, 'utf8');

// Ensure qr_image column exists
$checkCol = mysqli_query($conn, "SHOW COLUMNS FROM qr_settings LIKE 'qr_image'");
if (mysqli_num_rows($checkCol) === 0) {
    mysqli_query($conn, "ALTER TABLE qr_settings ADD COLUMN qr_image VARCHAR(255) NULL");
}

function clean($conn, $val) {
    return mysqli_real_escape_string($conn, trim((string)$val));
}

function generateOrderNumber($conn) {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM orders");
    $row = mysqli_fetch_assoc($res);
    return 'ORD-' . str_pad($row['cnt'] + 1, 4, '0', STR_PAD_LEFT);
}
?>
