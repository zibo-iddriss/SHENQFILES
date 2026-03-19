<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Record Sale - OBOADE NYAME HARDWARES</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar">
<div class="nav-container">
<div class="nav-brand">
<i class="fas fa-hammer"></i>
<span>OBOADE NYAME HARDWARES</span>
<small>🇬🇭</small>
</div>
<div class="nav-links">
<a href='dashboard.php' class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a>
<a href='products.php' class="nav-link"><i class="fas fa-box"></i> Products</a>
<a href='sales.php' class="nav-link active"><i class="fas fa-shopping-cart"></i> Sales</a>
<?php if($_SESSION['role'] == 'admin'): ?>
<a href='users.php' class="nav-link"><i class="fas fa-users-cog"></i> Users</a>
<?php endif; ?>
<a href='logout.php' class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</nav>

<div class="dashboard-wrapper">
<div class="dashboard-header">
<h1>Sales Management</h1>
<p>Record and monitor product sales</p>
</div>

<div class="form-container">
<form method="POST" class="sales-form">
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-user"></i>
<input type="text" name="customer_name" placeholder="Customer Name (Optional)">
</div>
</div>
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-barcode"></i>
<input type="number" name="product_id" placeholder="Enter Product ID" required>
</div>
</div>
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-cubes"></i>
<input type="number" name="qty" placeholder="Quantity Sold" required>
</div>
</div>
<button type="submit" name="sell" class="primary-btn">
<i class="fas fa-credit-card"></i>
<span>Process Sale</span>
</button>
</form>
</div>

<?php
if(isset($_POST['sell'])){
  $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
  $quantity = isset($_POST['qty']) ? intval($_POST['qty']) : 0;
  $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
  
  // Validate input
  if($product_id <= 0 || $quantity <= 0){
    echo "<div class='error-container'><div class='error-card'><i class='fas fa-exclamation-circle'></i><h3>Invalid Input</h3><p>Please enter valid product ID and quantity.</p></div></div>";
  } else {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0) {
      $p = $result->fetch_assoc();
      $total = $p['price'] * $quantity;
      
      // Insert sale
      $insert_stmt = $conn->prepare("INSERT INTO sales(product_id, quantity_sold, total_price) VALUES(?, ?, ?)");
      $insert_stmt->bind_param("iid", $product_id, $quantity, $total);
      $insert_stmt->execute();
      
      // Update product quantity
      $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
      $update_stmt->bind_param("ii", $quantity, $product_id);
      $update_stmt->execute();
      
      // Get updated quantity
      $check_stmt = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
      $check_stmt->bind_param("i", $product_id);
      $check_stmt->execute();
      $updated_product = $check_stmt->get_result()->fetch_assoc();
      $remaining_qty = $updated_product['quantity'];
      ?>
      <div class="receipt-container">
        <div class="receipt-card">
          <div class="receipt-header">
            <i class="fas fa-receipt"></i>
            <h2>Sale Receipt</h2>
          </div>
          <div class="receipt-details">
            <?php if($customer_name): ?>
            <div class="receipt-item">
              <span class="label">Customer Name:</span>
              <span class="value"><?php echo htmlspecialchars($customer_name); ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-item">
              <span class="label">Receipt Date & Time:</span>
              <span class="value"><?php echo date('M d, Y - H:i:s'); ?></span>
            </div>
            <div class="receipt-item">
              <span class="label">Product:</span>
              <span class="value"><?php echo htmlspecialchars($p['name']); ?></span>
            </div>
            <div class="receipt-item">
              <span class="label">Unit Price:</span>
              <span class="value">₵ <?php echo number_format($p['price'], 2); ?></span>
            </div>
            <div class="receipt-item">
              <span class="label">Quantity:</span>
              <span class="value"><?php echo $quantity; ?></span>
            </div>
            <div class="receipt-item total">
              <span class="label">Total Amount:</span>
              <span class="value">₵ <?php echo number_format($total, 2); ?></span>
            </div>
          </div>
          <button type="button" onclick="window.print()" class="print-btn">
            <i class="fas fa-print"></i>
            <span>Print Receipt</span>
          </button>
        </div>
      </div>
      <?php
    } else {
      ?>
      <div class="error-container">
        <div class="error-card">
          <i class="fas fa-exclamation-circle"></i>
          <h3>Product Not Found</h3>
          <p>The product ID <?php echo htmlspecialchars($product_id); ?> does not exist. Please check and try again.</p>
        </div>
      </div>
      <?php
    }
  }
}
?>
</div>
</body>
</html>
