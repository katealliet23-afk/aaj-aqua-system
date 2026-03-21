<?php
// customer/order.php
// ─────────────────────────────────────────────────────────────
//  PUBLIC PAGE — No login required.
//  Customers land here after scanning the QR code.
//  This file lives in aaj-aqua-v2/customer/order.php
// ─────────────────────────────────────────────────────────────
require_once '../includes/db.php';   // DB only, NO auth

// Fetch station settings
$qrRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM qr_settings LIMIT 1"));
$stationName = $qrRow['station_name'] ?? 'AAJ AQUA Clear Refilling Station';

// Fetch available products (in stock only)
$products = [];
$res = mysqli_query($conn, "SELECT id, product_name, unit_price FROM inventory WHERE quantity > 0 ORDER BY product_name");
while ($r = mysqli_fetch_assoc($res)) $products[] = $r;

// Handle form submission
$success  = false;
$orderNum = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = clean($conn, $_POST['customer_name']    ?? '');
    $phone = clean($conn, $_POST['contact_number']   ?? '');
    $addr  = clean($conn, $_POST['delivery_address'] ?? '');
    $pid   = (int)($_POST['product_id'] ?? 0);
    $qty   = (int)($_POST['quantity']   ?? 1);

    if (!$name || !$phone || !$addr || !$pid || $qty < 1) {
        $error = 'Please fill in all required fields.';
    } else {
        $pr = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT product_name, unit_price FROM inventory WHERE id=$pid AND quantity > 0"));
        if (!$pr) {
            $error = 'Selected product is currently out of stock. Please choose another.';
        } else {
            $total = (float)$pr['unit_price'] * $qty;
            $onum  = generateOrderNumber($conn);
            $pname = clean($conn, $pr['product_name']);
            $price = (float)$pr['unit_price'];
            $sql   = "INSERT INTO orders
                        (order_number, order_type, customer_name, contact_number,
                         delivery_address, product_id, product_name, quantity,
                         unit_price, total_amount, status, source)
                      VALUES
                        ('$onum','Delivery','$name','$phone','$addr',
                         $pid,'$pname',$qty,$price,$total,'Pending','qr_scan')";
            if (mysqli_query($conn, $sql)) {
                $success  = true;
                $orderNum = $onum;
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title><?= htmlspecialchars($stationName) ?> — Order Water</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════
   CUSTOMER ORDER PAGE — Standalone
   No admin UI, mobile-first design
═══════════════════════════════════════ */
:root {
  --aqua:      #0ea5e9;
  --aqua-deep: #0369a1;
  --teal:      #14b8a6;
  --rose:      #f43f5e;
  --text:      #0c1b2e;
  --text-mid:  #3d5a7a;
  --text-soft: #7a9cbb;
  --border:    rgba(14,165,233,.18);
  --f-head:    'Playfair Display', serif;
  --f-body:    'Nunito', sans-serif;
}

* { margin:0; padding:0; box-sizing:border-box; }
html { scroll-behavior:smooth; }

body {
  font-family: var(--f-body);
  min-height: 100vh;
  background: linear-gradient(160deg, #e0f2fe 0%, #f0fdf4 50%, #fef9c3 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 0 40px;
  position: relative;
  overflow-x: hidden;
}

/* ── BACKGROUND BLOBS ── */
.blob {
  position: fixed;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  z-index: 0;
}
.blob-1 { width:380px; height:380px; background:rgba(14,165,233,.18);  top:-80px;  left:-100px; animation:bf 12s ease-in-out infinite; }
.blob-2 { width:320px; height:320px; background:rgba(20,184,166,.16);  bottom:0;   right:-60px; animation:bf 14s ease-in-out infinite reverse; }
.blob-3 { width:220px; height:220px; background:rgba(249,115,22,.10);  top:40%;    right:10%;   animation:bf 10s ease-in-out infinite 4s; }
@keyframes bf { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(20px,-20px) scale(1.05)} }

/* ── HEADER HERO ── */
.hero {
  width: 100%;
  max-width: 520px;
  padding: 40px 24px 32px;
  text-align: center;
  position: relative;
  z-index: 1;
}
.hero-logo {
  width: 76px; height: 76px;
  background: linear-gradient(145deg, #0ea5e9, #14b8a6);
  border-radius: 24px;
  margin: 0 auto 18px;
  display: flex; align-items: center; justify-content: center;
  font-size: 34px;
  box-shadow: 0 12px 36px rgba(14,165,233,.42);
  animation: logoPulse 3s ease-in-out infinite;
}
@keyframes logoPulse {
  0%,100% { box-shadow:0 12px 36px rgba(14,165,233,.42); }
  50%      { box-shadow:0 18px 48px rgba(14,165,233,.65), 0 0 0 10px rgba(14,165,233,.08); }
}
.hero h1 {
  font-family: var(--f-head);
  font-size: 30px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 6px;
}
.hero-station {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(14,165,233,.1);
  border: 1px solid rgba(14,165,233,.2);
  border-radius: 20px;
  padding: 5px 16px;
  font-size: 12px; font-weight: 700;
  color: var(--aqua-deep);
  letter-spacing: .3px;
  margin-top: 10px;
}
.hero-station::before { content:'📍'; font-size:13px; }

/* ── STEPS INDICATOR ── */
.steps {
  display: flex; align-items: center; gap: 0;
  margin: 0 auto 28px;
  max-width: 300px;
  position: relative; z-index: 1;
}
.step {
  display: flex; flex-direction: column; align-items: center;
  flex: 1;
}
.step-dot {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; font-weight: 800;
  transition: all .3s;
  position: relative; z-index: 1;
}
.step-dot.done   { background:linear-gradient(135deg,#0ea5e9,#14b8a6); color:white; box-shadow:0 4px 12px rgba(14,165,233,.4); }
.step-dot.active { background:linear-gradient(135deg,#0ea5e9,#14b8a6); color:white; box-shadow:0 4px 16px rgba(14,165,233,.5); animation:stepPulse 1.5s infinite; }
.step-dot.idle   { background:rgba(255,255,255,.7); color:var(--text-soft); border:2px solid rgba(14,165,233,.2); }
@keyframes stepPulse { 0%,100%{box-shadow:0 4px 16px rgba(14,165,233,.5)} 50%{box-shadow:0 4px 24px rgba(14,165,233,.8)} }
.step-label { font-size:10px; font-weight:700; color:var(--text-soft); margin-top:5px; letter-spacing:.5px; text-transform:uppercase; }
.step-line { flex:1; height:2px; background:rgba(14,165,233,.15); margin:0 -2px; margin-bottom:22px; position:relative; }
.step-line.done { background:linear-gradient(90deg,#0ea5e9,#14b8a6); }

/* ── MAIN CARD ── */
.order-card {
  width: 100%;
  max-width: 520px;
  background: rgba(255,255,255,.88);
  backdrop-filter: blur(20px);
  border: 1.5px solid rgba(255,255,255,.85);
  border-radius: 28px;
  padding: 32px 28px;
  box-shadow: 0 20px 60px rgba(14,165,233,.16);
  position: relative; z-index: 1;
  margin: 0 16px;
}

/* ── SECTION LABEL ── */
.sec-label {
  font-size: 11px; font-weight: 800;
  letter-spacing: 1.2px; text-transform: uppercase;
  color: var(--aqua-deep);
  margin-bottom: 14px;
  display: flex; align-items: center; gap: 7px;
}
.sec-label::after { content:''; flex:1; height:1.5px; background:rgba(14,165,233,.1); border-radius:1px; }

/* ── FORM FIELDS ── */
.field { margin-bottom: 14px; }
.field label {
  display: block;
  font-size: 11px; font-weight: 800;
  letter-spacing: .8px; text-transform: uppercase;
  color: var(--text-mid); margin-bottom: 7px;
}
.field input, .field select {
  width: 100%; padding: 13px 16px;
  background: rgba(240,248,255,.8);
  border: 1.5px solid var(--border);
  border-radius: 14px;
  font-family: var(--f-body); font-size: 15px;
  color: var(--text); outline: none;
  transition: all .2s;
  -webkit-appearance: none;
}
.field input:focus, .field select:focus {
  border-color: var(--aqua);
  background: white;
  box-shadow: 0 0 0 4px rgba(14,165,233,.1);
}
.field input::placeholder { color: var(--text-soft); }

.row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* ── PRODUCT CARDS ── */
.product-grid {
  display: flex; flex-direction: column; gap: 10px;
  margin-bottom: 14px;
}
.prod-card {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 16px;
  background: rgba(240,248,255,.7);
  border: 2px solid transparent;
  border-radius: 14px; cursor: pointer;
  transition: all .2s;
  position: relative;
}
.prod-card:hover { border-color: rgba(14,165,233,.3); background: rgba(224,242,254,.8); }
.prod-card.selected {
  border-color: var(--aqua);
  background: rgba(224,242,254,.9);
  box-shadow: 0 4px 16px rgba(14,165,233,.2);
}
.prod-card input[type=radio] { display:none; }
.prod-icon {
  width: 44px; height: 44px; border-radius: 12px;
  background: linear-gradient(135deg,rgba(14,165,233,.12),rgba(20,184,166,.1));
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; flex-shrink: 0;
}
.prod-info { flex: 1; }
.prod-name  { font-size: 14px; font-weight: 700; color: var(--text); }
.prod-price { font-size: 13px; color: var(--aqua); font-weight: 800; margin-top: 2px; }
.prod-check {
  width: 22px; height: 22px; border-radius: 50%;
  border: 2px solid rgba(14,165,233,.25);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; transition: all .2s;
  font-size: 12px;
}
.prod-card.selected .prod-check {
  background: linear-gradient(135deg,#0ea5e9,#14b8a6);
  border-color: transparent; color: white;
}

/* ── QTY STEPPER ── */
.qty-wrap {
  display: flex; align-items: center; gap: 0;
  background: rgba(240,248,255,.8);
  border: 1.5px solid var(--border);
  border-radius: 14px; overflow: hidden;
  margin-bottom: 14px;
}
.qty-btn {
  width: 54px; height: 50px;
  background: none; border: none; cursor: pointer;
  font-size: 22px; color: var(--aqua); font-weight: 700;
  transition: background .15s;
  display: flex; align-items: center; justify-content: center;
}
.qty-btn:hover { background: rgba(14,165,233,.1); }
.qty-btn:active { background: rgba(14,165,233,.2); }
.qty-display {
  flex: 1; text-align: center;
  font-family: var(--f-head); font-size: 22px; font-weight: 700;
  color: var(--text);
  border-left: 1.5px solid var(--border);
  border-right: 1.5px solid var(--border);
  height: 50px; display: flex; align-items: center; justify-content: center;
}
input#qtyHidden { display:none; }

/* ── ORDER TOTAL ── */
.total-box {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  background: linear-gradient(135deg, rgba(14,165,233,.08), rgba(20,184,166,.06));
  border: 1.5px solid rgba(14,165,233,.15);
  border-radius: 16px; margin-bottom: 20px;
}
.total-label { font-size: 13px; font-weight: 600; color: var(--text-mid); }
.total-val   { font-family:var(--f-head); font-size:26px; font-weight:700; color:var(--aqua); }

/* ── SUBMIT BUTTON ── */
.btn-submit {
  width: 100%; padding: 17px;
  background: linear-gradient(135deg, #0ea5e9, #14b8a6);
  border: none; border-radius: 16px;
  color: white;
  font-family: var(--f-head); font-size: 17px; font-weight: 700;
  cursor: pointer; letter-spacing: .4px;
  box-shadow: 0 8px 28px rgba(14,165,233,.45);
  transition: transform .15s, box-shadow .2s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.btn-submit:hover  { transform:translateY(-2px); box-shadow:0 12px 36px rgba(14,165,233,.6); }
.btn-submit:active { transform:translateY(0); }
.btn-submit:disabled { opacity:.6; cursor:not-allowed; transform:none; }

/* ── ERROR ── */
.err-box {
  display: flex; align-items: flex-start; gap: 10px;
  background: #fee2e2; color: #991b1b;
  border: 1.5px solid rgba(244,63,94,.2);
  border-radius: 14px; padding: 13px 16px;
  font-size: 13.5px; font-weight: 600;
  margin-bottom: 18px;
}
.err-box .err-icon { font-size:18px; flex-shrink:0; }

/* ── SUCCESS STATE ── */
.success-wrap { text-align: center; padding: 16px 0 8px; }
.success-anim {
  width: 90px; height: 90px;
  background: linear-gradient(135deg,#d1fae5,#a7f3d0);
  border-radius: 50%; margin: 0 auto 22px;
  display: flex; align-items: center; justify-content: center;
  font-size: 46px;
  box-shadow: 0 8px 30px rgba(16,185,129,.3);
  animation: successBounce .5s cubic-bezier(.4,0,.2,1);
}
@keyframes successBounce { 0%{transform:scale(0) rotate(-20deg)} 70%{transform:scale(1.1) rotate(5deg)} 100%{transform:scale(1) rotate(0)} }
.success-wrap h2 { font-family:var(--f-head); font-size:26px; font-weight:700; color:#059669; margin-bottom:12px; }
.success-wrap p  { font-size:14px; color:var(--text-mid); line-height:1.8; }
.order-pill {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(14,165,233,.08);
  border: 1.5px solid rgba(14,165,233,.2);
  border-radius: 20px; padding: 8px 20px;
  margin: 16px auto;
  font-family: var(--f-head); font-size: 18px; font-weight: 700; color: var(--aqua);
}
.btn-again {
  display: inline-flex; align-items: center; gap: 8px;
  margin-top: 20px; padding: 13px 28px;
  background: white; border: 1.5px solid var(--border);
  border-radius: 14px; font-family: var(--f-body);
  font-size: 14px; font-weight: 700; color: var(--text-mid);
  cursor: pointer; transition: all .2s;
  text-decoration: none;
}
.btn-again:hover { border-color:var(--aqua); color:var(--aqua); }

/* ── FOOTER ── */
.co-footer {
  position: relative; z-index: 1;
  margin-top: 28px; text-align: center;
  font-size: 12px; color: var(--text-soft); font-weight: 600;
}
.co-footer span { color: var(--aqua); }

/* ── LOADING SPINNER ── */
.spinner {
  width: 18px; height: 18px; border: 2.5px solid rgba(255,255,255,.4);
  border-top-color: white; border-radius: 50%;
  animation: spin .7s linear infinite;
}
@keyframes spin { to{transform:rotate(360deg)} }

/* ── MOBILE TWEAKS ── */
@media (max-width: 420px) {
  .hero { padding: 28px 16px 24px; }
  .hero h1 { font-size: 26px; }
  .order-card { padding: 26px 20px; margin: 0 12px; }
}
</style>
</head>
<body>

<!-- Background blobs -->
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- Hero Header -->
<div class="hero">
  <div class="hero-logo">💧</div>
  <h1>Order Water</h1>
  <div class="hero-station"><?= htmlspecialchars($stationName) ?></div>
</div>

<!-- Step Indicator -->
<?php if (!$success): ?>
<div class="steps">
  <div class="step">
    <div class="step-dot active">1</div>
    <div class="step-label">Details</div>
  </div>
  <div class="step-line"></div>
  <div class="step">
    <div class="step-dot idle">2</div>
    <div class="step-label">Confirm</div>
  </div>
  <div class="step-line"></div>
  <div class="step">
    <div class="step-dot idle">3</div>
    <div class="step-label">Done</div>
  </div>
</div>
<?php else: ?>
<div class="steps">
  <div class="step"><div class="step-dot done">✓</div><div class="step-label">Details</div></div>
  <div class="step-line done"></div>
  <div class="step"><div class="step-dot done">✓</div><div class="step-label">Confirm</div></div>
  <div class="step-line done"></div>
  <div class="step"><div class="step-dot done">✓</div><div class="step-label">Done</div></div>
</div>
<?php endif; ?>

<!-- Main Card -->
<div class="order-card">

  <?php if ($success): ?>
  <!-- ── SUCCESS ── -->
  <div class="success-wrap">
    <div class="success-anim">✅</div>
    <h2>Order Received!</h2>
    <p>Thank you, your refill order has been placed successfully.<br>We'll contact you shortly to confirm delivery.</p>
    <div class="order-pill">📋 <?= htmlspecialchars($orderNum) ?></div>
    <p style="font-size:13px;color:var(--text-soft);">Please keep this order number for reference.</p>
    <a href="order.php" class="btn-again">🔄 Place Another Order</a>
  </div>

  <?php else: ?>
  <!-- ── ORDER FORM ── -->

  <?php if ($error): ?>
  <div class="err-box"><span class="err-icon">⚠️</span><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="order.php" id="orderForm">

    <!-- CONTACT INFO -->
    <div class="sec-label">👤 Your Information</div>
    <div class="row2">
      <div class="field">
        <label>Full Name *</label>
        <input type="text" name="customer_name"
               value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>"
               placeholder="e.g. Maria Santos" required />
      </div>
      <div class="field">
        <label>Contact # *</label>
        <input type="tel" name="contact_number"
               value="<?= htmlspecialchars($_POST['contact_number'] ?? '') ?>"
               placeholder="09XX-XXX-XXXX" required />
      </div>
    </div>

    <div class="field">
      <label>Delivery Address *</label>
      <input type="text" name="delivery_address"
             value="<?= htmlspecialchars($_POST['delivery_address'] ?? '') ?>"
             placeholder="House #, Street, Barangay, City" required />
    </div>

    <!-- PRODUCT SELECT -->
    <div class="sec-label" style="margin-top:6px;">💧 Choose Product</div>

    <?php if (empty($products)): ?>
    <div class="err-box"><span class="err-icon">😔</span>No products are currently available. Please check back later.</div>
    <?php else: ?>

    <div class="product-grid" id="productGrid">
      <?php
      $icons = ['5-Gal'=>'🪣','Round'=>'🧴','Slim'=>'💧','Mineral'=>'🫙','10-Gal'=>'🏺','Dispenser'=>'🚰'];
      foreach ($products as $i => $p):
        $selected = ($_POST['product_id'] ?? '') == $p['id'];
        $firstWord = explode(' ', $p['product_name'])[0];
        $icon = '💧';
        foreach ($icons as $k => $v) { if (stripos($p['product_name'], $k) !== false) { $icon = $v; break; } }
      ?>
      <label class="prod-card <?= $selected ? 'selected' : ($i===0?'selected':'') ?>" id="card-<?= $p['id'] ?>">
        <input type="radio" name="product_id" value="<?= $p['id'] ?>"
               data-price="<?= $p['unit_price'] ?>"
               <?= $selected || $i===0 ? 'checked' : '' ?>
               onchange="selectProduct(this, <?= $p['id'] ?>)" />
        <div class="prod-icon"><?= $icon ?></div>
        <div class="prod-info">
          <div class="prod-name"><?= htmlspecialchars($p['product_name']) ?></div>
          <div class="prod-price">₱<?= number_format($p['unit_price'], 2) ?> each</div>
        </div>
        <div class="prod-check" id="chk-<?= $p['id'] ?>"><?= ($selected || $i===0) ? '✓' : '' ?></div>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- QUANTITY STEPPER -->
    <div class="sec-label" style="margin-top:4px;">🔢 Quantity</div>
    <div class="qty-wrap">
      <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
      <div class="qty-display" id="qtyDisplay">1</div>
      <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
    </div>
    <input type="hidden" name="quantity" id="qtyHidden" value="<?= (int)($_POST['quantity'] ?? 1) ?>" />

    <!-- ORDER TOTAL -->
    <div class="total-box">
      <div class="total-label">Order Total</div>
      <div class="total-val" id="totalOut">₱0.00</div>
    </div>

    <!-- SUBMIT -->
    <button type="submit" class="btn-submit" id="submitBtn">
      <span>📦 Submit My Order</span>
    </button>

    <?php endif; ?>
  </form>

  <?php endif; ?>
</div>

<!-- Footer -->
<div class="co-footer">
  Powered by <span>AAJ AQUA</span> Station Management · <?= date('Y') ?>
</div>

<script>
// ── PRODUCT SELECTION ──────────────────────
let qty     = <?= (int)($_POST['quantity'] ?? 1) ?>;
let selPrice = 0;

// find initially selected price
document.addEventListener('DOMContentLoaded', () => {
  const checked = document.querySelector('input[name="product_id"]:checked');
  if (checked) { selPrice = parseFloat(checked.dataset.price || 0); }
  updateQtyDisplay();
  calcTotal();
});

function selectProduct(radio, id) {
  // deselect all
  document.querySelectorAll('.prod-card').forEach(c => c.classList.remove('selected'));
  document.querySelectorAll('.prod-check').forEach(c => c.textContent = '');
  // select clicked
  document.getElementById('card-' + id).classList.add('selected');
  document.getElementById('chk-'  + id).textContent = '✓';
  selPrice = parseFloat(radio.dataset.price || 0);
  calcTotal();
}

// ── QUANTITY STEPPER ───────────────────────
function changeQty(delta) {
  qty = Math.max(1, Math.min(99, qty + delta));
  updateQtyDisplay();
  calcTotal();
}
function updateQtyDisplay() {
  document.getElementById('qtyDisplay').textContent = qty;
  document.getElementById('qtyHidden').value = qty;
}

// ── TOTAL CALCULATOR ──────────────────────
function calcTotal() {
  const total = selPrice * qty;
  document.getElementById('totalOut').textContent = '₱' + total.toFixed(2);
}

// ── SUBMIT LOADING STATE ──────────────────
document.getElementById('orderForm')?.addEventListener('submit', function() {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spinner"></div><span>Placing order…</span>';
});
</script>

</body>
</html>
