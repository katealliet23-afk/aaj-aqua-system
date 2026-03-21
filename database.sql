-- ============================================================
--  AAJ AQUA v2 — DATABASE SETUP (Glassmorphism Design)
--  1. Open phpMyAdmin → http://localhost/phpmyadmin
--  2. Click "Import" tab → Choose this file → Click "Go"
-- ============================================================

CREATE DATABASE IF NOT EXISTS aaj_aqua_v2;
USE aaj_aqua_v2;

-- ── USERS ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    full_name  VARCHAR(100) NOT NULL,
    role       ENUM('owner','staff') DEFAULT 'owner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Login: admin / aqua2024
INSERT INTO users (username, password, full_name, role) VALUES
('admin', MD5('aqua2024'), 'Admin User', 'owner');

-- ── INVENTORY ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS inventory (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    product_name  VARCHAR(100) NOT NULL,
    category      ENUM('Container','Sachet','Accessory') DEFAULT 'Container',
    quantity      INT           NOT NULL DEFAULT 0,
    reorder_level INT           NOT NULL DEFAULT 20,
    unit_price    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO inventory (product_name, category, quantity, reorder_level, unit_price) VALUES
('5-Gallon Purified Water', 'Container',  75, 20,  65.00),
('Round Jug (1.5L)',        'Container',  42, 15,  30.00),
('Slim Container (3.7L)',   'Container',  20, 20,  55.00),
('Mineral Sachet (500ml)',  'Sachet',     30, 50,  15.00),
('10-Gallon Container',     'Container',  28, 10, 120.00),
('Dispenser (Table Top)',   'Accessory',   6,  3, 850.00);

-- ── ORDERS ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    order_number     VARCHAR(20)   NOT NULL UNIQUE,
    order_type       ENUM('Walk-in','Delivery') DEFAULT 'Walk-in',
    customer_name    VARCHAR(100),
    contact_number   VARCHAR(20),
    delivery_address TEXT,
    product_id       INT NOT NULL,
    product_name     VARCHAR(100) NOT NULL,
    quantity         INT           NOT NULL DEFAULT 1,
    unit_price       DECIMAL(10,2) NOT NULL,
    total_amount     DECIMAL(10,2) NOT NULL,
    status           ENUM('Pending','In Process','Out for Delivery','Completed','Cancelled') DEFAULT 'Pending',
    source           ENUM('admin','qr_scan','walk-in') DEFAULT 'admin',
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES inventory(id) ON DELETE RESTRICT
);

INSERT INTO orders (order_number, order_type, customer_name, contact_number, delivery_address, product_id, product_name, quantity, unit_price, total_amount, status, source) VALUES
('ORD-0001','Delivery','Maria Santos',   '09171234567','Blk 3 Lot 5, Brgy Tubig, Davao',  1,'5-Gallon Purified Water', 3, 65.00, 195.00,'Completed',      'admin'),
('ORD-0002','Walk-in', 'Walk-in',         NULL,          NULL,                               2,'Round Jug (1.5L)',        2, 30.00,  60.00,'Completed',      'walk-in'),
('ORD-0003','Delivery','Juan Dela Cruz', '09281234567','123 Water St., Brgy Puro, Davao',  1,'5-Gallon Purified Water', 5, 65.00, 325.00,'Pending',        'admin'),
('ORD-0004','Delivery','Ana Reyes',      '09391234567','456 Spring Ave., Brgy Basa',        3,'Slim Container (3.7L)',   4, 55.00, 220.00,'Out for Delivery','admin'),
('ORD-0005','Walk-in', 'Walk-in',         NULL,          NULL,                               2,'Round Jug (1.5L)',        1, 30.00,  30.00,'Completed',      'walk-in'),
('ORD-0006','Delivery','Pedro Bautista', '09451234567','789 River Rd., Brgy Daloy',         4,'Mineral Sachet (500ml)', 10, 15.00, 150.00,'In Process',     'qr_scan');

-- ── QR SETTINGS ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS qr_settings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    order_url    VARCHAR(255) DEFAULT 'http://localhost/aaj-aqua-v3/aaj-aqua-v3/customer/order.php',
    station_name VARCHAR(150) DEFAULT 'AAJ AQUA Clear Refilling Station',
    qr_image     VARCHAR(255) NULL,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO qr_settings (order_url, station_name) VALUES
('http://192.168.31.250/aaj-aqua-v3/aaj-aqua-v3/customer/order.php', 'AAJ AQUA Clear Refilling Station');
