<?php
// includes/db_cloud.php — Cloud Database Connection for Production

// Check if we're in production (Vercel) or development (localhost)
$isProduction = !empty($_SERVER['VERCEL']) || 
               (!empty($_SERVER['HTTP_HOST']) && 
                strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false) ||
               (!empty($_SERVER['HTTP_HOST']) && 
                strpos($_SERVER['HTTP_HOST'], 'localhost') === false);

if ($isProduction) {
    // Production: Railway Cloud Database
    define('DB_HOST', 'hopper.proxy.rlwy.net');
    define('DB_USER', 'root');
    define('DB_PASS', 'SgKEGFibNBCsLGrVXGIKHQSUWqDSSbDj');
    define('DB_NAME', 'railway');
    define('DB_PORT', '34578');
} else {
    // Development: Local XAMPP
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'aaj_aqua_v2');
    define('DB_PORT', '3306');
}

// Create connection
$host = DB_HOST . ':' . DB_PORT;
$conn = mysqli_connect($host, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    if ($isProduction) {
        die('<div style="font-family:sans-serif;padding:40px;color:#991b1b;background:#fee2e2;border-radius:12px;margin:40px auto;max-width:500px;">
            <h2>⚠️ Database Connection Failed</h2>
            <p>Cloud database connection failed. Please check Railway connection details.</p>
            <p style="font-size:13px;margin-top:10px;">Make sure your Railway database is running and connection details are correct.</p>
        </div>');
    } else {
        die('<div style="font-family:sans-serif;padding:40px;color:#991b1b;background:#fee2e2;border-radius:12px;margin:40px auto;max-width:500px;">
            <h2>⚠️ Database Connection Failed</h2>
            <p>' . mysqli_connect_error() . '</p>
            <p style="font-size:13px;margin-top:10px;">Make sure XAMPP MySQL is running and you have imported <b>database.sql</b>.</p>
        </div>');
    }
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
