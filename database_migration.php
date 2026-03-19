<?php
/**
 * Database Migration Script
 * Adds last_login column to users table
 */

require_once 'config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['run_migration'])){
    try {
        // Add last_login column if it doesn't exist
        $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
        
        if($check_column->num_rows == 0) {
            // Column doesn't exist, add it
            $migration_sql = "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER created_at";
            
            if($conn->query($migration_sql)) {
                $success_msg = "✓ Successfully added 'last_login' column to users table!";
            } else {
                $error_msg = "Failed to add column: " . $conn->error;
            }
        } else {
            $success_msg = "✓ The 'last_login' column already exists in the users table.";
        }
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - OBOADE NYAME HARDWARES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .migration-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .migration-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
        }

        .migration-icon {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 20px;
        }

        .migration-title {
            font-size: 2em;
            color: #333;
            margin-bottom: 15px;
        }

        .migration-desc {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-msg {
            background: #d4edda;
            color: #155724;
        }

        .error-msg {
            background: #f8d7da;
            color: #721c24;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 25px;
            text-align: left;
            border-radius: 5px;
            color: #0c5aa0;
        }

        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }

        .info-box ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }

        .info-box li {
            margin-bottom: 5px;
        }

        .migration-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-size: 1.05em;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .migration-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .back-to-dashboard {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .back-to-dashboard:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
<div class="migration-container">
    <div class="migration-card">
        <div class="migration-icon">
            <i class="fas fa-database"></i>
        </div>
        <h1 class="migration-title">Database Migration</h1>
        <p class="migration-desc">Add login tracking to your system</p>

        <?php if(isset($success_msg)): ?>
            <div class="message success-msg">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if(isset($error_msg)): ?>
            <div class="message error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>This migration will:</strong>
            <ul>
                <li>Add a 'last_login' timestamp column to the users table</li>
                <li>Enable login tracking for all users</li>
                <li>Store the date and time of each user's last login</li>
                <li>Display this information in the User Management page</li>
            </ul>
        </div>

        <form method="POST">
            <button type="submit" name="run_migration" class="migration-btn">
                <i class="fas fa-arrow-right"></i> Run Migration
            </button>
        </form>

        <a href="dashboard.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>
</body>
</html>
