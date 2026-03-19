# Installation Guide

Complete step-by-step guide to install and configure OBOADE NYAME HARDWARES Store.

## 📋 Prerequisites

Before installing, ensure you have:

1. **Web Server**
   - Apache 2.4+ with mod_rewrite enabled
   - OR Nginx 1.18+

2. **PHP**
   - PHP 7.4 or higher (PHP 8.0+ recommended)
   - Required extensions: mysqli, gzip/zlib, sessions

3. **Database**
   - MySQL 5.7+ or MariaDB 10.3+

4. **System**
   - 100MB free disk space
   - 512MB RAM minimum
   - Network connectivity for CDN resources (Font Awesome)

### Check Your System

```bash
# Check PHP version
php -v

# Check MySQL version
mysql -V

# Check Apache modules
apache2ctl -M | grep rewrite
```

---

## 🔧 Installation Steps

### Step 1: Download and Extract Files

```bash
# Download Omanbapa
# Extract to your web server directory

# Linux/Mac
tar -xzf omanbapa.tar.gz
mv omanbapa /var/www/html/

# Windows
# Extract zip file to C:\xampp\htdocs\
```

### Step 2: Set File Permissions

```bash
# Linux/Mac
cd /var/www/html/omanbapa

# Set file permissions
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Set special permissions
chmod 700 config.php
chmod 755 backups logs cache
chmod 644 .htaccess

# Check
ls -la
```

### Step 3: Create Database

#### Using Command Line:

```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE omanbapa_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create database user (recommended, instead of using root)
CREATE USER 'omanbapa_user'@'localhost' IDENTIFIED BY 'secure_password_here';

# Grant permissions
GRANT ALL PRIVILEGES ON omanbapa_store.* TO 'omanbapa_user'@'localhost';
FLUSH PRIVILEGES;

# Exit
EXIT;
```

#### Using phpMyAdmin (XAMPP):

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" on left sidebar
3. Enter database name: `omanbapa_store`
4. Set collation: `utf8mb4_unicode_ci`
5. Click "Create"

### Step 4: Create Database Tables

#### Using Command Line:

```bash
# Create tables from file
mysql -u omanbapa_user -p omanbapa_store < schema.sql

# Or manually (see below)
```

#### Manual Creation:

```bash
mysql -u root -p omanbapa_store
```

Then run these SQL queries:

```sql
-- Users Table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'cashier') DEFAULT 'cashier',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products Table
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(50),
  quantity INT NOT NULL DEFAULT 0,
  price DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sales Table
CREATE TABLE sales (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  quantity_sold INT NOT NULL,
  total_price DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Indexes for better performance
CREATE INDEX idx_user_username ON users(username);
CREATE INDEX idx_product_category ON products(category);
CREATE INDEX idx_sales_product ON sales(product_id);
CREATE INDEX idx_sales_date ON sales(created_at);
```

### Step 5: Insert Initial Data

```sql
-- Insert admin user (CHANGE PASSWORD!)
INSERT INTO users (username, password, role) VALUES 
('admin', 'admin123', 'admin');

-- Insert test cashier user
INSERT INTO users (username, password, role) VALUES 
('cashier1', 'cashier123', 'cashier');

-- Insert sample products
INSERT INTO products (name, category, quantity, price) VALUES 
('Hammer', 'Tools', 50, 25.00),
('Nails (Box)', 'Hardware', 200, 5.00),
('Wood Saw', 'Tools', 30, 35.00),
('Paint (1L)', 'Paint', 100, 15.00),
('Paint Brush', 'Tools', 75, 8.00),
('Screwdriver Set', 'Tools', 40, 20.00),
('Sandpaper (Pack)', 'Hardware', 150, 3.50),
('Drill Bits Set', 'Tools', 25, 45.00),
('Safety Helmet', 'Safety', 60, 12.00),
('Work Gloves (Pair)', 'Safety', 200, 6.00);
```

### Step 6: Configure Application

#### Create config.php

```php
<?php
// config.php
$host = 'localhost';
$user = 'omanbapa_user';  // Change to your database user
$password = 'secure_password_here';  // Change to your database password
$database = 'omanbapa_store';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
?>
```

#### Create env.php (from env.example.php)

```bash
cp env.example.php env.php
```

Open `env.php` and update:
- `$environment = 'production'` (for live server)
- Database credentials
- Email configuration
- Security settings

#### Create .htaccess (from .htaccess example)

```bash
cp .htaccess.example .htaccess
```

Then enable HTTPS:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Step 7: Create Required Directories

```bash
# Create backups directory
mkdir -p backups
chmod 700 backups

# Create logs directory
mkdir -p logs
chmod 700 logs

# Create cache directory (optional)
mkdir -p cache
chmod 755 cache
```

### Step 8: Configure Web Server

#### Apache (.htaccess):

The `.htaccess` file is already configured. Make sure mod_rewrite is enabled:

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2
```

#### Nginx:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/omanbapa;

    index login.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## ✅ Verification

### Test Installation

```bash
# Open browser and navigate to:
# Local: http://localhost/omanbapa/login.php
# Remote: https://yourdomain.com/login.php

# You should see the login page
```

### Test Database Connection

Create a test file:

```php
<?php
// test.php
require_once 'config.php';

echo "✓ PHP version: " . phpversion() . "\n";
echo "✓ MySQL version: " . $conn->server_info . "\n";

// Test query
$result = $conn->query("SELECT COUNT(*) as count FROM products");
$row = $result->fetch_assoc();
echo "✓ Products in database: " . $row['count'] . "\n";

echo "\n✓ Installation successful!";
?>
```

Access: `http://localhost/omanbapa/test.php`

### Test Login

Use credentials:
- Username: `admin`
- Password: `admin123`

⚠️ **Change these default credentials immediately!**

---

## 🔐 Security Configuration

### Change Default Passwords

```sql
-- Update admin password
UPDATE users SET password = 'your_new_secure_password' WHERE username = 'admin';

-- Update cashier password
UPDATE users SET password = 'cashier_new_secure_password' WHERE username = 'cashier1';
```

### Enable HTTPS

1. Obtain SSL certificate:
   ```bash
   # Using Let's Encrypt (free)
   sudo certbot certonly --apache -d yourdomain.com
   ```

2. Enable in .htaccess:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. Update config.php for sessions:
   ```php
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_samesite', 'Strict');
   ```

### Set PHP Configuration

Edit `php.ini`:

```ini
; Disable error display (production)
display_errors = Off
log_errors = On
error_log = /var/log/php-errors.log

; Session security
session.cookie_httponly = On
session.cookie_secure = On
session.use_only_cookies = On
session.name = OMANBAPASESSID

; File uploads
upload_max_filesize = 2M
post_max_size = 2M

; Execution
max_execution_time = 30
memory_limit = 256M
```

---

## 📊 Database Maintenance

### Schedule Automatic Backups

#### Linux (using cron):

```bash
# Edit crontab
crontab -e

# Add this line for daily backups at 2 AM
0 2 * * * cd /var/www/omanbapa && php backup_database.php backup
```

#### Windows (using Task Scheduler):

1. Open Task Scheduler
2. Create Basic Task
3. Set trigger: Daily at 2:00 AM
4. Set action: 
   - Program: `C:\php\php.exe`
   - Arguments: `C:\xampp\htdocs\omanbapa\backup_database.php`

### Manual Backup

```bash
# Create backup
php backup_database.php backup

# List backups
php backup_database.php list

# Restore from backup
php backup_database.php restore backup_filename.sql.gz
```

---

## 🚀 Post-Installation

### 1. Test All Features

- [ ] Login with admin credentials
- [ ] View dashboard - all stats should show
- [ ] View products - inventory should display
- [ ] Process a sale - receipt should show stock remaining
- [ ] Add/update a product - low stock alert should work

### 2. Configure Email (Optional)

For notifications, update email settings in `env.php`:

```php
$email_config = [
    'driver'    => 'smtp',
    'host'      => 'smtp.gmail.com',
    'port'      => 587,
    'username'  => 'your_email@gmail.com',
    'password'  => 'app_password_here',
    'encryption'=> 'tls'
];
```

### 3. Set Up Monitoring

- [ ] Configure error logging
- [ ] Set up backup notifications
- [ ] Enable access logging
- [ ] Set up SMS alerts for critical issues

### 4. Train Users

- [ ] Provide login credentials
- [ ] Explain admin functions (products.php)
- [ ] Train cashiers (sales.php)
- [ ] Review low stock alerts process

### 5. Do Final Security Check

- [ ] Verify HTTPS is working
- [ ] Check for debug info in code
- [ ] Verify backups are running
- [ ] Test login with wrong credentials
- [ ] Verify access control (cashier can't access admin)

---

## 🆘 Troubleshooting

If you encounter issues during installation:

1. **Check logs:**
   ```bash
   tail -f /var/log/php-errors.log
   tail -f /var/log/apache2/error.log
   ```

2. **Test database connection:**
   - Use phpMyAdmin to verify database and tables exist
   - Check database user permissions

3. **Check file permissions:**
   - config.php should be 700
   - .htaccess should be 644
   - Directories should be 755

4. **See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for detailed solutions**

---

## 📚 Next Steps

After installation:

1. Read [README.md](README.md) - System overview
2. Read [SECURITY.md](SECURITY.md) - Security guidelines
3. Review [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) - Before going live
4. Read [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues

---

## ✅ Installation Checklist

- [ ] Downloaded and extracted files
- [ ] Set file permissions correctly
- [ ] Created database and user
- [ ] Created database tables
- [ ] Inserted initial data
- [ ] Configured config.php
- [ ] Created env.php from env.example.php
- [ ] Created required directories (backups, logs, cache)
- [ ] Configured web server (.htaccess or nginx)
- [ ] Tested database connection
- [ ] Tested login page loads
- [ ] Successfully logged in with admin account
- [ ] Changed default passwords
- [ ] Enabled HTTPS
- [ ] Set up automatic backups
- [ ] Trained users

---

**Congratulations! Your OBOADE NYAME HARDWARES Store is now installed and ready to use! 🎉**

For support, refer to [TROUBLESHOOTING.md](TROUBLESHOOTING.md) or contact your system administrator.

