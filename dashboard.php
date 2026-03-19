<?php include 'config.php'; if(!isset($_SESSION['role'])){header("Location: login.php");} ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - OBOADE NYAME HARDWARES</title>
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
<a href='dashboard.php' class="nav-link active"><i class="fas fa-chart-line"></i> Dashboard</a>
<a href='products.php' class="nav-link"><i class="fas fa-box"></i> Products</a>
<a href='sales.php' class="nav-link"><i class="fas fa-shopping-cart"></i> Sales</a>
<?php if($_SESSION['role'] == 'admin'): ?>
<a href='users.php' class="nav-link"><i class="fas fa-users-cog"></i> Users</a>
<?php endif; ?>
<a href='logout.php' class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</nav>

<div class="dashboard-wrapper">
<div class="dashboard-header">
<h1>Dashboard</h1>
<p>Welcome back to OBOADE NYAME HARDWARES</p>
<div id="live-clock" style="font-size: 24px; color: #667eea; font-weight: bold; margin-top: 15px; font-family: 'Courier New', monospace;">
<i class="fas fa-clock"></i> <span id="clock-display">Loading...</span>
</div>
</div>

<div class="stats-grid">
<?php
$p=$conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc();
$s=$conn->query("SELECT SUM(total_price) as revenue FROM sales")->fetch_assoc();
$sales_count=$conn->query("SELECT COUNT(*) as count FROM sales")->fetch_assoc();
?>
<div class="stat-card">
<div class="stat-icon products-icon">
<i class="fas fa-box"></i>
</div>
<div class="stat-content">
<h3>Total Products</h3>
<p class="stat-value"><?php echo $p['total']; ?></p>
<span class="stat-label">In Stock</span>
</div>
</div>

<?php if($_SESSION['role'] == 'admin'): ?>
<div class="stat-card">
<div class="stat-icon revenue-icon">
<i class="fas fa-chart-line"></i>
</div>
<div class="stat-content">
<h3>Total Revenue</h3>
<p class="stat-value">₵ <?php echo number_format($s['revenue'],2); ?></p>
<span class="stat-label">All Time</span>
</div>
</div>
<?php endif; ?>

<div class="stat-card">
<div class="stat-icon sales-icon">
<i class="fas fa-shopping-cart"></i>
</div>
<div class="stat-content">
<h3>Total Sales</h3>
<p class="stat-value"><?php echo $sales_count['count']; ?></p>
<span class="stat-label">Transactions</span>
</div>
</div>
</div>
</div>

<script>
function updateClock() {
  const now = new Date();
  const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
  const timeString = now.toLocaleDateString('en-US', options);
  document.getElementById('clock-display').textContent = timeString;
}

// Update clock immediately
updateClock();

// Update clock every second
setInterval(updateClock, 1000);
</script>
</body>
</html>
