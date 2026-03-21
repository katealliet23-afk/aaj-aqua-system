<?php
$pageTitle  = 'Settings';
$activePage = 'settings';
$rootPath   = '../';
require_once '../includes/header.php';

$qrRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM qr_settings LIMIT 1"));
$user  = getUser();
?>

<div class="two-col">
  <div class="glass-card">
    <div class="sec-title" style="margin-bottom:20px;">🏪 Station Settings</div>
    <form method="POST" action="../api/update_settings.php">
      <div class="field" style="margin-bottom:16px;"><label>Station Name</label><input type="text" name="station_name" value="<?= htmlspecialchars($qrRow['station_name'] ?? '') ?>" /></div>
      <div class="field" style="margin-bottom:20px;"><label>Order Page URL</label><input type="text" name="order_url" value="<?= htmlspecialchars($qrRow['order_url'] ?? '/customer/order.php') ?>" /><small>Relative URL (recommended): /customer/order.php, or absolute: https://yourdomain/customer/order.php</small></div>
      <input type="hidden" name="redirect" value="../pages/settings.php">
      <button type="submit" class="btn btn-aqua">💾 Save Settings</button>
    </form>
  </div>
  <div class="glass-card">
    <div class="sec-title" style="margin-bottom:20px;">🔑 Change Password</div>
    <form method="POST" action="../api/change_password.php">
      <div class="field" style="margin-bottom:14px;"><label>Current Password</label><input type="password" name="current_password" required /></div>
      <div class="field" style="margin-bottom:14px;"><label>New Password</label><input type="password" name="new_password" required minlength="6" /></div>
      <div class="field" style="margin-bottom:20px;"><label>Confirm New Password</label><input type="password" name="confirm_password" required /></div>
      <input type="hidden" name="redirect" value="../pages/settings.php">
      <button type="submit" class="btn btn-aqua">🔒 Update Password</button>
    </form>
  </div>
</div>

<div class="glass-card" style="margin-top:20px;max-width:480px;">
  <div class="sec-title" style="margin-bottom:16px;">ℹ️ System Info</div>
  <table>
    <tbody>
      <tr><td class="text-soft" style="width:150px;">Version</td><td class="fw7">AAJ AQUA v2.0</td></tr>
      <tr><td class="text-soft">Design Theme</td><td class="fw7">Aqua Glass ✨</td></tr>
      <tr><td class="text-soft">PHP Version</td><td class="fw7"><?= PHP_VERSION ?></td></tr>
      <tr><td class="text-soft">Database</td><td class="fw7">MySQL via XAMPP</td></tr>
      <tr><td class="text-soft">Logged in as</td><td class="fw7"><?= htmlspecialchars($user['full_name']) ?> (<?= $user['role'] ?>)</td></tr>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
