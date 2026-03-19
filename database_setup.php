#!/usr/bin/env php
<?php
/**
 * Database Setup Script
 * Initializes the database with proper schema, indexes, and sample data
 * 
 * Usage: php database_setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ANSI color codes for terminal output
class Colors {
    const RESET = "\033[0m";
    const RED = "\033[91m";
    const GREEN = "\033[92m";
    const YELLOW = "\033[93m";
    const BLUE = "\033[94m";
    const CYAN = "\033[96m";
}

function print_header($text) {
    echo "\n" . Colors::CYAN . "===============================================" . Colors::RESET . "\n";
    echo Colors::CYAN . $text . Colors::RESET . "\n";
    echo Colors::CYAN . "===============================================" . Colors::RESET . "\n\n";
}

function print_success($text) {
    echo Colors::GREEN . "✓ " . $text . Colors::RESET . "\n";
}

function print_error($text) {
    echo Colors::RED . "✗ " . $text . Colors::RESET . "\n";
}

function print_warning($text) {
    echo Colors::YELLOW . "⚠ " . $text . Colors::RESET . "\n";
}

function print_info($text) {
    echo Colors::BLUE . "ℹ " . $text . Colors::RESET . "\n";
}

// Configuration
print_header("Database Setup - OBOADE NYAME HARDWARES Store");

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'omanbapa_store';

print_info("Connecting to database server...");

// Connect to MySQL
$conn = new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    print_error("Connection failed: " . $conn->connect_error);
    exit(1);
}

print_success("Connected to MySQL server");

// Drop existing database if it exists
print_info("Checking if database exists...");
if ($conn->select_db($db_name)) {
    print_warning("Database '$db_name' already exists. Dropping...");
    if ($conn->query("DROP DATABASE `$db_name`")) {
        print_success("Database dropped successfully");
    } else {
        print_error("Failed to drop database: " . $conn->error);
        exit(1);
    }
}

// Create new database
print_info("Creating new database...");
if ($conn->query("CREATE DATABASE `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    print_success("Database created successfully");
} else {
    print_error("Failed to create database: " . $conn->error);
    exit(1);
}

// Select database
$conn->select_db($db_name);

// Create tables
print_header("Creating Tables");

$tables = [
    // Users table
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Products table
    "CREATE TABLE products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_category (category),
        INDEX idx_quantity (quantity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    // Sales table
    "CREATE TABLE sales (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        quantity_sold INT NOT NULL,
        total_price DECIMAL(10, 2) NOT NULL,
        sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_product_id (product_id),
        INDEX idx_sale_date (sale_date),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $table) {
    if ($conn->query($table)) {
        $table_name = preg_match('/CREATE TABLE (\w+)/', $table, $matches) ? $matches[1] : 'Unknown';
        print_success("Created table: $table_name");
    } else {
        print_error("Failed to create table: " . $conn->error);
        exit(1);
    }
}

// Insert default admin user
print_header("Setting Up Default Users");

$admin_password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
$cashier_password = password_hash('cashier123', PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
if (!$stmt) {
    print_error("Prepare failed: " . $conn->error);
    exit(1);
}

// Insert admin user
$stmt->bind_param("sss", $admin_user, $admin_password, $admin_role);
$admin_user = 'admin';
$admin_role = 'admin';
if ($stmt->execute()) {
    print_success("Created admin user: admin (password: admin123)");
} else {
    print_error("Failed to create admin user: " . $stmt->error);
}

// Insert cashier user
$stmt2 = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
if (!$stmt2) {
    print_error("Prepare failed: " . $conn->error);
    exit(1);
}

$stmt2->bind_param("sss", $cashier_user, $cashier_password, $cashier_role);
$cashier_user = 'cashier';
$cashier_role = 'cashier';
if ($stmt2->execute()) {
    print_success("Created cashier user: cashier (password: cashier123)");
} else {
    print_error("Failed to create cashier user: " . $stmt2->error);
}

// Insert sample products
print_header("Inserting Sample Products");

$products = [
    ['Cement (25kg)', 'Building Materials', 50, 35.50],
    ['Steel Rods (12mm)', 'Steel & Metal', 30, 45.00],
    ['Wooden Planks', 'Lumber', 100, 12.50],
    ['Nails (1kg)', 'Hardware', 200, 5.00],
    ['Paint (2L)', 'Coatings', 40, 25.00],
    ['Tiles (Box)', 'Tiles', 25, 55.00],
    ['Hinges', 'Hardware', 150, 3.50],
    ['Door Locks', 'Hardware', 45, 15.00],
    ['Sandpaper', 'Tools & Equipment', 80, 2.50],
    ['Measuring Tape (5m)', 'Tools & Equipment', 60, 8.00],
];

$product_stmt = $conn->prepare("INSERT INTO products (name, category, quantity, price) VALUES (?, ?, ?, ?)");
if (!$product_stmt) {
    print_error("Prepare failed: " . $conn->error);
    exit(1);
}

$inserted = 0;
foreach ($products as $product) {
    $product_stmt->bind_param("ssid", $product[0], $product[1], $product[2], $product[3]);
    if ($product_stmt->execute()) {
        $inserted++;
    } else {
        print_warning("Failed to insert product: " . $product[0]);
    }
}

print_success("Inserted $inserted sample products");

// Create database backup
print_header("Creating Database Backup");

$backup_dir = 'backups';
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
    print_success("Created backup directory");
}

$backup_file = $backup_dir . '/' . $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';

// Backup database
$result = shell_exec("mysqldump -u $db_user $db_name > $backup_file 2>&1");
if (file_exists($backup_file)) {
    print_success("Database backup created: $backup_file");
} else {
    print_warning("Failed to create backup file");
}

// Database statistics
print_header("Database Statistics");

$result = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = $result->fetch_assoc()['count'];
print_info("Total Users: $user_count");

$result = $conn->query("SELECT COUNT(*) as count FROM products");
$product_count = $result->fetch_assoc()['count'];
print_info("Total Products: $product_count");

$result = $conn->query("SELECT COUNT(*) as count FROM sales");
$sales_count = $result->fetch_assoc()['count'];
print_info("Total Sales: $sales_count");

$result = $conn->query("SELECT SUM(quantity) as total FROM products");
$total_inventory = $result->fetch_assoc()['total'];
print_info("Total Inventory Units: $total_inventory");

// Close connection
$conn->close();

print_header("Setup Complete!");
print_success("Database '$db_name' has been successfully set up");
print_info("Admin URL: http://localhost/omanbapa");
print_info("Default Admin Credentials:");
print_info("  Username: admin");
print_info("  Password: admin123");
print_info("Default Cashier Credentials:");
print_info("  Username: cashier");
print_info("  Password: cashier123");
print_warning("IMPORTANT: Change default passwords in production!");

echo "\n";
?>
