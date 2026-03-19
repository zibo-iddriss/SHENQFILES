<?php
/**
 * Comprehensive Testing Suite
 * Tests all critical functionality and security measures
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class SystemTest {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];
    private $conn;
    
    public function __construct() {
        $this->connect_database();
    }
    
    private function connect_database() {
        $this->conn = new mysqli("localhost", "root", "", "omanbapa_store");
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }
    
    public function test($name, $condition, $message = "") {
        if ($condition) {
            $this->passed++;
            $this->tests[] = [
                'name' => $name,
                'status' => 'PASS',
                'message' => $message
            ];
        } else {
            $this->failed++;
            $this->tests[] = [
                'name' => $name,
                'status' => 'FAIL',
                'message' => $message
            ];
        }
    }
    
    // ===== File Existence Tests =====
    public function test_files_exist() {
        echo "\n=== FILE EXISTENCE TESTS ===\n";
        
        $required_files = [
            'config.php',
            'login.php',
            'dashboard.php',
            'products.php',
            'sales.php',
            'users.php',
            'logout.php',
            'style.css',
            'security.php',
            'dashboard_analytics.php'
        ];
        
        foreach ($required_files as $file) {
            $file_exists = file_exists($file);
            $this->test(
                "File: $file",
                $file_exists,
                $file_exists ? "File found" : "File missing"
            );
        }
    }
    
    // ===== Database Tests =====
    public function test_database_structure() {
        echo "\n=== DATABASE STRUCTURE TESTS ===\n";
        
        // Check users table
        $result = $this->conn->query("SHOW TABLES LIKE 'users'");
        $this->test("Users table exists", $result->num_rows > 0);
        
        // Check products table
        $result = $this->conn->query("SHOW TABLES LIKE 'products'");
        $this->test("Products table exists", $result->num_rows > 0);
        
        // Check sales table
        $result = $this->conn->query("SHOW TABLES LIKE 'sales'");
        $this->test("Sales table exists", $result->num_rows > 0);
        
        // Check table columns
        $result = $this->conn->query("DESCRIBE users");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        $this->test("Users table has required columns", 
            in_array('id', $columns) && in_array('username', $columns) && in_array('password', $columns)
        );
        
        // Check for indexes
        $result = $this->conn->query("SHOW INDEX FROM users");
        $this->test("Users table has indexes", $result->num_rows > 0);
    }
    
    // ===== Data Integrity Tests =====
    public function test_data_integrity() {
        echo "\n=== DATA INTEGRITY TESTS ===\n";
        
        // Check if admin user exists
        $result = $this->conn->query("SELECT * FROM users WHERE role='admin' LIMIT 1");
        $this->test("Default admin user exists", $result->num_rows > 0);
        
        // Check if sample products exist
        $result = $this->conn->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch_assoc();
        $this->test("Sample products inserted", $row['count'] > 0, "Products: " . $row['count']);
        
        // Check data consistency
        $result = $this->conn->query("SELECT s.product_id FROM sales s WHERE s.product_id NOT IN (SELECT id FROM products)");
        $this->test("Sales referential integrity", $result->num_rows == 0, "Orphaned sales records: " . $result->num_rows);
    }
    
    // ===== Security Tests =====
    public function test_security() {
        echo "\n=== SECURITY TESTS ===\n";
        
        // Check if security.php exists and contains key functions
        if (file_exists('security.php')) {
            $security_content = file_get_contents('security.php');
            
            $this->test("sanitize_input function exists", strpos($security_content, 'function sanitize_input') !== false);
            $this->test("CSRF protection functions exist", strpos($security_content, 'function verify_csrf_token') !== false);
            $this->test("Rate limiting function exists", strpos($security_content, 'function check_rate_limit') !== false);
            $this->test("Password hash function exists", strpos($security_content, 'function hash_password') !== false);
        }
        
        // Check config.php for security practices
        if (file_exists('config.php')) {
            $config_content = file_get_contents('config.php');
            $this->test("Session status check exists", strpos($config_content, 'session_status') !== false);
        }
        
        // Check for prepared statements in main files
        $files_to_check = ['login.php', 'products.php', 'sales.php', 'users.php'];
        foreach ($files_to_check as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $has_prepared = strpos($content, 'prepare') !== false;
                $this->test("Prepared statements in $file", $has_prepared);
            }
        }
    }
    
    // ===== Performance Tests =====
    public function test_performance() {
        echo "\n=== PERFORMANCE TESTS ===\n";
        
        // Test query performance
        $start = microtime(true);
        $this->conn->query("SELECT COUNT(*) FROM sales");
        $query_time = microtime(true) - $start;
        $this->test("Query performance", $query_time < 1.0, "Query time: " . round($query_time * 1000, 2) . "ms");
        
        // Check for indexes on frequently queried columns
        $result = $this->conn->query("SHOW INDEX FROM sales WHERE Column_name='product_id'");
        $this->test("Index on sales.product_id", $result->num_rows > 0);
        
        $result = $this->conn->query("SHOW INDEX FROM sales WHERE Column_name='sale_date'");
        $this->test("Index on sales.sale_date", $result->num_rows > 0);
        
        $result = $this->conn->query("SHOW INDEX FROM products WHERE Column_name='category'");
        $this->test("Index on products.category", $result->num_rows > 0);
    }
    
    // ===== API/Function Tests =====
    public function test_functions() {
        echo "\n=== FUNCTION TESTS ===\n";
        
        if (file_exists('security.php')) {
            include 'security.php';
            
            // Test sanitize_input
            $dirty = "<script>alert('xss')</script>";
            $clean = sanitize_input($dirty);
            $this->test("sanitize_input removes XSS", strpos($clean, '<script>') === false);
            
            // Test validate_email
            $this->test("validate_email - valid email", validate_email('test@example.com'));
            $this->test("validate_email - invalid email", !validate_email('invalid-email'));
            
            // Test validate_username
            $this->test("validate_username - valid username", validate_username('test_user123'));
            $this->test("validate_username - invalid username", !validate_username('ab'));
            
            // Test password hashing
            $password = 'TestPassword123!';
            $hash = hash_password($password);
            $this->test("hash_password creates valid hash", verify_password($password, $hash));
            
            // Test strong password validation
            $this->test("validate_strong_password - strong", validate_strong_password('StrongPass123!'));
            $this->test("validate_strong_password - weak", !validate_strong_password('weak'));
        }
    }
    
    // ===== Configuration Tests =====
    public function test_configuration() {
        echo "\n=== CONFIGURATION TESTS ===\n";
        
        // Check .htaccess exists
        $this->test(".htaccess configured", file_exists('.htaccess'));
        
        // Check env.example.php exists
        $this->test("env.example.php exists", file_exists('env.example.php'));
        
        // Check documentation files
        $docs = [
            'README.md',
            'SECURITY.md',
            'DEPLOYMENT_CHECKLIST.md',
            'DATABASE_VERIFICATION.md',
            'SERVER_CONFIGURATION.md'
        ];
        
        foreach ($docs as $doc) {
            $this->test("Documentation: $doc", file_exists($doc));
        }
    }
    
    // ===== Generate Report =====
    public function generate_report() {
        echo "\n\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║           SYSTEM TEST REPORT - OBOADE NYAME HARDWARES STORE              ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\nTest Summary:\n";
        echo "  Passed: " . $this->passed . "\n";
        echo "  Failed: " . $this->failed . "\n";
        echo "  Total:  " . ($this->passed + $this->failed) . "\n";
        
        $total = $this->passed + $this->failed;
        $percentage = $total > 0 ? round(($this->passed / $total) * 100, 2) : 0;
        echo "  Pass Rate: " . $percentage . "%\n";
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "Detailed Results:\n";
        echo str_repeat("=", 60) . "\n";
        
        foreach ($this->tests as $test) {
            $status_symbol = $test['status'] === 'PASS' ? '✓' : '✗';
            $status_color = $test['status'] === 'PASS' ? "\033[92m" : "\033[91m";
            $reset = "\033[0m";
            
            echo $status_color . $status_symbol . " " . $reset;
            echo str_pad($test['name'], 40);
            echo " [" . $test['status'] . "]";
            if ($test['message']) {
                echo " - " . $test['message'];
            }
            echo "\n";
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
        
        if ($this->failed === 0) {
            echo "\033[92m✓ ALL TESTS PASSED - SYSTEM READY FOR DEPLOYMENT\033[0m\n";
        } else {
            echo "\033[91m✗ " . $this->failed . " TEST(S) FAILED - REVIEW REQUIRED\033[0m\n";
        }
        
        echo str_repeat("=", 60) . "\n\n";
    }
    
    public function run_all_tests() {
        echo "\n\033[96m╔════════════════════════════════════════════════════════════╗\033[0m\n";
        echo "\033[96m║         RUNNING COMPREHENSIVE SYSTEM TESTS                 ║\033[0m\n";
        echo "\033[96m╚════════════════════════════════════════════════════════════╝\033[0m\n";
        
        $this->test_files_exist();
        $this->test_database_structure();
        $this->test_data_integrity();
        $this->test_security();
        $this->test_performance();
        $this->test_functions();
        $this->test_configuration();
        
        $this->generate_report();
    }
}

// Run tests
$tester = new SystemTest();
$tester->run_all_tests();
?>
