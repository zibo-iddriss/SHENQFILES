<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OBOADE NYAME HARDWARES Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-wrapper">
<div class="stars"></div>
<div class="login-container">
<div class="login-header">
<div class="logo-icon">
<i class="fas fa-tools"></i>
</div>
<h1>OBOADE NYAME HARDWARES</h1>
<p>Store Management System</p>
</div>
<form method="POST" class="login-form">
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-user"></i>
<input type="text" name="username" placeholder="Username" required>
</div>
</div>
<div class="form-group">
<div class="input-wrapper">
<i class="fas fa-lock"></i>
<input type="password" name="password" placeholder="Password" required>
</div>
</div>
<button type="submit" name="login" class="login-btn">
<span>Sign In</span>
<i class="fas fa-arrow-right"></i>
</button>
</form>
<div class="login-message">
<?php
if(isset($_POST['login'])){
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  
  // Validate input
  if(empty($username) || empty($password)){
    echo "<span class='error'><i class='fas fa-exclamation-circle'></i> Username and password are required</span>";
  } else {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows > 0){
      $user = $res->fetch_assoc();
      $_SESSION['role'] = $user['role'];
      $_SESSION['username'] = $user['username'];
      header("Location: dashboard.php");
      exit;
    } else {
      echo "<span class='error'><i class='fas fa-exclamation-circle'></i> Invalid credentials</span>";
    }
  }
}
?>
</div>
<div class="login-footer">
<p>🇬🇭 Ghana Hardware Solutions</p>
</div>
</div>
</div>
</body>
</html>
