<?php
// includes/config.php — Application Configuration

// Public URL for QR codes (works from any network)
// Update this when you deploy to Vercel
if (!empty($_SERVER['VERCEL']) || 
    (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'vercel.app') !== false)) {
    
    // Production: Your Vercel URL
    define('PUBLIC_URL', 'https://your-vercel-app.vercel.app');
} else {
    // Development: Local
    define('PUBLIC_URL', 'http://localhost/aaj-aqua-v3');
}

function getPublicOrderUrl($relativePath = '/customer/order.php') {
    return PUBLIC_URL . $relativePath;
}

function getAppBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path   = dirname($_SERVER['SCRIPT_NAME']);
    $root   = preg_replace('#/pages$#', '', $path);
    return rtrim($scheme . '://' . $host . $root, '/');
}
?>
