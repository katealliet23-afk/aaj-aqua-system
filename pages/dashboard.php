<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
$rootPath   = '../';
require_once '../includes/header.php';

// ── KPI DATA ──
$kpi = [];
foreach (['Pending'=>'pending','In Process'=>'process','Out for Delivery'=>'delivery'] as $st=>$key) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE status='$st'"));
    $kpi[$key] = (int)$r['c'];
}
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE DATE(created_at)=CURDATE()"));
$kpi['today'] = (int)$r['c'];
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(total_amount),0) rev FROM orders WHERE DATE(created_at)=CURDATE() AND status='Completed'"));
$todayRev = (float)$r['rev'];

// ── INVENTORY BARS ──
$invItems = [];
$res = mysqli_query($conn, "SELECT product_name,quantity,reorder_level FROM inventory ORDER BY quantity DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) $invItems[] = $row;

// ── PENDING ORDERS TABLE ──
$pending = [];
$res = mysqli_query($conn, "SELECT * FROM orders WHERE status='Pending' ORDER BY created_at ASC LIMIT 5");
while ($row = mysqli_fetch_assoc($res)) $pending[] = $row;

// ── RECENT ORDERS ──
$recent = [];
$res = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 6");
while ($row = mysqli_fetch_assoc($res)) $recent[] = $row;

// ── QR URL ──
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
        $path  = ($parts['path'] ?? '/customer/order.php') . (isset($parts['query']) ? '?'.$parts['query'] : '');
        if (strpos($path, '/customer/order.php') === false) {
            return $url;
        }
    } else {
        $path = '/' . ltrim($url, '/');
    }
    
    // For QR codes, use a public URL that works from any network
    // Replace this with your actual domain or public IP
    $publicUrl = 'http://your-domain.com/aaj-aqua-v3';
    
    // If you have a public domain, use it. Otherwise, fallback to localhost for testing
    if (strpos($publicUrl, 'your-domain.com') !== false) {
        // Fallback to localhost for development
        return getAppBaseUrl() . $path;
    }
    
    return $publicUrl . $path;
}

$qrRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT order_url FROM qr_settings LIMIT 1"));
$qrUrl = resolveOrderUrl($qrRow['order_url'] ?? '/customer/order.php');

// ── HOURLY CHART DATA ──
$hourly = [];
$hours  = [4,8,12,16,20,22];
foreach ($hours as $h) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM orders WHERE HOUR(created_at)<=$h AND DATE(created_at)=CURDATE()"));
    $hourly[] = (int)$r['c'];
}
?>

<!-- KPI CARDS -->
<div class="kpi-grid">
  <div class="kpi kpi-a">
    <span class="kpi-icon">🛒</span>
    <div class="kpi-label">Pending Orders</div>
    <div class="kpi-value"><?= $kpi['pending'] ?></div>
    <div class="kpi-sub">Awaiting processing</div>
  </div>
  <div class="kpi kpi-b">
    <span class="kpi-icon">🚚</span>
    <div class="kpi-label">Out for Delivery</div>
    <div class="kpi-value"><?= $kpi['delivery'] ?></div>
    <div class="kpi-sub">On the road</div>
  </div>
  <div class="kpi kpi-c">
    <span class="kpi-icon">💰</span>
    <div class="kpi-label">Today's Revenue</div>
    <div class="kpi-value" style="font-size:28px;">₱<?= number_format($todayRev) ?></div>
    <div class="kpi-sub">Completed orders</div>
  </div>
  <div class="kpi kpi-d">
    <span class="kpi-icon">📋</span>
    <div class="kpi-label">Total Orders Today</div>
    <div class="kpi-value"><?= $kpi['today'] ?></div>
    <div class="kpi-sub">All statuses</div>
  </div>
</div>

<div class="dash-grid">
  <!-- LEFT -->
  <div>
    <!-- Inventory Status -->
    <div class="glass-card mb">
      <div class="sec-hdr">
        <div class="sec-title">💧 Inventory Status</div>
        <a href="inventory.php" class="sec-link">View all →</a>
      </div>
      <?php
      $colors = ['#0ea5e9','#14b8a6','#f97316','#f43f5e','#8b5cf6'];
      foreach ($invItems as $i => $it):
        $low = $it['quantity'] <= $it['reorder_level'];
        $pct = min(100, $it['quantity']);
        $col = $low ? '#f43f5e' : $colors[$i % count($colors)];
      ?>
      <div class="inv-item">
        <div class="inv-label" title="<?= htmlspecialchars($it['product_name']) ?>" style="<?= $low?'color:#f43f5e':'' ?>"><?= htmlspecialchars($it['product_name']) ?></div>
        <div class="inv-track"><div class="inv-bar" style="width:<?= $pct ?>%;background:<?= $col ?>;"></div></div>
        <div class="inv-count" style="<?= $low?'color:#f43f5e':'' ?>"><?= $it['quantity']>99?'99+':$it['quantity'] ?></div>
      </div>
      <?php endforeach; ?>
      <div style="height:100px;margin-top:12px;"><canvas id="invMini"></canvas></div>
    </div>

    <!-- Pending Refill Orders -->
    <div class="glass-card mb">
      <div class="sec-hdr">
        <div class="sec-title">⏳ Pending Refill Orders</div>
        <a href="orders.php" class="sec-link">Process all →</a>
      </div>
      <?php if (empty($pending)): ?>
        <p style="color:var(--text-soft);font-size:13px;text-align:center;padding:20px;">No pending orders 🎉</p>
      <?php else: ?>
      <div class="tbl-container" style="border:none;box-shadow:none;background:transparent;backdrop-filter:none;">
        <table>
          <thead><tr><th>Customer</th><th>Product</th><th>Qty</th><th>Time</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($pending as $o): ?>
          <tr>
            <td class="tname"><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= htmlspecialchars(substr($o['product_name'],0,22)) ?></td>
            <td><?= $o['quantity'] ?></td>
            <td style="font-size:12px;color:var(--text-soft);"><?= date('h:i A', strtotime($o['created_at'])) ?></td>
            <td>
              <form method="POST" action="../api/update_status.php" style="display:inline;">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <input type="hidden" name="status"   value="In Process">
                <input type="hidden" name="redirect"  value="../pages/dashboard.php">
                <button type="submit" class="btn btn-aqua btn-sm">Go ▶</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <!-- Order Summary Chart -->
    <div class="glass-card">
      <div class="sec-hdr"><div class="sec-title">📊 Order Summary</div><span class="text-soft" style="font-size:12px;">Today</span></div>
      <div style="height:120px;"><canvas id="orderLine"></canvas></div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="dash-right">
    <!-- QR Card -->
    <div class="qr-wrap">
      <div class="qr-title">Customer QR Code</div>
      <div class="qr-sub">Customers scan to place orders from their phone</div>
      <div class="qr-box" id="qrContainer"></div>
      <div class="qr-url-badge">🔗 <span><?= htmlspecialchars($qrUrl) ?></span></div>
      <div style="display:flex;gap:8px;">
        <button class="btn-qr" onclick="downloadQRCode('qrContainer', 'aaj-aqua-qr-code.png')" style="flex:1;">💾 Download</button>
        <button class="btn-qr" onclick="openModal('qrSet')" style="flex:1;">⚙ Manage</button>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="glass-card" style="flex:1;">
      <div class="sec-hdr">
        <div class="sec-title">🕒 Recent Activity</div>
        <a href="orders.php" class="sec-link">See all →</a>
      </div>
      <?php
      $dotColors = ['Completed'=>'#14b8a6','Pending'=>'#f59e0b','In Process'=>'#8b5cf6','Out for Delivery'=>'#0ea5e9','Cancelled'=>'#f43f5e'];
      $badgeCls  = ['Completed'=>'b-ok','Pending'=>'b-warn','In Process'=>'b-proc','Out for Delivery'=>'b-info','Cancelled'=>'b-del'];
      foreach ($recent as $o):
        $dc = $dotColors[$o['status']] ?? '#ccc';
        $bc = $badgeCls[$o['status']]  ?? 'b-can';
      ?>
      <div class="activity-item">
        <div class="ai-dot" style="background:<?= $dc ?>;box-shadow:0 0 6px <?= $dc ?>44;"></div>
        <div class="ai-info">
          <div class="ai-name"><?= htmlspecialchars($o['customer_name']) ?></div>
          <div class="ai-meta"><?= htmlspecialchars(substr($o['product_name'],0,28)) ?> · ₱<?= number_format($o['total_amount'],2) ?></div>
        </div>
        <span class="badge <?= $bc ?>"><?= $o['status'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- QR MODAL -->
<div class="modal-ov" id="modal-qrSet">
  <div class="modal">
    <div class="m-hdr"><div class="m-title">📲 QR Code Settings</div><button class="m-close" onclick="closeModal('qrSet')">✕</button></div>
    <form method="POST" action="../api/update_qr.php">
      <div class="field" style="margin-bottom:16px;">
        <label>Customer Order URL</label>
        <input type="url" name="order_url" value="<?= htmlspecialchars($qrUrl) ?>" required />
        <small>Customers land here when they scan the QR</small>
      </div>
      <div class="info-chip" style="margin-bottom:18px;">💡 When customers scan the QR code, they are directed to <b>customer/order.php</b> — a separate public page with no admin access.</div>
      <input type="hidden" name="redirect" value="../pages/dashboard.php">
      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-aqua">✓ Save & Update QR</button>
        <button type="button" class="btn btn-ghost" onclick="closeModal('qrSet')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<?php
$invNames    = array_column($invItems, 'product_name');
$invQtys     = array_column($invItems, 'quantity');
$jsQrUrl     = json_encode($qrUrl);
$jsHourly    = json_encode(array_values($hourly));
$jsInvLabels = json_encode(array_values(array_map(fn($n)=>explode(' ',$n)[0], $invNames)));
$jsInvQtys   = json_encode(array_values($invQtys));
$extraJs = <<<JS
<script>
window.QR_URL  = $jsQrUrl;
const hourly   = $jsHourly;
const invLabels = $jsInvLabels;
const invQtys   = $jsInvQtys;

document.addEventListener('DOMContentLoaded', () => {
  generateQRCode('qrContainer', window.QR_URL, 160);

  new Chart(document.getElementById('invMini'), {
    type:'bar',
    data:{labels:invLabels,datasets:[{data:invQtys,backgroundColor:['rgba(14,165,233,.7)','rgba(20,184,166,.7)','rgba(249,115,22,.7)','rgba(244,63,94,.7)','rgba(139,92,246,.7)'],borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:'#7a9cbb',font:{size:10}}},y:{display:false}}}
  });

  new Chart(document.getElementById('orderLine'), {
    type:'line',
    data:{labels:['4am','8am','12pm','4pm','8pm','10pm'],datasets:[{data:hourly,borderColor:'#0ea5e9',backgroundColor:'rgba(14,165,233,.08)',fill:true,tension:.5,pointRadius:4,pointBackgroundColor:'#0ea5e9',pointBorderColor:'white',pointBorderWidth:2}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{color:'#7a9cbb',font:{size:10}}},y:{display:false}}}
  });
});
</script>
JS;
?>
<?php require_once '../includes/footer.php'; ?>
