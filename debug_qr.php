<?php
require_once 'includes/db.php';

// Get current QR settings
$qrRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM qr_settings LIMIT 1"));

echo "<h2>QR Settings Debug</h2>";
echo "<pre>";
echo "Raw order_url: " . ($qrRow['order_url'] ?? 'NOT SET') . "\n";
echo "Station name: " . ($qrRow['station_name'] ?? 'NOT SET') . "\n";

// Test URL resolution
function getAppBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path   = dirname($_SERVER['SCRIPT_NAME']);
    $root   = preg_replace('#/pages$#', '', $path);
    return rtrim($scheme . '://' . $host . $root, '/');
}

function resolveOrderUrl($url) {
    $url = trim($url);
    if ($url === '') {
        $path = '/customer/order.php';
    } elseif (preg_match('#^https?://#i', $url)) {
        $parts = parse_url($url);
        $pathRaw = $parts['path'] ?? '/customer/order.php';
        $idx = strpos($pathRaw, '/customer/order.php');
        if ($idx !== false) {
            $path = substr($pathRaw, $idx);
        } else {
            $path = rtrim($pathRaw, '/');
        }
        if (isset($parts['query']) && $parts['query'] !== '') {
            $path .= '?' . $parts['query'];
        }
        $baseForMobile = getAppBaseUrl();
        // If we're on localhost, phones can't reach it, so use the actual network IP
        if (strpos($baseForMobile, 'localhost') !== false) {
            // Try to get the actual local IP that phones can connect to
            $baseForMobile = preg_replace('/localhost/', '192.168.1.45', $baseForMobile);
        }
        return $baseForMobile . $path;
    } else {
        $path = '/' . ltrim($url, '/');
    }
    return getAppBaseUrl() . $path;
}

$rawOrderUrl = $qrRow['order_url'] ?? '/customer/order.php';
$qrUrl = resolveOrderUrl($rawOrderUrl);

echo "Resolved URL: " . $qrUrl . "\n";
echo "</pre>";

// Fix the URL if it's malformed
if (strpos($rawOrderUrl, 'order.p') !== false || strpos($rawOrderUrl, 'aaj-aqua-v3/aaj-aqua-v3') !== false) {
    echo "<h3 style='color: red;'>URL is malformed! Fixing...</h3>";
    $correctUrl = '/customer/order.php';
    $exists = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM qr_settings LIMIT 1"));
    if ($exists) {
        mysqli_query($conn, "UPDATE qr_settings SET order_url='$correctUrl'");
    } else {
        mysqli_query($conn, "INSERT INTO qr_settings (order_url, station_name) VALUES ('$correctUrl', 'AAJ AQUA')");
    }
    echo "<p style='color: green;'>URL fixed to: $correctUrl</p>";
    echo "<p><a href='pages/qrmanage.php'>Go to QR Management</a></p>";
} else {
    echo "<h3 style='color: green;'>URL looks correct!</h3>";
    echo "<p><a href='pages/qrmanage.php'>Go to QR Management</a></p>";
}
?>
