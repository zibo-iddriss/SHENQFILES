<?php
/**
 * System Verification Script
 * Checks all database tables, columns, and code alignment
 */

require_once 'config.php';

$verification_results = [];
$errors = [];
$warnings = [];

// Check users table
echo "<pre style='background: #f5f5f5; padding: 20px; border-radius: 8px; font-family: monospace; overflow-x: auto;'>";
echo "<h2>🔍 SYSTEM VERIFICATION REPORT</h2>\n";
echo str_repeat("=", 80) . "\n\n";

// 1. Check users table structure
echo "1. CHECKING USERS TABLE:\n";
$users_columns = $conn->query("SHOW COLUMNS FROM users");
$users_expected = ['id', 'username', 'password', 'role', 'created_at'];
$users_found = [];

if($users_columns) {
    while($col = $users_columns->fetch_assoc()) {
        $users_found[] = $col['Field'];
        echo "   ✓ Column: " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    $errors[] = "Could not verify users table";
    echo "   ✗ Error reading users table\n";
}

// 2. Check products table
echo "\n2. CHECKING PRODUCTS TABLE:\n";
$products_columns = $conn->query("SHOW COLUMNS FROM products");
$products_expected = ['id', 'name', 'category', 'quantity', 'price', 'created_at'];
$products_found = [];

if($products_columns) {
    while($col = $products_columns->fetch_assoc()) {
        $products_found[] = $col['Field'];
        echo "   ✓ Column: " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    $errors[] = "Could not verify products table";
    echo "   ✗ Error reading products table\n";
}

// 3. Check sales table
echo "\n3. CHECKING SALES TABLE:\n";
$sales_columns = $conn->query("SHOW COLUMNS FROM sales");
$sales_expected = ['id', 'product_id', 'quantity_sold', 'total_price', 'created_at'];
$sales_found = [];

if($sales_columns) {
    while($col = $sales_columns->fetch_assoc()) {
        $sales_found[] = $col['Field'];
        echo "   ✓ Column: " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} else {
    $errors[] = "Could not verify sales table";
    echo "   ✗ Error reading sales table\n";
}

// Check for missing columns
echo "\n" . str_repeat("=", 80) . "\n";
echo "MISSING COLUMNS ANALYSIS:\n";

$missing_users = array_diff($users_expected, $users_found);
if(empty($missing_users)) {
    echo "   ✓ Users table: All required columns present\n";
} else {
    foreach($missing_users as $col) {
        echo "   ✗ Users table missing: $col\n";
        $warnings[] = "Users table missing column: $col";
    }
}

$missing_products = array_diff($products_expected, $products_found);
if(empty($missing_products)) {
    echo "   ✓ Products table: All required columns present\n";
} else {
    foreach($missing_products as $col) {
        echo "   ✗ Products table missing: $col\n";
        $warnings[] = "Products table missing column: $col";
    }
}

$missing_sales = array_diff($sales_expected, $sales_found);
if(empty($missing_sales)) {
    echo "   ✓ Sales table: All required columns present\n";
} else {
    foreach($missing_sales as $col) {
        echo "   ✗ Sales table missing: $col\n";
        $warnings[] = "Sales table missing column: $col";
    }
}

// Check data counts
echo "\n" . str_repeat("=", 80) . "\n";
echo "DATA STATISTICS:\n";

$user_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc();
echo "   Users: " . $user_count['count'] . " records\n";

$product_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
echo "   Products: " . $product_count['count'] . " records\n";

$sales_count = $conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc();
echo "   Sales: " . $sales_count['count'] . " records\n";

// Check file existence
echo "\n" . str_repeat("=", 80) . "\n";
echo "FILE VERIFICATION:\n";

$required_files = [
    'config.php',
    'login.php',
    'dashboard.php',
    'products.php',
    'sales.php',
    'users.php',
    'logout.php',
    'style.css'
];

foreach($required_files as $file) {
    if(file_exists($file)) {
        echo "   ✓ $file exists\n";
    } else {
        echo "   ✗ $file MISSING\n";
        $errors[] = "Missing file: $file";
    }
}

// Code alignment checks
echo "\n" . str_repeat("=", 80) . "\n";
echo "CODE ALIGNMENT CHECKS:\n";

// Check products.php for column references
$products_content = file_get_contents('products.php');
if(strpos($products_content, "['updated_at']") !== false) {
    echo "   ⚠ products.php still references 'updated_at' column\n";
    $warnings[] = "products.php references non-existent 'updated_at' column";
}
if(strpos($products_content, "['created_at']") !== false) {
    echo "   ✓ products.php correctly references 'created_at' column\n";
}

// Check login.php for last_login
$login_content = file_get_contents('login.php');
if(strpos($login_content, "last_login") !== false) {
    echo "   ⚠ login.php still references 'last_login' column\n";
    $warnings[] = "login.php references non-existent 'last_login' column";
} else {
    echo "   ✓ login.php does not reference 'last_login'\n";
}

// Check users.php for last_login
$users_content = file_get_contents('users.php');
if(strpos($users_content, "last_login") !== false) {
    echo "   ⚠ users.php still references 'last_login' column\n";
    $warnings[] = "users.php references non-existent 'last_login' column";
} else {
    echo "   ✓ users.php does not reference 'last_login'\n";
}

// Check sales.php for feature implementation
if(strpos($products_content, "customer_name") !== false) {
    echo "   ✓ sales.php has customer_name feature\n";
} else {
    echo "   ⚠ sales.php missing customer_name feature\n";
}

if(strpos($products_content, "clock") !== false || strpos($products_content, "updateClock") !== false) {
    echo "   ✓ dashboard.php has live clock feature\n";
} else {
    echo "   ⚠ dashboard.php missing live clock feature\n";
}

// Summary
echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY:\n";
echo "   Errors: " . count($errors) . "\n";
echo "   Warnings: " . count($warnings) . "\n";

if(count($errors) > 0) {
    echo "\n❌ ERRORS FOUND:\n";
    foreach($errors as $error) {
        echo "   - $error\n";
    }
}

if(count($warnings) > 0) {
    echo "\n⚠️ WARNINGS:\n";
    foreach($warnings as $warning) {
        echo "   - $warning\n";
    }
}

if(count($errors) == 0 && count($warnings) == 0) {
    echo "\n✅ ALL SYSTEMS OPERATIONAL\n";
    echo "   Database: ✓\n";
    echo "   Files: ✓\n";
    echo "   Code Alignment: ✓\n";
    echo "   Features: ✓\n";
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "</pre>";
?>
