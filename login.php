<?php
// login.php
session_start();
if (!empty($_SESSION['user_id'])) { header('Location: pages/dashboard.php'); exit(); }
require_once 'includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = clean($conn, $_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u && $p) {
        $res  = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' LIMIT 1");
        $user = mysqli_fetch_assoc($res);
        if ($user && $user['password'] === md5($p)) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: pages/dashboard.php'); exit();
        } else { $error = 'Invalid username or password.'; }
    } else { $error = 'Please enter both fields.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AAJ AQUA — Login</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<style>
:root{--aqua:#0ea5e9;--teal:#14b8a6;--f-display:'Playfair Display',serif;--f-body:'Nunito',sans-serif;}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:var(--f-body);min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(145deg,#e0f2fe 0%,#f0fdf4 45%,#fef9c3 100%);overflow:hidden;position:relative;}
.d-blob{position:absolute;border-radius:50%;filter:blur(80px);}
.d-blob:nth-child(1){width:500px;height:500px;background:rgba(14,165,233,.18);top:-140px;left:-100px;}
.d-blob:nth-child(2){width:400px;height:400px;background:rgba(20,184,166,.16);bottom:-80px;right:-60px;}
.d-blob:nth-child(3){width:300px;height:300px;background:rgba(249,115,22,.1);top:35%;right:12%;}
.card{position:relative;z-index:10;background:rgba(255,255,255,.75);backdrop-filter:blur(24px);border:1.5px solid rgba(255,255,255,.82);border-radius:32px;padding:52px 46px;width:440px;box-shadow:0 30px 80px rgba(14,165,233,.18);}
.brand{text-align:center;margin-bottom:36px;}
.icon{width:70px;height:70px;background:linear-gradient(145deg,#0ea5e9,#14b8a6);border-radius:22px;margin:0 auto 18px;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 30px rgba(14,165,233,.4);animation:iconPulse 3s ease-in-out infinite;}
.icon svg{width:36px;height:36px;fill:white;}
@keyframes iconPulse{0%,100%{box-shadow:0 12px 30px rgba(14,165,233,.4)}50%{box-shadow:0 16px 40px rgba(14,165,233,.65),0 0 0 8px rgba(14,165,233,.1)}}
.brand h1{font-family:var(--f-display);font-size:28px;font-weight:700;color:#0c1b2e;}
.brand p{font-size:13.5px;color:#7a9cbb;margin-top:5px;font-weight:500;}
.fg{margin-bottom:18px;}
.fg label{display:block;font-size:11px;font-weight:800;letter-spacing:1.2px;text-transform:uppercase;color:#3d5a7a;margin-bottom:8px;}
.fg input{width:100%;padding:14px 18px;background:rgba(255,255,255,.82);border:1.5px solid rgba(14,165,233,.2);border-radius:14px;font-family:var(--f-body);font-size:15px;color:#0c1b2e;outline:none;transition:all .2s;}
.fg input:focus{border-color:var(--aqua);background:white;box-shadow:0 0 0 4px rgba(14,165,233,.1);}
.fg input::placeholder{color:#7a9cbb;}
.err{background:#fee2e2;color:#991b1b;border:1.5px solid rgba(244,63,94,.2);border-radius:10px;padding:11px 16px;font-size:13px;font-weight:600;margin-bottom:16px;}
.btn-login{width:100%;padding:15px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);border:none;border-radius:14px;color:white;font-family:var(--f-display);font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 8px 24px rgba(14,165,233,.4);transition:transform .15s,box-shadow .2s;letter-spacing:.3px;}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(14,165,233,.55);}
.hint{text-align:center;margin-top:16px;font-size:13px;color:#7a9cbb;}
.hint b{color:var(--aqua);font-weight:700;}
</style>
</head>
<body>
<div class="d-blob"></div><div class="d-blob"></div><div class="d-blob"></div>
<div class="card">
  <div class="brand">
    <div class="icon"><svg viewBox="0 0 24 24"><path d="M12 2C8 2 5 8 5 12c0 3.87 3.13 7 7 7s7-3.13 7-7c0-4-3-10-7-10z"/></svg></div>
    <h1>AAJ AQUA</h1>
    <p>Pure Water Refilling Station</p>
  </div>
  <?php if ($error): ?><div class="err">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <div class="fg"><label>Username</label><input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Enter username" autofocus required /></div>
    <div class="fg"><label>Password</label><input type="password" name="password" placeholder="Enter password" required /></div>
    <button type="submit" class="btn-login">Sign In →</button>
  </form>
</div>
</body>
</html>
