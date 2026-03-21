<?php
$pageTitle  = 'Inventory';
$activePage = 'inventory';
$rootPath   = '../';
require_once '../includes/header.php';

$q     = clean($conn, $_GET['q'] ?? '');
$where = $q ? "WHERE product_name LIKE '%$q%' OR category LIKE '%$q%'" : '';
$items = [];
$res   = mysqli_query($conn, "SELECT * FROM inventory $where ORDER BY product_name");
while ($r = mysqli_fetch_assoc($res)) $items[] = $r;
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
  <div class="sec-title">📦 Inventory (<?= count($items) ?> items)</div>
  <div style="display:flex;gap:10px;align-items:center;">
    <form method="GET" style="display:flex;gap:8px;">
      <div style="position:relative;">
        <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;">🔍</span>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search products..." style="padding:10px 16px 10px 38px;background:rgba(255,255,255,.8);border:1.5px solid rgba(14,165,233,.15);border-radius:12px;font-size:13px;width:220px;outline:none;color:var(--text-dark);" />
      </div>
      <button type="submit" class="btn btn-ghost btn-sm">Search</button>
      <?php if ($q): ?><a href="inventory.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>
    <button class="btn btn-aqua" onclick="openModal('addProd')">＋ Add Product</button>
  </div>
</div>

<div class="tbl-container">
  <table>
    <thead><tr><th>Product</th><th>Category</th><th>Qty on Hand</th><th>Reorder Level</th><th>Unit Price</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (empty($items)): ?>
      <tr><td colspan="7" style="text-align:center;color:var(--text-soft);padding:28px;">No products found.</td></tr>
    <?php else: ?>
    <?php foreach ($items as $it):
      if ($it['quantity'] <= 0)                          { $st='Out of Stock'; $sc='b-del'; }
      elseif ($it['quantity'] <= $it['reorder_level'])   { $st='Low Stock';    $sc='b-warn'; }
      else                                               { $st='In Stock';     $sc='b-ok'; }
    ?>
    <tr>
      <td class="tname"><?= htmlspecialchars($it['product_name']) ?></td>
      <td><span class="badge b-info"><?= $it['category'] ?></span></td>
      <td style="font-weight:800;color:<?= $it['quantity']<=$it['reorder_level']?'#f43f5e':'var(--text-dark)' ?>;"><?= $it['quantity'] ?></td>
      <td style="color:var(--text-soft);"><?= $it['reorder_level'] ?></td>
      <td class="tprice">₱<?= number_format($it['unit_price'],2) ?></td>
      <td><span class="badge <?= $sc ?>"><?= $st ?></span></td>
      <td style="white-space:nowrap;">
        <button class="btn btn-ghost btn-sm" onclick="openRestock(<?= $it['id'] ?>,'<?= addslashes($it['product_name']) ?>',<?= $it['quantity'] ?>)">+ Restock</button>
        <button class="btn btn-ghost btn-sm" style="margin-left:5px;" onclick="openEdit(<?= $it['id'] ?>,'<?= addslashes($it['product_name']) ?>','<?= $it['category'] ?>',<?= $it['quantity'] ?>,<?= $it['reorder_level'] ?>,<?= $it['unit_price'] ?>)">✏️ Edit</button>
        <form method="POST" action="../api/delete_product.php" style="display:inline;" onsubmit="return confirm('Delete this product?')">
          <input type="hidden" name="product_id" value="<?= $it['id'] ?>">
          <input type="hidden" name="redirect"    value="../pages/inventory.php">
          <button type="submit" class="btn btn-danger btn-sm" style="margin-left:5px;">✕</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ADD MODAL -->
<div class="modal-ov" id="modal-addProd">
  <div class="modal">
    <div class="m-hdr"><div class="m-title">📦 Add New Product</div><button class="m-close" onclick="closeModal('addProd')">✕</button></div>
    <form method="POST" action="../api/add_product.php">
      <div class="form-grid" style="margin-bottom:14px;">
        <div class="field"><label>Product Name</label><input type="text" name="product_name" placeholder="e.g. 5-Gallon Purified" required /></div>
        <div class="field"><label>Category</label><select name="category"><option>Container</option><option>Sachet</option><option>Accessory</option></select></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>Initial Qty</label><input type="number" name="quantity" value="0" min="0" /></div>
        <div class="field"><label>Reorder Level</label><input type="number" name="reorder_level" value="20" min="0" /></div>
        <div class="field"><label>Price (₱)</label><input type="number" name="unit_price" step="0.01" value="0" min="0" /></div>
      </div>
      <input type="hidden" name="redirect" value="../pages/inventory.php">
      <div style="display:flex;gap:12px;margin-top:4px;"><button type="submit" class="btn btn-aqua">✓ Save Product</button><button type="button" class="btn btn-ghost" onclick="closeModal('addProd')">Cancel</button></div>
    </form>
  </div>
</div>

<!-- RESTOCK MODAL -->
<div class="modal-ov" id="modal-restock">
  <div class="modal">
    <div class="m-hdr"><div class="m-title">🔄 Restock Product</div><button class="m-close" onclick="closeModal('restock')">✕</button></div>
    <form method="POST" action="../api/restock_product.php">
      <input type="hidden" name="product_id" id="rstId" />
      <p style="font-size:14px;color:var(--text-mid);margin-bottom:18px;">Product: <b id="rstName"></b><br>Current stock: <b id="rstCurrent" style="color:var(--aqua);"></b></p>
      <div class="field" style="margin-bottom:20px;"><label>Quantity to Add</label><input type="number" name="add_qty" value="10" min="1" required /></div>
      <input type="hidden" name="redirect" value="../pages/inventory.php">
      <div style="display:flex;gap:12px;"><button type="submit" class="btn btn-aqua">✓ Add Stock</button><button type="button" class="btn btn-ghost" onclick="closeModal('restock')">Cancel</button></div>
    </form>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-ov" id="modal-editProd">
  <div class="modal">
    <div class="m-hdr"><div class="m-title">✏️ Edit Product</div><button class="m-close" onclick="closeModal('editProd')">✕</button></div>
    <form method="POST" action="../api/edit_product.php">
      <input type="hidden" name="product_id" id="editId" />
      <div class="form-grid" style="margin-bottom:14px;">
        <div class="field"><label>Product Name</label><input type="text" name="product_name" id="editName" required /></div>
        <div class="field"><label>Category</label><select name="category" id="editCat"><option>Container</option><option>Sachet</option><option>Accessory</option></select></div>
      </div>
      <div class="form-grid">
        <div class="field"><label>Quantity</label><input type="number" name="quantity" id="editQty" min="0" /></div>
        <div class="field"><label>Reorder Level</label><input type="number" name="reorder_level" id="editReorder" min="0" /></div>
        <div class="field"><label>Price (₱)</label><input type="number" name="unit_price" id="editPrice" step="0.01" min="0" /></div>
      </div>
      <input type="hidden" name="redirect" value="../pages/inventory.php">
      <div style="display:flex;gap:12px;margin-top:4px;"><button type="submit" class="btn btn-aqua">✓ Save Changes</button><button type="button" class="btn btn-ghost" onclick="closeModal('editProd')">Cancel</button></div>
    </form>
  </div>
</div>

<?php $extraJs = <<<JS
<script>
function openRestock(id,name,qty){document.getElementById('rstId').value=id;document.getElementById('rstName').textContent=name;document.getElementById('rstCurrent').textContent=qty;openModal('restock');}
function openEdit(id,name,cat,qty,reorder,price){document.getElementById('editId').value=id;document.getElementById('editName').value=name;document.getElementById('editCat').value=cat;document.getElementById('editQty').value=qty;document.getElementById('editReorder').value=reorder;document.getElementById('editPrice').value=price;openModal('editProd');}
</script>
JS; ?>
<?php require_once '../includes/footer.php'; ?>
