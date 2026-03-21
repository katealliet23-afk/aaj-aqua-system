<?php
// Test QR code generation with debug info
session_start();
require_once 'includes/auth.php';
requireLogin('../');
require_once 'includes/db.php';

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
        if (strpos($path, '/customer/order.php') === false) {
            return $url;
        }
    } else {
        $path = '/' . ltrim($url, '/');
    }
    return getAppBaseUrl() . $path;
}

// Get QR settings
$result = mysqli_query($conn, "SELECT * FROM qr_settings LIMIT 1");
if (!$result) {
    die("Error: " . mysqli_error($conn));
}

$qrRow = mysqli_fetch_assoc($result);
$rawOrderUrl = $qrRow['order_url'] ?? '/customer/order.php';
$qrUrl = resolveOrderUrl($rawOrderUrl);
$qrName = $qrRow['station_name'] ?? 'AAJ AQUA';

echo "<h2>QR Debug Info</h2>";
echo "<pre>";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n\n";
echo "getAppBaseUrl(): " . getAppBaseUrl() . "\n";
echo "Raw Order URL from DB: " . htmlspecialchars($rawOrderUrl) . "\n";
echo "Resolved QR URL: " . htmlspecialchars($qrUrl) . "\n";
echo "Station Name: " . htmlspecialchars($qrName) . "\n";
echo "\n";
echo "JSON for JS: " . json_encode($qrUrl) . "\n";
echo "</pre>";

echo "<h3>QR Code Test</h3>";
echo '<div id="qrTest" style="width:220px;height:220px;border:2px solid red;margin:20px auto;"></div>';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>';
echo '<script>
console.log("QRCode available:", typeof QRCode);
if (typeof QRCode !== "undefined") {
    new QRCode(document.getElementById("qrTest"), {
        text: ' . json_encode($qrUrl) . ',
        width: 220,
        height: 220,
        colorDark: "#0c4a6e",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    console.log("QR code generated");
} else {
    console.error("QRCode library not available");
    document.getElementById("qrTest").innerHTML = "QRCode library not loaded";
}
</script>';
