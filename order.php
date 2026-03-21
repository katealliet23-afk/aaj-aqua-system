<?php
// order.php — Public customer order page (QR scan landing)
require_once 'includes/db.php';

$qrRow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM qr_settings LIMIT 1"));
$stationName = htmlspecialchars($qrRow['station_name'] ?? 'AAJ AQUA Clear Refilling Station');

$products = [];
$res = mysqli_query($conn,"SELECT id,product_name,unit_price FROM inventory WHERE quantity>0 ORDER BY product_name");
while ($r = mysqli_fetch_assoc($res)) $products[] = $r;

$success  = false;
$orderNum = '';
$error    = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = clean($conn,$_POST['customer_name']??'');
    $phone = clean($conn,$_POST['contact_number']??'');
    $addr  = clean($conn,$_POST['delivery_address']??'');
    $pid   = (int)($_POST['product_id']??0);
    $qty   = (int)($_POST['quantity']??1);

    if (!$name||!$phone||!$addr||!$pid||$qty<1) {
        $error = 'Please fill in all required fields.';
    } else {
        $pr = mysqli_fetch_assoc(mysqli_query($conn,"SELECT product_name,unit_price FROM inventory WHERE id=$pid AND quantity>0"));
        if (!$pr) { $error='Selected product is out of stock.'; }
        else {
            $total  = (float)$pr['unit_price'] * $qty;
            $onum   = generateOrderNumber($conn);
            $pname  = clean($conn,$pr['product_name']);
            $price  = $pr['unit_price'];
            $sql = "INSERT INTO orders (order_number,order_type,customer_name,contact_number,delivery_address,product_id,product_name,quantity,unit_price,total_amount,status,source)
                    VALUES ('$onum','Delivery','$name','$phone','$addr',$pid,'$pname',$qty,$price,$total,'Pending','qr_scan')";
            if (mysqli_query($conn,$sql)) { $success=true; $orderNum=$onum; }
            else $error = 'Could not place order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $stationName ?> — Order</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<style>
:root{--aqua:#0ea5e9;--teal:#14b8a6;--f-display:'Playfair Display',serif;--f-body:'Nunito',sans-serif;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--f-body);min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(145deg,#e0f2fe 0%,#f0fdf4 55%,#fef9c3 100%);padding:24px;overflow:hidden;position:relative;}
.d-blob{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;}
.d-blob:nth-child(1){width:400px;height:400px;background:rgba(14,165,233,.18);top:-100px;left:-80px;}
.d-blob:nth-child(2){width:350px;height:350px;background:rgba(20,184,166,.16);bottom:-80px;right:-60px;}
.card{position:relative;z-index:10;background:white;border-radius:28px;padding:40px 36px;max-width:460px;width:100%;box-shadow:0 30px 80px rgba(14,165,233,.2);}
.brand{text-align:center;margin-bottom:28px;}
.brand-icon{width:56px;height:56px;background:linear-gradient(145deg,#0ea5e9,#14b8a6);border-radius:18px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:26px;box-shadow:0 8px 24px rgba(14,165,233,.4);}
.brand h2{font-family:var(--f-display);font-size:22px;font-weight:700;color:#0c1b2e;}
.brand p{font-size:13px;color:#7a9cbb;margin-top:4px;}
.fg{margin-bottom:14px;}
.fg label{display:block;font-size:10px;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:#3d5a7a;margin-bottom:7px;}
.fg input,.fg select{width:100%;padding:11px 16px;background:#f8faff;border:1.5px solid rgba(14,165,233,.15);border-radius:12px;font-family:var(--f-body);font-size:14px;color:#0c1b2e;outline:none;transition:all .2s;}
.fg input:focus,.fg select:focus{border-color:var(--aqua);background:white;box-shadow:0 0 0 4px rgba(14,165,233,.08);}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.total-bar{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;background:linear-gradient(135deg,rgba(14,165,233,.08),rgba(20,184,166,.06));border:1.5px solid rgba(14,165,233,.14);border-radius:12px;margin-bottom:16px;}
.total-bar span:first-child{font-size:13px;font-weight:600;color:#3d5a7a;}
.total-val{font-family:var(--f-display);font-size:22px;font-weight:700;color:var(--aqua);}
.btn-sub{width:100%;padding:14px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);border:none;border-radius:12px;color:white;font-family:var(--f-display);font-size:15px;font-weight:700;cursor:pointer;box-shadow:0 6px 20px rgba(14,165,233,.4);transition:all .2s;}
.btn-sub:hover{transform:translateY(-1px);box-shadow:0 10px 28px rgba(14,165,233,.55);}
.err{background:#fee2e2;color:#991b1b;border:1.5px solid rgba(244,63,94,.2);border-radius:10px;padding:11px 16px;font-size:13px;font-weight:600;margin-bottom:14px;}
.success{text-align:center;padding:20px 0;}
.success .big{font-size:62px;margin-bottom:14px;animation:bounce .5s ease;}
@keyframes bounce{0%{transform:scale(0)}70%{transform:scale(1.15)}100%{transform:scale(1)}}
.success h3{font-family:var(--f-display);font-size:24px;font-weight:700;color:#059669;margin-bottom:10px;}
.success p{font-size:13.5px;color:#3d5a7a;line-height:1.8;}
.success .onum{color:var(--aqua);font-weight:800;}
.btn-again{display:inline-block;margin-top:18px;padding:10px 24px;background:white;border:1.5px solid rgba(14,165,233,.2);border-radius:10px;font-size:13px;font-weight:700;color:#3d5a7a;cursor:pointer;transition:all .2s;}
.btn-again:hover{border-color:var(--aqua);color:var(--aqua);}
</style>
</head>
<body>
<div class="d-blob"></div><div class="d-blob"></div>
<div class="card">
  <div class="brand">
    <div class="brand-icon">💧</div>
    <h2>Place Your Order</h2>
    <p><?= $stationName ?></p>
  </div>

  <?php if ($success): ?>
  <div class="success">
    <div class="big">✅</div>
    <h3>Order Received!</h3>
    <p>Thank you! Order <span class="onum"><?= htmlspecialchars($orderNum) ?></span> has been placed.<br>We'll contact you shortly to confirm delivery.</p>
    <a href="order.php" class="btn-again">Place Another Order</a>
  </div>

  <?php else: ?>
    <?php if ($error): ?><div class="err">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST" action="order.php">
      <div class="row2">
        <div class="fg"><label>Full Name *</label><input type="text" name="customer_name" value="<?= htmlspecialchars($_POST['customer_name']??'') ?>" placeholder="Your full name" required /></div>
        <div class="fg"><label>Contact # *</label><input type="text" name="contact_number" value="<?= htmlspecialchars($_POST['contact_number']??'') ?>" placeholder="09XX-XXX-XXXX" required /></div>
      </div>
      <div class="fg"><label>Delivery Address *</label><input type="text" name="delivery_address" value="<?= htmlspecialchars($_POST['delivery_address']??'') ?>" placeholder="House #, Street, Barangay" required /></div>
      <div class="fg">
        <label>Product *</label>
        <select name="product_id" id="coProd" onchange="coCalc()">
          <?php foreach ($products as $p): ?>
          <option value="<?= $p['id'] ?>" data-price="<?= $p['unit_price'] ?>" <?= ($_POST['product_id']??'')==$p['id']?'selected':'' ?>>
            <?= htmlspecialchars($p['product_name']) ?> — ₱<?= number_format($p['unit_price'],2) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="fg"><label>Quantity *</label><input type="number" name="quantity" id="coQty" value="<?= (int)($_POST['quantity']??1) ?>" min="1" oninput="coCalc()" /></div>
      <div class="total-bar"><span>Order Total</span><span class="total-val" id="coTot">₱0.00</span></div>
      <button type="submit" class="btn-sub">📦 Submit Order</button>
    </form>
  <?php endif; ?>
</div>
<script>
function coCalc(){
  const s=document.getElementById('coProd');
  const p=parseFloat(s?.options[s.selectedIndex]?.dataset.price||0);
  const q=parseInt(document.getElementById('coQty')?.value||0);
  const el=document.getElementById('coTot');
  if(el)el.textContent='₱'+(p*q).toFixed(2);
}
document.addEventListener('DOMContentLoaded',coCalc);
</script>
</body>
</html>
