<?php
$pageTitle  = 'QR Management';
$activePage = 'qrmanage';
$rootPath   = '../';
require_once '../includes/header.php';
require_once '../includes/config.php';

function resolveOrderUrl($url) {
    $url = trim($url);
    if ($url === '') {
        return getPublicOrderUrl();
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
        return getPublicOrderUrl($path);
    } else {
        $path = '/' . ltrim($url, '/');
        return getPublicOrderUrl($path);
    }
}

$qrRow      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM qr_settings LIMIT 1"));
$rawOrderUrl= $qrRow['order_url'] ?? '/customer/order.php';
$qrUrl      = resolveOrderUrl($rawOrderUrl);
$qrName     = $qrRow['station_name'] ?? 'AAJ AQUA Clear Refilling Station';
?>

<div class="qrm-grid">
  <!-- Config Panel -->
  <div class="glass-card">
    <div class="sec-title" style="margin-bottom:20px;">⚙️ Configure QR Code</div>
    <form method="POST" action="../api/update_qr.php">
      <div class="field" style="margin-bottom:16px;">
        <label>Customer Order URL</label>
        <input type="text" name="order_url" value="<?= htmlspecialchars($rawOrderUrl) ?>" required />
        <small>Relative (preferred): /customer/order.php or absolute: https://yourdomain/customer/order.php</small>
      </div>
      <div class="field" style="margin-bottom:16px;">
        <label>Mobile URL (for QR scanning)</label>
        <input type="text" name="mobile_url" id="mobileUrl" value="<?= htmlspecialchars($qrUrl) ?>" />
        <small>Public URL that works from any network (e.g., https://yourdomain.com/aaj-aqua-v3/customer/order.php)</small>
      </div>
      <div class="field" style="margin-bottom:16px;">
        <label>Station Name (shown on order page)</label>
        <input type="text" name="station_name" value="<?= htmlspecialchars($qrName) ?>" />
      </div>
      <div class="field" style="margin-bottom:20px;">
        <label>QR Code Size</label>
        <select id="qrSzSel">
          <option value="160">Small (160px)</option>
          <option value="220" selected>Medium (220px)</option>
          <option value="280">Large (280px)</option>
        </select>
      </div>
      <input type="hidden" name="redirect" value="../pages/qrmanage.php">
      <button type="submit" class="btn btn-aqua btn-full" style="margin-bottom:12px;">📲 Save & Regenerate QR</button>
    </form>
    <button class="btn btn-ghost btn-full" onclick="downloadQRCode('qrBig', 'aaj-aqua-qr-code.png')" style="margin-bottom:12px;">💾 Download QR Code</button>
    <button class="btn btn-ghost btn-full" onclick="window.print()">🖨️ Print QR Code</button>
    <div class="divider"></div>
    <div class="info-chip">
      <b>📌 How it works</b><br>
      1. Customer scans the QR code<br>
      2. A mobile order form opens on their phone<br>
      3. They fill in name, address &amp; product<br>
      4. Order appears in your dashboard instantly
    </div>
  </div>

  <!-- Preview Panel -->
  <div class="glass-card" style="text-align:center;">
    <div class="sec-title" style="margin-bottom:20px;">Live Preview</div>
    <div id="qrBig" style="min-height:250px;">
      <div id="qrBigStatus" style="position:absolute;top:50%;left:50%;transform:translate(-50%, -50%);font-size:14px;color:#999;">
        Loading QR code...
      </div>
    </div>
    <p style="font-size:14px;font-weight:700;color:var(--text-dark);margin-bottom:4px;" id="qrBigName"><?= htmlspecialchars($qrName) ?></p>
    <p style="font-size:12px;color:var(--text-soft);margin-bottom:14px;">Scan to order water</p>
    <div style="padding:10px 14px;background:rgba(14,165,233,.06);border-radius:10px;font-size:11px;color:var(--aqua);word-break:break-all;" id="qrBigUrl"><?= htmlspecialchars($qrUrl) ?></div>

    <div class="divider"></div>
    <div style="padding:14px;background:var(--canvas);border-radius:12px;text-align:center;">
      <p style="font-size:12px;color:var(--text-mid);font-weight:600;margin-bottom:6px;">Share order link directly:</p>
      <a href="<?= htmlspecialchars($qrUrl) ?>" target="_blank" style="font-size:12px;color:var(--aqua);word-break:break-all;"><?= htmlspecialchars($qrUrl) ?></a>
    </div>
  </div>
</div>

<?php $extraJs = <<<JS
<script>
window.QR_URL = document.getElementById('mobileUrl').value || '<?= $qrUrl ?>';

function initializeQrBig() {
    if (typeof generateQRCode === 'function' && typeof QRCode !== 'undefined') {
        generateQRCode('qrBig', window.QR_URL, 220);
        console.log('[qrManage] QR code generated with URL:', window.QR_URL);
    } else {
        console.log('[qrManage] Waiting for generateQRCode/QRCode to be available...');
        setTimeout(initializeQrBig, 25);
    }
}

initializeQrBig();

document.getElementById('qrSzSel')?.addEventListener('change', function() {
    if (typeof generateQRCode === 'function') {
        generateQRCode('qrBig', window.QR_URL, parseInt(this.value));
    }
});

document.getElementById('mobileUrl')?.addEventListener('input', function() {
    window.QR_URL = this.value;
    if (typeof generateQRCode === 'function') {
        generateQRCode('qrBig', window.QR_URL, 220);
    }
});
</script>
JS; ?>
<?php require_once '../includes/footer.php'; ?>
