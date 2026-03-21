<?php // api/upload_qr.php
session_start(); require_once '../includes/auth.php'; requireLogin();
require_once '../includes/db.php';

$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['qr_image'])) {
    $file = $_FILES['qr_image'];
    $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
    
    if (!in_array($file['type'], $allowed)) {
        $_SESSION['flash_err'] = 'Only PNG, JPG, GIF images allowed.';
        header("Location: ../pages/qrmanage.php");
        exit();
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        $_SESSION['flash_err'] = 'File size must be under 5MB.';
        header("Location: ../pages/qrmanage.php");
        exit();
    }
    
    $filename = 'qr_code_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old QR image if exists
        $old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT qr_image FROM qr_settings LIMIT 1"));
        if ($old && $old['qr_image'] && file_exists($uploadDir . $old['qr_image'])) {
            unlink($uploadDir . $old['qr_image']);
        }
        
        // Update database
        $exists = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM qr_settings LIMIT 1"));
        if ($exists) {
            mysqli_query($conn, "UPDATE qr_settings SET qr_image='$filename'");
        } else {
            mysqli_query($conn, "INSERT INTO qr_settings (qr_image) VALUES('$filename')");
        }
        
        $_SESSION['flash'] = 'QR Code uploaded successfully! 📲';
    } else {
        $_SESSION['flash_err'] = 'Failed to upload file. Please try again.';
    }
    
    header("Location: ../pages/qrmanage.php");
    exit();
}
