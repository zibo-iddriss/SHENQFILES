<?php include 'config.php'; 
if($_SESSION['role']!='admin'){ 
  ?>
  <!DOCTYPE html>
  <html>
  <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Access Denied - OBOADE NYAME HARDWARES</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  </head>
  <body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh;">
  <div class="access-denied-container">
    <div class="access-denied-card">
      <div class="denied-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h1>Access Denied</h1>
      <p class="denied-message">You don't have permission to access this page.</p>
      <p class="denied-role">Your role: <strong><?php echo htmlspecialchars($_SESSION['role']); ?></strong></p>
      <p class="denied-note">Only administrators can access the Product Management section.</p>
      <div class="denied-actions">
        <a href="dashboard.php" class="denied-btn primary">
          <i class="fas fa-arrow-left"></i> Go to Dashboard
        </a>
        <a href="logout.php" class="denied-btn secondary">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </div>
  </body>
  </html>
  <?php
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - OBOADE NYAME HARDWARES</title>
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
<a href='products.php' class="nav-link active"><i class="fas fa-box"></i> Products</a>
<a href='sales.php' class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a>
<a href='users.php' class="nav-link"><i class="fas fa-users-cog"></i> Users</a>
<a href='logout.php' class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</nav>

<div class="dashboard-wrapper">
<div class="dashboard-header">
<h1>Product Management</h1>
<p>Add and manage hardware products</p>
</div>

<?php
// Display success messages
if(isset($_GET['success'])){
  if($_GET['success'] == 'stock_updated'){
    echo "<div class='success-message'><i class='fas fa-check-circle'></i> Stock updated successfully!</div>";
  } elseif($_GET['success'] == 'product_deleted'){
    echo "<div class='success-message'><i class='fas fa-trash'></i> Product deleted successfully!</div>";
  } elseif($_GET['success'] == 'product_added'){
    echo "<div class='success-message'><i class='fas fa-check-circle'></i> Product added successfully!</div>";
  }
}
?>

<?php
// Get low stock products (quantity < 10)
$low_stock_result = $conn->query("SELECT * FROM products WHERE quantity < 10 ORDER BY quantity ASC");
$low_stock_count = $low_stock_result->num_rows;

// Get inventory stats
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
$total_inventory_value = $conn->query("SELECT SUM(quantity * price) as total FROM products")->fetch_assoc();
$categories = $conn->query("SELECT COUNT(DISTINCT category) as count FROM products")->fetch_assoc();
?>

<!-- Low Stock Alerts -->
<?php if($low_stock_count > 0): ?>
<div class="alert-section">
<div class="alert-card alert-warning">
<div class="alert-header">
<i class="fas fa-exclamation-triangle"></i>
<h3>Low Stock Alert</h3>
<span class="alert-badge"><?php echo $low_stock_count; ?></span>
</div>
<div class="alert-items">
<?php
while($item = $low_stock_result->fetch_assoc()):
?>
<div class="alert-item">
<div class="alert-item-name"><?php echo $item['name']; ?></div>
<div class="alert-item-details">
<span class="stock-indicator low">Qty: <?php echo $item['quantity']; ?></span>
<span class="category-mini"><?php echo $item['category']; ?></span>
</div>
</div>
<?php endwhile; ?>
</div>
</div>
</div>
<?php endif; ?>

<!-- Reports Section -->
<div class="reports-section">
<h2><i class="fas fa-chart-bar"></i> Inventory Reports</h2>
<div class="reports-grid">
<div class="report-card">
<div class="report-icon products-icon">
<i class="fas fa-box"></i>
</div>
<div class="report-content">
<h4>Total Products</h4>
<p class="report-value"><?php echo $total_products['count']; ?></p>
<span class="report-label">Product Types</span>
</div>
</div>

<div class="report-card">
<div class="report-icon revenue-icon">
<i class="fas fa-coins"></i>
</div>
<div class="report-content">
<h4>Inventory Value</h4>
<p class="report-value">₵ <?php echo number_format($total_inventory_value['total'], 2); ?></p>
<span class="report-label">Total Worth</span>
</div>
</div>

<div class="report-card">
<div class="report-icon sales-icon">
<i class="fas fa-list"></i>
</div>
<div class="report-content">
<h4>Categories</h4>
<p class="report-value"><?php echo $categories['count']; ?></p>
<span class="report-label">Product Categories</span>
</div>
</div>

<div class="report-card">
<div class="report-icon warning-icon">
<i class="fas fa-warning"></i>
</div>
<div class="report-content">
<h4>Low Stock Items</h4>
<p class="report-value"><?php echo $low_stock_count; ?></p>
<span class="report-label">Need Reorder</span>
</div>
</div>
</div>
</div>

<div class="products-container">
<div class="form-section">
<h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
<form method="POST" class="product-form">
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-tag"></i>
<input type="text" name="name" placeholder="Product Name" required>
</div>
</div>
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-list"></i>
<input type="text" name="category" placeholder="Category (e.g Cement, Tools)" required>
</div>
</div>
<div class="form-row">
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-cubes"></i>
<input type="number" name="quantity" placeholder="Quantity" required>
</div>
</div>
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-coins"></i>
<input type="number" step="0.01" name="price" placeholder="Price" required>
</div>
</div>
</div>
<button type="submit" name="add" class="primary-btn">
<i class="fas fa-plus"></i>
<span>Add Product</span>
</button>
</form>
<?php
if(isset($_POST['add'])){
  $name = isset($_POST['name']) ? trim($_POST['name']) : '';
  $category = isset($_POST['category']) ? trim($_POST['category']) : '';
  $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
  $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
  
  // Validate input
  if(empty($name) || empty($category) || $quantity < 0 || $price < 0){
    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Please fill in all fields correctly</div>";
  } else {
    // Use prepared statement
    $stmt = $conn->prepare("INSERT INTO products(name, category, quantity, price) VALUES(?, ?, ?, ?)");
    $stmt->bind_param("ssid", $name, $category, $quantity, $price);
    
    if($stmt->execute()){
      header("Location: products.php?success=product_added");
      exit;
    } else {
      echo "<div class='error-message'><i class='fas fa-times-circle'></i> Error adding product</div>";
    }
  }
}

// Handle stock update
if(isset($_POST['update_stock'])){
  $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
  $new_quantity = isset($_POST['new_quantity']) ? intval($_POST['new_quantity']) : 0;
  
  // Validate input
  if($product_id <= 0 || $new_quantity < 0){
    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Invalid input</div>";
  } else {
    // Use prepared statement
    $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $product_id);
    
    if($stmt->execute()){
      header("Location: products.php?success=stock_updated");
      exit;
    } else {
      echo "<div class='error-message'><i class='fas fa-times-circle'></i> Error updating stock</div>";
    }
  }
}

// Handle product deletion
if(isset($_POST['delete_product'])){
  $product_id = isset($_POST['delete_product']) ? intval($_POST['delete_product']) : 0;
  
  // Validate input
  if($product_id <= 0){
    echo "<div class='error-message'><i class='fas fa-exclamation-circle'></i> Invalid product</div>";
  } else {
    // Use prepared statement
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    
    if($stmt->execute()){
      header("Location: products.php?success=product_deleted");
      exit;
    } else {
      echo "<div class='error-message'><i class='fas fa-times-circle'></i> Error deleting product</div>";
    }
  }
}
?>
</div>

<div class="products-list-section">
<h2><i class="fas fa-warehouse"></i> Products Inventory</h2>
<div class="products-table-wrapper">
<table class="products-table">
<thead>
<tr>
<th><i class="fas fa-hashtag"></i> ID</th>
<th><i class="fas fa-box"></i> Product Name</th>
<th><i class="fas fa-list"></i> Category</th>
<th><i class="fas fa-cubes"></i> Quantity</th>
<th><i class="fas fa-tag"></i> Price</th>
<th><i class="fas fa-clock"></i> Updated</th>
<th><i class="fas fa-info-circle"></i> Status</th>
<th><i class="fas fa-cog"></i> Actions</th>
</tr>
</thead>
<tbody>
<?php
$res=$conn->query("SELECT * FROM products ORDER BY id DESC");
if($res->num_rows > 0) {
  while($row=$res->fetch_assoc()){
    $status_class = $row['quantity'] < 10 ? 'low' : 'good';
    $status_text = $row['quantity'] < 10 ? 'Low Stock' : 'In Stock';
    $status_icon = $row['quantity'] < 10 ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
    
    echo "<tr>";
    echo "<td><span class='id-badge'>".$row['id']."</span></td>";
    echo "<td><strong>".$row['name']."</strong></td>";
    echo "<td><span class='category-badge'>".$row['category']."</span></td>";
    echo "<td><span class='qty-badge'>".$row['quantity']."</span></td>";
    echo "<td><span class='price-badge'>₵ ".number_format($row['price'],2)."</span></td>";
    echo "<td><small style='color: #999;'>".(isset($row['created_at']) && $row['created_at'] ? date('M d, Y', strtotime($row['created_at'])) : 'N/A')."</small></td>";
    echo "<td><span class='status-badge status-".$status_class."'><i class='".$status_icon."'></i> ".$status_text."</span></td>";
    echo "<td>";
    echo "<div class='action-buttons'>";
    echo "<button class='action-btn edit-btn' onclick='openEditStock(".$row['id'].", \"".$row['name']."\", ".$row['quantity']."); return false;'><i class='fas fa-edit'></i> Update</button>";
    echo "<button class='action-btn delete-btn' onclick='confirmDelete(".$row['id'].", \"".addslashes($row['name'])."\"); return false;'><i class='fas fa-trash'></i> Delete</button>";
    echo "</div>";
    echo "</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='8' style='text-align:center; padding: 30px; color: #999;'><i class='fas fa-inbox'></i> No products yet</td></tr>";
}
?>
</tbody>
</tbody>
</table>
</div>
</div>
</div>

<!-- Edit Stock Modal -->
<div id="editStockModal" class="modal">
<div class="modal-content">
<div class="modal-header">
<h2><i class="fas fa-edit"></i> Update Stock Level</h2>
<button class="modal-close" onclick="closeEditStock()">&times;</button>
</div>
<form method="POST" class="modal-form">
<div class="form-group">
<label>Product Name</label>
<input type="text" id="productName" disabled class="modal-input">
</div>
<div class="form-group">
<label>New Quantity</label>
<div class="input-wrapper">
<i class="fas fa-cubes"></i>
<input type="number" id="newQuantity" name="new_quantity" placeholder="Enter new quantity" class="modal-input-with-icon" required>
</div>
</div>
<input type="hidden" id="productIdUpdate" name="product_id">
<div class="modal-actions">
<button type="submit" name="update_stock" class="modal-btn primary">
<i class="fas fa-check"></i> Update Stock
</button>
<button type="button" class="modal-btn secondary" onclick="closeEditStock()">
<i class="fas fa-times"></i> Cancel
</button>
</div>
</form>
</div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
<div class="modal-content modal-danger">
<div class="modal-header">
<h2><i class="fas fa-exclamation-triangle"></i> Delete Product</h2>
<button class="modal-close" onclick="closeDelete()">&times;</button>
</div>
<div class="modal-body">
<p class="danger-message">Are you sure you want to delete <strong id="deleteProductName"></strong>?</p>
<p class="danger-note">This action cannot be undone. All sales records for this product will remain in the system.</p>
</div>
<form method="POST" class="modal-form">
<input type="hidden" id="deleteProductId" name="delete_product">
<div class="modal-actions">
<button type="submit" class="modal-btn danger">
<i class="fas fa-trash"></i> Delete Product
</button>
<button type="button" class="modal-btn secondary" onclick="closeDelete()">
<i class="fas fa-times"></i> Cancel
</button>
</div>
</form>
</div>
</div>

<script>
function openEditStock(productId, productName, quantity) {
  document.getElementById('productIdUpdate').value = productId;
  document.getElementById('productName').value = productName;
  document.getElementById('newQuantity').value = quantity;
  document.getElementById('editStockModal').classList.add('active');
}

function closeEditStock() {
  document.getElementById('editStockModal').classList.remove('active');
}

function confirmDelete(productId, productName) {
  document.getElementById('deleteProductId').value = productId;
  document.getElementById('deleteProductName').textContent = productName;
  document.getElementById('deleteModal').classList.add('active');
}

function closeDelete() {
  document.getElementById('deleteModal').classList.remove('active');
}

// Close modals when clicking outside
window.onclick = function(event) {
  let editModal = document.getElementById('editStockModal');
  let deleteModal = document.getElementById('deleteModal');
  if (event.target == editModal) {
    editModal.classList.remove('active');
  }
  if (event.target == deleteModal) {
    deleteModal.classList.remove('active');
  }
}
</script>
</body>
</html>
