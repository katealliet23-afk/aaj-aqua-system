# AAJ Aqua - Cloud Deployment Guide

## 🚀 Quick Setup for QR Code Working from Any Network

### Step 1: Get Railway Connection String
1. Go to railway.app
2. Click on your MySQL database
3. Go to "Connect" tab
4. Copy the connection string

### Step 2: Update Database Connection
1. Open `includes/db_cloud.php`
2. Replace these lines with your Railway details:
   ```php
   define('DB_HOST', 'your_railway_host');
   define('DB_USER', 'your_railway_username');
   define('DB_PASS', 'your_railway_password');
   define('DB_NAME', 'railway');
   define('DB_PORT', 'your_railway_port');
   ```

### Step 3: Create Database Tables
Run this SQL in Railway Query tab:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    quantity INT DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    reorder_level INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    delivery_address TEXT,
    product_id INT,
    quantity INT DEFAULT 1,
    total_amount DECIMAL(10,2),
    status ENUM('Pending', 'In Process', 'Out for Delivery', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE qr_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_url VARCHAR(255) DEFAULT '/customer/order.php',
    station_name VARCHAR(100) DEFAULT 'AAJ AQUA Clear Refilling Station'
);
```

### Step 4: Deploy to Vercel
1. Go to vercel.com
2. Click "New Project"
3. Upload your `aaj-aqua-v3` folder
4. Vercel will deploy and give you a URL like: `https://your-app.vercel.app`

### Step 5: Update Public URL
1. Open `includes/config.php`
2. Replace this line with your Vercel URL:
   ```php
   define('PUBLIC_URL', 'https://your-app.vercel.app');
   ```

### Step 6: Switch Database Connection
1. In all PHP files, replace:
   ```php
   require_once 'includes/db.php';
   ```
   With:
   ```php
   require_once 'includes/db_cloud.php';
   ```

### Step 7: Test QR Code
1. Go to your Vercel URL
2. QR code will use your public URL
3. Scan with any phone on any network
4. Orders will save to Railway database

## ✅ Result
- QR code works from ANY network worldwide
- No IP address changes needed
- Cloud database stores all orders
- Professional online ordering system

## 📞 Support
If you need help with any step, let me know!
