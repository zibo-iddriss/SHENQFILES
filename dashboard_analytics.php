<?php 
/**
 * Advanced Dashboard Analytics Module
 * Provides comprehensive business analytics and reporting
 * Include with: dashboard.php
 */

include 'config.php';
if(!isset($_SESSION['role'])){header("Location: login.php");exit;}

/**
 * Get Daily Sales Summary
 */
function get_daily_sales_summary() {
    global $conn;
    $query = "
        SELECT 
            DATE(sale_date) as sale_day,
            COUNT(*) as transactions,
            SUM(quantity_sold) as items_sold,
            SUM(total_price) as daily_revenue
        FROM sales
        WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(sale_date)
        ORDER BY sale_date DESC
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Get Top Selling Products
 */
function get_top_products($limit = 5) {
    global $conn;
    $query = "
        SELECT 
            p.id,
            p.name,
            p.category,
            SUM(s.quantity_sold) as total_sold,
            SUM(s.total_price) as total_revenue,
            COUNT(s.id) as sale_count
        FROM sales s
        JOIN products p ON s.product_id = p.id
        WHERE s.sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT $limit
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Get Category Performance
 */
function get_category_performance() {
    global $conn;
    $query = "
        SELECT 
            p.category,
            COUNT(DISTINCT p.id) as products,
            SUM(p.quantity) as total_stock,
            SUM(p.quantity * p.price) as stock_value,
            COALESCE(SUM(s.total_price), 0) as revenue
        FROM products p
        LEFT JOIN sales s ON p.id = s.product_id 
            AND s.sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY p.category
        ORDER BY revenue DESC
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Get Sales Trend (Last 30 days)
 */
function get_sales_trend() {
    global $conn;
    $query = "
        SELECT 
            DATE(sale_date) as date,
            COUNT(*) as sales_count,
            SUM(total_price) as revenue
        FROM sales
        WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(sale_date)
        ORDER BY date ASC
    ";
    $result = $conn->query($query);
    $dates = [];
    $sales = [];
    $revenue = [];
    
    while($row = $result->fetch_assoc()) {
        $dates[] = date('M d', strtotime($row['date']));
        $sales[] = $row['sales_count'];
        $revenue[] = round($row['revenue'], 2);
    }
    
    return ['dates' => $dates, 'sales' => $sales, 'revenue' => $revenue];
}

/**
 * Get Inventory Health
 */
function get_inventory_health() {
    global $conn;
    
    $health = [
        'critical' => 0,  // < 5 items
        'warning' => 0,   // 5-10 items
        'good' => 0       // > 10 items
    ];
    
    $result = $conn->query("SELECT quantity FROM products");
    while($row = $result->fetch_assoc()) {
        if($row['quantity'] < 5) {
            $health['critical']++;
        } elseif($row['quantity'] < 10) {
            $health['warning']++;
        } else {
            $health['good']++;
        }
    }
    
    return $health;
}

/**
 * Get Monthly Revenue Summary
 */
function get_monthly_summary() {
    global $conn;
    $query = "
        SELECT 
            YEAR(sale_date) as year,
            MONTH(sale_date) as month,
            COUNT(*) as transactions,
            SUM(quantity_sold) as items_sold,
            SUM(total_price) as revenue
        FROM sales
        WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY YEAR(sale_date), MONTH(sale_date)
        ORDER BY year DESC, month DESC
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $row['month_name'] = date('M Y', mktime(0, 0, 0, $row['month'], 1, $row['year']));
        $data[] = $row;
    }
    return $data;
}

/**
 * Get Low Stock Products
 */
function get_low_stock_products($threshold = 10) {
    global $conn;
    $query = "
        SELECT 
            id,
            name,
            category,
            quantity,
            price,
            quantity * price as stock_value
        FROM products
        WHERE quantity < $threshold
        ORDER BY quantity ASC
        LIMIT 10
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Get Customer Purchase Patterns
 */
function get_purchase_patterns() {
    global $conn;
    $query = "
        SELECT 
            DAYNAME(sale_date) as day_name,
            COUNT(*) as sales_count,
            AVG(total_price) as avg_sale
        FROM sales
        WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DAYNAME(sale_date), DAYOFWEEK(sale_date)
        ORDER BY DAYOFWEEK(sale_date)
    ";
    $result = $conn->query($query);
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Format currency
 */
function format_currency($amount) {
    return '₵ ' . number_format($amount, 2);
}

/**
 * Get percentage change
 */
function get_percentage_change($current, $previous) {
    if($previous == 0) return 0;
    return round(($current - $previous) / $previous * 100, 2);
}
?>
