<?php
session_start();
header('Location: ' . (isset($_SESSION['user_id']) ? 'pages/dashboard.php' : 'login.php'));
exit();
?>
