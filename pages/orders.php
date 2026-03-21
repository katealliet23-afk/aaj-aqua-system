<?php
$pageTitle  = 'Orders';
$activePage = 'orders';
$rootPath   = '../';
require_once '../includes/header.php';

// Products for dropdown
$products = [];
$res = mysqli_query($conn, "SELECT id,product_name,unit_price FROM inventory ORDER BY product_name");
while ($r = mysqli_fetch_assoc($res)) $products[] = $r;

// Status filter
$sf    = clean($conn, $_GET['status'] ?? '');
$where = $sf ? "WHERE status='$sf'" : '';
$orders = [];
$res = mysqli_query($conn, "SELECT * FROM orders $where ORDER BY created_at DESC");
while ($r = mysqli_fetch_assoc($res)) $orders[] = $r;

$statusCycle = ['Pending'=>'In Process','In Process'=>'Out for Delivery','Out for Delivery'=>'Completed'];
$badgeCls    = ['Completed'=>'b-ok','Pending'=>'b-warn','In Process'=>'b-proc','Out for Delivery'=>'b-info','Cancelled'=>'b-del'];
$typeCls     = ['Delivery'=>'b-type-d','Walk-in'=>'b-type-w'];
?>

<!-- New Order Form -->
<div class="glass-card mb">
  <div class="sec-title" style="margin-bottom:20px;">🛒 New Order</div>
  <form method="POST" action="../api/create_order.php">
    <div class="form-grid">
      <div class="field"><label>Order Type</label><select name="order_type"><option>Walk-in</option><option>Delivery</option></select></div>
      <div class="field"><label>Customer Name</label><input type="text" name="customer_name" placeholder="Name or Walk-in" /></div>
      <div class="field"><label>Contact #</label><input type="text" name="contact_number" placeholder="09XX-XXX-XXXX" /></div>
    </div>
    <div class="form-grid">
      <div class="field">
        <label>Product</label>
        <select name="product_id" id="ordProd" onchange="updateOrdTotal()">
          <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-price="<?= $p['unit_price'] ?>">
            <?= htmlspecialchars($p['product_name']) ?> — ₱<?= number_format($p['unit_price'],2) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Quantity</label><input type="number" name="quantity" id="ordQty" value="1" min="1" oninput="updateOrdTotal()" /></div>
      <div class="field"><label>Delivery Address</label><input type="text" name="delivery_address" placeholder="For delivery orders" /></div>
    </div>
    <div class="order-total-bar">
      <span class="ot-label">Order Total</span>
      <span class="ot-value" id="ordTotalOut">₱0.00</span>
    </div>
    <div style="display:flex;gap:12px;">
      <button type="submit" class="btn btn-aqua">✓ Place Order</button>
      <button type="reset"  class="btn btn-ghost">✕ Clear</button>
    </div>
    <input type="hidden" name="redirect" value="../pages/orders.php">
  </form>
</div>

<!-- Filter + Table -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
  <div class="sec-title">All Orders (<?= count($orders) ?>)</div>
  <div class="filter-pills">
    <?php
    $sts = [''=>'All','Pending'=>'Pending','In Process'=>'In Process','Out for Delivery'=>'Out for Del.','Completed'=>'Completed'];
    foreach ($sts as $val => $label):
    ?>
    <a href="orders.php<?= $val ? '?status='.urlencode($val) : '' ?>" class="pill <?= $sf===$val?'active':'' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="tbl-container">
  <table>
    <thead><tr><th>Order #</th><th>Type</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($orders)): ?>
      <tr><td colspan="9" style="text-align:center;color:var(--text-soft);padding:28px;">No orders found.</td></tr>
    <?php else: ?>
    <?php foreach ($orders as $o):
      $next = $statusCycle[$o['status']] ?? null;
      $bc   = $badgeCls[$o['status']] ?? 'b-can';
      $tc   = $typeCls[$o['order_type']] ?? 'b-info';
    ?>
    <tr>
      <td class="tnum"><?= htmlspecialchars($o['order_number']) ?></td>
      <td><span class="badge <?= $tc ?>"><?= $o['order_type'] ?></span></td>
      <td class="tname"><?= htmlspecialchars($o['customer_name']) ?></td>
      <td><?= htmlspecialchars($o['product_name']) ?></td>
      <td><?= $o['quantity'] ?></td>
      <td class="tprice">₱<?= number_format($o['total_amount'],2) ?></td>
      <td><span class="badge <?= $bc ?>"><?= $o['status'] ?></span></td>
      <td style="font-size:12px;color:var(--text-soft);"><?= date('M d, H:i', strtotime($o['created_at'])) ?></td>
      <td style="white-space:nowrap;">
        <?php if ($next): ?>
        <form method="POST" action="../api/update_status.php" style="display:inline;">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input type="hidden" name="status"   value="<?= htmlspecialchars($next) ?>">
          <input type="hidden" name="redirect"  value="../pages/orders.php<?= $sf ? '?status='.urlencode($sf) : '' ?>">
          <button type="submit" class="btn btn-ghost btn-sm">▶ <?= explode(' ',$next)[0] ?></button>
        </form>
        <?php endif; ?>
        <form method="POST" action="../api/delete_order.php" style="display:inline;" onsubmit="return confirm('Delete this order?')">
          <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
          <input type="hidden" name="redirect"  value="../pages/orders.php">
          <button type="submit" class="btn btn-danger btn-sm" style="margin-left:5px;">✕</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
$firstPrice = !empty($products) ? $products[0]['unit_price'] : 0;
$extraJs = <<<JS
<script>
function updateOrdTotal() {
  const sel   = document.getElementById('ordProd');
  const price = parseFloat(sel?.options[sel.selectedIndex]?.dataset.price || 0);
  const qty   = parseInt(document.getElementById('ordQty')?.value || 0);
  const out   = document.getElementById('ordTotalOut');
  if (out) out.textContent = '₱' + (price * qty).toFixed(2);
}
document.addEventListener('DOMContentLoaded', updateOrdTotal);
</script>
JS;
?>
<?php require_once '../includes/footer.php'; ?>
