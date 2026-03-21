<?php
$pageTitle  = 'Analytics';
$activePage = 'analytics';
$rootPath   = '../';
require_once '../includes/header.php';

// Monthly revenue (current year, completed orders)
$monthlyRev = array_fill(0, 12, 0);
$res = mysqli_query($conn, "SELECT MONTH(created_at) m, SUM(total_amount) rev FROM orders WHERE YEAR(created_at)=YEAR(CURDATE()) AND status='Completed' GROUP BY m");
while ($r = mysqli_fetch_assoc($res)) $monthlyRev[$r['m']-1] = (float)$r['rev'];

// Product sales
$prodSales = [];
$res = mysqli_query($conn, "SELECT product_name, SUM(quantity) qty, SUM(total_amount) rev FROM orders WHERE status='Completed' GROUP BY product_name ORDER BY qty DESC LIMIT 6");
while ($r = mysqli_fetch_assoc($res)) $prodSales[] = $r;

// Top customers
$topCust = [];
$res = mysqli_query($conn, "SELECT customer_name, COUNT(*) orders, SUM(total_amount) rev FROM orders WHERE customer_name!='Walk-in' GROUP BY customer_name ORDER BY orders DESC LIMIT 6");
while ($r = mysqli_fetch_assoc($res)) $topCust[] = $r;

// Month KPIs
$mk  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(total_amount),0) rev, COUNT(*) cnt FROM orders WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())"));
$top = mysqli_fetch_assoc(mysqli_query($conn, "SELECT product_name, SUM(quantity) q FROM orders GROUP BY product_name ORDER BY q DESC LIMIT 1"));
?>

<div class="kpi-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
  <div class="kpi kpi-a"><span class="kpi-icon">💰</span><div class="kpi-label">Monthly Revenue</div><div class="kpi-value" style="font-size:26px;color:var(--aqua);">₱<?= number_format($mk['rev'],2) ?></div></div>
  <div class="kpi kpi-b"><span class="kpi-icon">📋</span><div class="kpi-label">Orders This Month</div><div class="kpi-value"><?= $mk['cnt'] ?></div></div>
  <div class="kpi kpi-c"><span class="kpi-icon">⭐</span><div class="kpi-label">Top Product</div><div class="kpi-value" style="font-size:16px;padding-top:4px;"><?= htmlspecialchars($top['product_name'] ?? 'N/A') ?></div></div>
</div>

<div class="an-grid">
  <div class="glass-card">
    <div class="sec-hdr"><div class="sec-title">Monthly Sales Revenue</div><span class="text-soft" style="font-size:12px;"><?= date('Y') ?> · Completed orders</span></div>
    <div class="chart-wrap"><canvas id="salesBar"></canvas></div>
  </div>
  <div class="glass-card">
    <div class="sec-hdr"><div class="sec-title">Sales by Product</div></div>
    <div class="chart-wrap"><canvas id="salesPie"></canvas></div>
  </div>
</div>

<div class="two-col">
  <div class="glass-card">
    <div class="sec-title" style="margin-bottom:16px;">🏆 Top Customers</div>
    <?php if (empty($topCust)): ?>
      <p class="text-soft" style="font-size:13px;">No customer data yet.</p>
    <?php else: ?>
    <table>
      <thead><tr><th>Customer</th><th>Orders</th><th>Revenue</th></tr></thead>
      <tbody>
      <?php foreach ($topCust as $c): ?>
      <tr><td class="tname"><?= htmlspecialchars($c['customer_name']) ?></td><td><?= $c['orders'] ?></td><td class="tprice">₱<?= number_format($c['rev'],2) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <div class="glass-card">
    <div class="sec-title" style="margin-bottom:16px;">⬇️ Download Reports</div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;background:rgba(14,165,233,.05);border-radius:12px;border:1px solid rgba(14,165,233,.1);">
        <div><div style="font-weight:700;font-size:13px;">Sales — This Month</div><div style="font-size:11px;color:var(--text-soft);">Transactions &amp; totals</div></div>
        <a href="../api/export_csv.php?type=sales" class="btn btn-aqua btn-sm">⬇ CSV</a>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;background:rgba(20,184,166,.05);border-radius:12px;border:1px solid rgba(20,184,166,.1);">
        <div><div style="font-weight:700;font-size:13px;">Inventory Report</div><div style="font-size:11px;color:var(--text-soft);">Stock levels &amp; alerts</div></div>
        <a href="../api/export_csv.php?type=inventory" class="btn btn-aqua btn-sm">⬇ CSV</a>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;background:rgba(245,158,11,.05);border-radius:12px;border:1px solid rgba(245,158,11,.1);">
        <div><div style="font-weight:700;font-size:13px;">Full Order History</div><div style="font-size:11px;color:var(--text-soft);">All-time records</div></div>
        <a href="../api/export_csv.php?type=orders_all" class="btn btn-aqua btn-sm">⬇ CSV</a>
      </div>
    </div>
  </div>
</div>

<?php
$jsMonthlyRev = json_encode(array_values($monthlyRev));
$jsProdNames  = json_encode(array_column($prodSales, 'product_name'));
$jsProdQtys   = json_encode(array_column($prodSales, 'qty'));
$extraJs = <<<JS
<script>
document.addEventListener('DOMContentLoaded', () => {
  const rev    = $jsMonthlyRev;
  const pNames = $jsProdNames;
  const pQtys  = $jsProdQtys;

  new Chart(document.getElementById('salesBar'), {
    type:'bar',
    data:{labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],datasets:[{data:rev,backgroundColor:'rgba(14,165,233,.65)',borderRadius:6,borderColor:'#0ea5e9',borderWidth:1.5}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(14,165,233,.07)'},ticks:{color:'#7a9cbb',font:{size:11}}},y:{grid:{color:'rgba(14,165,233,.07)'},ticks:{color:'#7a9cbb',font:{size:11},callback:v=>'₱'+(v/1000).toFixed(0)+'k'}}}}
  });

  if (pNames.length > 0) {
    new Chart(document.getElementById('salesPie'), {
      type:'doughnut',
      data:{labels:pNames,datasets:[{data:pQtys,backgroundColor:['#0ea5e9','#14b8a6','#f97316','#f43f5e','#8b5cf6','#f59e0b'],borderColor:'white',borderWidth:3,hoverOffset:8}]},
      options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{color:'#3d5a7a',font:{size:11},padding:14,boxWidth:12}}}}
    });
  }
});
</script>
JS;
?>
<?php require_once '../includes/footer.php'; ?>
