<!DOCTYPE html>
<html>
<head>
    <title>QR Test</title>
    <script src="js/qrcode.min.js"></script>
</head>
<body>
    <h1>QR Code Test</h1>
    <div id="qr-test"></div>
    <script>
        try {
            new QRCode(document.getElementById("qr-test"), {
                text: "http://localhost/aaj-aqua-v3/customer/order.php",
                width: 220,
                height: 220,
                colorDark: "#0369a1",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            console.log("QR Code generated successfully");
        } catch (e) {
            console.error("QR Code generation failed:", e);
            document.getElementById("qr-test").innerHTML = "Error: " + e.message;
        }
    </script>
</body>
</html>
