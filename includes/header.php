<?php
// includes/header.php
// Set before including: $pageTitle, $activePage, $rootPath
require_once __DIR__ . '/auth.php';
requireLogin($rootPath ?? '../');
require_once __DIR__ . '/db.php';
$user = getUser();
$flash    = $_SESSION['flash']     ?? '';
$flashErr = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_err']);

// Sidebar badge counts
$pendingCount = (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM orders WHERE status='Pending'"))['c'];
$lowStockCount= (int)mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) c FROM inventory WHERE quantity<=reorder_level"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AAJ AQUA — <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $rootPath ?>css/style.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<!-- Load QRCode from local source first (guaranteed to work) -->
<script src="<?= $rootPath ?>js/qrcode.min.js"></script>
</head>
<body>
<div class="bg-blobs">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
</div>

<div class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-inner">
      <div class="sb-logo-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2C8 2 5 8 5 12c0 3.87 3.13 7 7 7s7-3.13 7-7c0-4-3-10-7-10z"/></svg>
      </div>
      <div><h2>AAJ AQUA</h2><span>Refilling Station</span></div>
    </div>
  </div>

  <div class="sb-section-label">Overview</div>
  <a href="<?= $rootPath ?>pages/dashboard.php"  class="nav-link <?= ($activePage==='dashboard') ?'active':'' ?>"><div class="nl-icon">🏠</div> Dashboard</a>

  <div class="sb-section-label">Operations</div>
  <a href="<?= $rootPath ?>pages/orders.php"    class="nav-link <?= ($activePage==='orders')    ?'active':'' ?>">
    <div class="nl-icon">🛒</div> Orders
    <?php if ($pendingCount > 0): ?><span class="nl-badge"><?= $pendingCount ?></span><?php endif; ?>
  </a>
  <a href="<?= $rootPath ?>pages/inventory.php" class="nav-link <?= ($activePage==='inventory') ?'active':'' ?>">
    <div class="nl-icon">📦</div> Inventory
    <?php if ($lowStockCount > 0): ?><span class="nl-badge amber"><?= $lowStockCount ?></span><?php endif; ?>
  </a>
  <a href="<?= $rootPath ?>pages/qrmanage.php"  class="nav-link <?= ($activePage==='qrmanage')  ?'active':'' ?>"><div class="nl-icon">📱</div> QR Management</a>

  <div class="sb-section-label">Insights</div>
  <a href="<?= $rootPath ?>pages/analytics.php" class="nav-link <?= ($activePage==='analytics') ?'active':'' ?>"><div class="nl-icon">📈</div> Analytics</a>
  <a href="<?= $rootPath ?>pages/settings.php"  class="nav-link <?= ($activePage==='settings')  ?'active':'' ?>"><div class="nl-icon">⚙️</div> Settings</a>

  <div class="sb-footer">
    <div class="sb-user">
      <div class="sb-avatar"><?= htmlspecialchars($user['initial']) ?></div>
      <div>
        <div class="sb-uname"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="sb-role"><?= strtoupper($user['role']) ?></div>
      </div>
      <a href="<?= $rootPath ?>logout.php" class="sb-logout" title="Sign out">⏻</a>
    </div>
  </div>
</div>

<div class="main-area">
  <div class="topbar">
    <div class="tb-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></div>
    <div class="tb-badge"><span class="tb-dot"></span> Live</div>
    <div class="tb-time" id="tbTime"></div>
    <div class="tb-user">
      <div class="tb-uav"><?= htmlspecialchars($user['initial']) ?></div>
      <span class="tb-uname"><?= htmlspecialchars($user['full_name']) ?></span>
    </div>
  </div>
  <div class="content">
    <?php if ($flash):    ?><div class="alert alert-ok mb"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <?php if ($flashErr): ?><div class="alert alert-err mb"><?= htmlspecialchars($flashErr) ?></div><?php endif; ?>
