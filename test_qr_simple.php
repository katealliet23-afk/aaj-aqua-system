<?php
require_once 'includes/config.php';
require_once 'includes/db_cloud.php';

// Test QR generation
$qr_url = getPublicOrderUrl();
echo "<h1>QR Code Test</h1>";
echo "<p>QR URL: " . $qr_url . "</p>";
echo "<p>Scan this QR code:</p>";

// Generate QR code
echo '<div id="qrTestContainer"></div>';
echo '<script src="js/qrcode.min.js"></script>';
echo '<script>
new QRCode(document.getElementById("qrTestContainer"), {
    text: "' . $qr_url . '",
    width: 200,
    height: 200,
    colorDark: "#0c4a6e",
    colorLight: "#ffffff",
    correctLevel: QRCode.CorrectLevel.H
});
</script>';

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✅ Database connected to Railway</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
}
?>
