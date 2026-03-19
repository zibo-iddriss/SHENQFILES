<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    die("<div style='text-align: center; padding: 40px;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px; border-radius: 20px; max-width: 400px; margin: 0 auto;'>
            <i style='font-size: 60px; color: white; display: block; margin-bottom: 20px;' class='fas fa-lock'></i>
            <h1 style='color: white; font-family: Arial, sans-serif; margin-bottom: 10px;'>Access Denied</h1>
            <p style='color: rgba(255,255,255,0.9); font-family: Arial, sans-serif; margin-bottom: 30px;'>Only administrators can access this page.</p>
            <a href='dashboard.php' style='background: white; color: #667eea; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block;'>
                <i class='fas fa-arrow-left'></i> Back to Dashboard
            </a>
        </div>
    </div>");
    exit();
}

// Handle password change for admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_admin_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "All fields are required!";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ? AND role = 'admin'");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['password'] === $current_password) {
            if ($new_password === $confirm_password) {
                // Update password
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                $update_stmt->bind_param("ss", $new_password, $_SESSION['username']);
                
                if ($update_stmt->execute()) {
                    $success_msg = "Your password has been changed successfully!";
                    header("Refresh: 2; url=users.php");
                } else {
                    $error_msg = "Failed to update password. Please try again.";
                }
            } else {
                $error_msg = "New passwords do not match!";
            }
        } else {
            $error_msg = "Current password is incorrect!";
        }
    }
}

// Handle password change for cashier (by admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_cashier_password'])) {
    $cashier_id = intval($_POST['cashier_id']);
    $new_password = trim($_POST['cashier_new_password']);
    $confirm_password = trim($_POST['cashier_confirm_password']);

    if (empty($cashier_id) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "All fields are required!";
    } else if ($new_password === $confirm_password) {
        // Update cashier password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'cashier'");
        $stmt->bind_param("si", $new_password, $cashier_id);
        
        if ($stmt->execute()) {
            $success_msg = "Cashier password has been reset successfully!";
        } else {
            $error_msg = "Failed to update cashier password. Please try again.";
        }
    } else {
        $error_msg = "Passwords do not match!";
    }
}

// Get all users
$users_result = $conn->query("SELECT * FROM users ORDER BY role DESC, username");
$all_users = [];
while ($row = $users_result->fetch_assoc()) {
    $all_users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - OBOADE NYAME HARDWARES</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .users-container {
            padding: 40px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .users-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .users-header h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .users-header p {
            color: #666;
            font-size: 1.1em;
        }

        .section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .section-title {
            font-size: 1.5em;
            color: #667eea;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #333;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 1.05em;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.2);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.3);
        }

        .success-message {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: #2d5016;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        .error-message {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: #7c2d12;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .users-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .users-table th {
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        .users-table td {
            padding: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .users-table tbody tr:hover {
            background: #f9f9f9;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }

        .role-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .role-cashier {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: scaleIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            color: #333;
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #999;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            color: #333;
            transform: rotate(90deg);
        }

        .reset-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .reset-btn:hover {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .users-container {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .users-table {
                font-size: 0.9em;
            }

            .users-table th,
            .users-table td {
                padding: 10px;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
<div class="users-container">
    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>

    <div class="users-header">
        <h1>
            <i class="fas fa-users-cog"></i>
            User Management
        </h1>
        <p>Manage admin password and cashier credentials</p>
    </div>

    <?php if(isset($success_msg)): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($error_msg)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- Change Admin Password Section -->
    <div class="section">
        <div class="section-title">
            <i class="fas fa-key"></i>
            Change Your Password
        </div>
        <form method="POST">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <div class="button-group">
                <button type="submit" name="change_admin_password" class="btn btn-primary">
                    <i class="fas fa-lock"></i> Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Set Cashier Password Section -->
    <div class="section">
        <div class="section-title">
            <i class="fas fa-user-tie"></i>
            Manage Cashier Passwords
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_users as $user): ?>
                    <?php if($user['role'] == 'cashier'): ?>
                        <tr>
                            <td>
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?>
                            </td>
                            <td>
                                <span class="role-badge role-cashier">
                                    <i class="fas fa-cash-register"></i> Cashier
                                </span>
                            </td>
                            <td><?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?></td>
                            <td>
                                <button type="button" class="reset-btn" onclick="openResetModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class="fas fa-sync-alt"></i> Reset Password
                                </button>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php $cashier_count = count(array_filter($all_users, fn($u) => $u['role'] == 'cashier')); ?>
        <?php if($cashier_count == 0): ?>
            <p style="text-align: center; color: #999; margin-top: 20px;">
                <i class="fas fa-info-circle"></i> No cashier accounts found.
            </p>
        <?php endif; ?>
    </div>

    <!-- All Users Overview -->
    <div class="section">
        <div class="section-title">
            <i class="fas fa-list"></i>
            All Users
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Password</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_users as $user): ?>
                    <tr>
                        <td>
                            <i class="fas fa-<?php echo $user['role'] == 'admin' ? 'shield-alt' : 'user'; ?>"></i>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-family: monospace;">
                                <?php echo str_repeat('•', strlen($user['password'])); ?>
                            </code>
                        </td>
                        <td><?php echo isset($user['created_at']) ? date('M d, Y H:i', strtotime($user['created_at'])) : 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Password Reset Modal -->
<div id="resetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Reset Cashier Password</h2>
            <button type="button" class="close-btn" onclick="closeResetModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" id="cashier_id" name="cashier_id">
            
            <div class="form-group">
                <label>Cashier</label>
                <input type="text" id="cashier_name" disabled style="background: #f0f0f0;">
            </div>

            <div class="form-group">
                <label for="cashier_new_password">New Password</label>
                <input type="password" id="cashier_new_password" name="cashier_new_password" required>
            </div>

            <div class="form-group">
                <label for="cashier_confirm_password">Confirm Password</label>
                <input type="password" id="cashier_confirm_password" name="cashier_confirm_password" required>
            </div>

            <div class="button-group">
                <button type="button" onclick="closeResetModal()" class="btn btn-secondary" style="background: #ccc;">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" name="change_cashier_password" class="btn btn-primary">
                    <i class="fas fa-check"></i> Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(cashierId, cashierName) {
    document.getElementById('cashier_id').value = cashierId;
    document.getElementById('cashier_name').value = cashierName;
    document.getElementById('resetModal').classList.add('active');
}

function closeResetModal() {
    document.getElementById('resetModal').classList.remove('active');
    document.getElementById('cashier_new_password').value = '';
    document.getElementById('cashier_confirm_password').value = '';
}

// Close modal when clicking outside
document.getElementById('resetModal').addEventListener('click', function(e) {
    if(e.target === this) {
        closeResetModal();
    }
});
</script>

</body>
</html>
