# Quick Reference Guide

Fast answers to common questions and quick command reference.

## 🎯 Quick Links

- **Login Page:** `http://yourdomain.com/login.php`
- **Dashboard:** `http://yourdomain.com/dashboard.php`
- **Products:** `http://yourdomain.com/products.php` (admin only)
- **Sales:** `http://yourdomain.com/sales.php` (cashier)
- **phpMyAdmin:** `http://yourdomain.com/phpmyadmin`

---

## 👤 User Roles

### Admin
- Manage products (add, update, delete)
- View inventory reports
- View sales analytics
- Access products.php
- Can process sales

### Cashier
- Process customer sales
- View sales history
- Cannot access products.php
- Cannot delete or edit products

---

## 🔐 Default Login Credentials

| Username | Password | Role |
|----------|----------|------|
| admin | admin123 | Admin |
| cashier1 | cashier123 | Cashier |

⚠️ **Change immediately after first login!**

---

## 📝 Common Tasks

### Change User Password

1. Login to MySQL:
   ```bash
   mysql -u omanbapa_user -p omanbapa_store
   ```

2. Update password:
   ```sql
   UPDATE users SET password = 'new_password' 
   WHERE username = 'admin';
   ```

3. Exit:
   ```sql
   EXIT;
   ```

### Add a New User

```sql
INSERT INTO users (username, password, role) 
VALUES ('newuser', 'newpassword', 'cashier');
```

### Add a New Product

Via web interface (admin):
1. Go to Products page
2. Scroll to "Add New Product"
3. Enter product details
4. Click "Add Product"

Via database:
```sql
INSERT INTO products (name, category, quantity, price) 
VALUES ('Product Name', 'Category', 100, 25.00);
```

### Update Product Stock

Via web interface:
1. Go to Products page
2. Find product
3. Click "Update Stock"
4. Enter new quantity
5. Click "Update"

Via database:
```sql
UPDATE products SET quantity = 150 WHERE id = 1;
```

### Delete a Product

Via web interface:
1. Go to Products page
2. Find product
3. Click "Delete"
4. Confirm

Via database:
```sql
DELETE FROM products WHERE id = 1;
```

### View Sales History

1. Go to Sales page
2. Scroll down to "Sales History"
3. View last 20 transactions

Via database:
```bash
mysql> SELECT * FROM sales ORDER BY sale_date DESC LIMIT 20;
```

---

## 🗄️ Database Commands

### View All Users

```bash
mysql> SELECT * FROM users;
```

### View All Products

```bash
mysql> SELECT * FROM products ORDER BY name;
```

### View Products with Low Stock

```bash
mysql> SELECT * FROM products WHERE quantity < 10 ORDER BY quantity;
```

### View Today's Sales

```bash
mysql> SELECT * FROM sales WHERE DATE(sale_date) = CURDATE();
```

### View Total Revenue

```bash
mysql> SELECT SUM(total_price) as revenue FROM sales;
```

### View Sales by Date Range

```bash
mysql> SELECT DATE(sale_date), SUM(total_price) 
       FROM sales 
       WHERE sale_date BETWEEN '2024-01-01' AND '2024-01-31' 
       GROUP BY DATE(sale_date);
```

### Export Sales Data

```bash
mysqldump -u omanbapa_user -p omanbapa_store sales > sales_export.sql
```

---

## 💾 Backup & Restore

### Create Backup

```bash
php backup_database.php backup
```

### List All Backups

```bash
php backup_database.php list
```

### Restore from Backup

```bash
php backup_database.php restore backup_filename.sql.gz
```

### Manual MySQL Backup

```bash
mysqldump -u omanbapa_user -p omanbapa_store > backup.sql
```

### Manual MySQL Restore

```bash
mysql -u omanbapa_user -p omanbapa_store < backup.sql
```

---

## 🔍 Monitoring Commands

### Check PHP Version

```bash
php -v
```

### Check MySQL Status

```bash
# Linux
sudo systemctl status mysql

# Windows (XAMPP)
Check Control Panel for "MySQL" status
```

### Check Disk Space

```bash
# Linux
df -h

# Windows PowerShell
Get-Volume
```

### Check Memory Usage

```bash
# Linux
free -h

# Windows PowerShell
Get-Process | Sort-Object WS -Descending | Head -10
```

### View Error Logs

```bash
# PHP errors
tail -100 /var/log/php-errors.log

# Apache errors
tail -100 /var/log/apache2/error.log

# Apache access
tail -100 /var/log/apache2/access.log
```

---

## 🚀 Performance Tips

### Clear Old Sales Records (archive before deleting)

```sql
-- View old sales (older than 1 year)
SELECT * FROM sales WHERE sale_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Delete old sales
DELETE FROM sales WHERE sale_date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Optimize Database Tables

```sql
OPTIMIZE TABLE users;
OPTIMIZE TABLE products;
OPTIMIZE TABLE sales;
```

### Check Database Size

```sql
SELECT table_name, 
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size in MB'
FROM information_schema.TABLES
WHERE table_schema = 'omanbapa_store';
```

### Enable Query Caching (MySQL 5.7)

```sql
SET GLOBAL query_cache_size = 268435456; -- 256MB
SET GLOBAL query_cache_type = 1;
```

---

## 🔒 Security Quick Check

### Test Login Security

```bash
# Try SQL injection (should fail)
Username: admin' OR '1'='1
Password: anything

# Should show "Invalid username or password"
```

### Check File Permissions

```bash
# Should show -rw-r--r-- (644)
ls -l *.php *.css .htaccess

# Should show -r------- (700)
ls -l config.php

# Should show drwxr-xr-x (755)
ls -d backups logs cache
```

### Verify HTTPS

```bash
# Should redirect to https://
curl -I http://yourdomain.com
# Should show: HTTP/1.1 301 Moved Permanently

# Check SSL certificate
openssl s_client -connect yourdomain.com:443
```

---

## 📊 Status Checker Script

Create `health_check.php`:

```php
<?php
echo "=== OBOADE NYAME HARDWARES System Health Check ===\n\n";

// PHP Version
echo "✓ PHP Version: " . phpversion() . "\n";

// Database Connection
require 'config.php';
if ($conn->connect_error) {
    echo "✗ Database: FAILED - " . $conn->connect_error . "\n";
} else {
    echo "✓ Database: Connected\n";
    
    // Count records
    $users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row();
    $products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row();
    $sales = $conn->query("SELECT COUNT(*) FROM sales")->fetch_row();
    
    echo "  - Users: " . $users[0] . "\n";
    echo "  - Products: " . $products[0] . "\n";
    echo "  - Sales Transactions: " . $sales[0] . "\n";
}

// Disk Space
$free = disk_free_space('/');
$total = disk_total_space('/');
$used = $total - $free;
$percent = ($used / $total) * 100;
echo "\n✓ Disk Space: " . round($percent, 1) . "% used\n";
echo "  - Free: " . round($free / 1024 / 1024 / 1024, 2) . " GB\n";

// Backup Status
$backup_dir = __DIR__ . '/backups/';
$backups = count(glob($backup_dir . 'backup_*'));
echo "\n✓ Backups: " . $backups . " found\n";

// File Permissions
echo "\n✓ Critical Files:\n";
echo "  - config.php: " . (is_readable('config.php') ? "✓" : "✗") . "\n";
echo "  - style.css: " . (is_readable('style.css') ? "✓" : "✗") . "\n";
echo "  - .htaccess: " . (is_readable('.htaccess') ? "✓" : "✗") . "\n";

$conn->close();
?>
```

Access: `http://yourdomain.com/health_check.php`

---

## 🆘 Quick Troubleshooting

### System Won't Load

```bash
# 1. Check if web server is running
sudo systemctl status apache2

# 2. Check if MySQL is running
sudo systemctl status mysql

# 3. Check error logs
tail -20 /var/log/apache2/error.log

# 4. Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql
```

### Login Not Working

```bash
# 1. Verify users exist
mysql -u root omanbapa_store
SELECT * FROM users;

# 2. Check password is correct
SELECT * FROM users WHERE username = 'admin';

# 3. Check Sessions directory
ls -la /var/lib/php/sessions/
```

### Sales Not Processing

```bash
# 1. Check products exist
mysql> SELECT * FROM products LIMIT 5;

# 2. Verify product has quantity > 0
mysql> SELECT * FROM products WHERE quantity = 0;

# 3. Check for MySQL errors
tail -20 /var/log/mysql/error.log
```

### Page Loads Slowly

```bash
# 1. Check server resources
top

# 2. Check database queries
mysql> SHOW PROCESSLIST;

# 3. Check slow query log
tail -20 /var/log/mysql/slow.log
```

---

## 📞 Support Resources

| Topic | Resource |
|-------|----------|
| Installation Issues | [INSTALLATION.md](INSTALLATION.md) |
| Security Questions | [SECURITY.md](SECURITY.md) |
| Technical Problems | [TROUBLESHOOTING.md](TROUBLESHOOTING.md) |
| Before Deployment | [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) |
| System Overview | [README.md](README.md) |

---

## ⏰ Maintenance Schedule

| Task | Frequency | Command |
|------|-----------|---------|
| Database Backup | Daily | `php backup_database.php backup` |
| Check Error Logs | Daily | `tail /var/log/php-errors.log` |
| Database Optimization | Monthly | `php maintenance.php optimize` |
| Old Data Cleanup | Monthly | `php maintenance.php cleanup` |
| Security Update Check | Monthly | Check PHP/MySQL release notes |
| Full Review | Quarterly | Review logs, performance metrics |

---

## 🎯 Emergency Contacts

| Role | Name | Phone | Email |
|------|------|-------|-------|
| System Admin | | | |
| Database Admin | | | |
| Support Lead | | | |
| On-Call | | | |

---

## 💡 Pro Tips

1. **Use cron for backups** - Set it and forget it!
   ```bash
   0 2 * * * php /var/www/omanbapa/backup_database.php backup
   ```

2. **Archive old sales** - Keep database clean
   ```bash
   DELETE FROM sales WHERE sale_date < DATE_SUB(NOW(), INTERVAL 6 MONTH);
   ```

3. **Monitor disk space** - Backups can grow large
   ```bash
   df -h
   ```

4. **Keep error logs** - But rotate them
   ```bash
   logrotate -f /etc/logrotate.d/php-errors
   ```

5. **Test restores** - Backups only matter if they work!
   ```bash
   php backup_database.php restore backup_latest.sql.gz
   ```

---

**Last Updated:** March 17, 2026  
**Version:** 1.0.0  
