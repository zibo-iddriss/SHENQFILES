# Troubleshooting Guide

Common issues and their solutions for OBOADE NYAME HARDWARES Store.

## 🔧 Installation & Setup Issues

### Issue: "Connection refused" or "Can't connect to localhost"

**Symptoms:**
- Error: "Connection refused (111)" or "No connection could be made (10061)"
- White page or "Error connecting to database"

**Solutions:**
1. Check if MySQL is running:
   ```bash
   # Windows (XAMPP)
   Check XAMPP Control Panel - MySQL service should be "Running"
   
   # Linux
   sudo systemctl status mysql
   sudo systemctl start mysql
   
   # macOS
   brew services list
   brew services start mysql
   ```

2. Verify database credentials in config.php:
   ```php
   $host = 'localhost';      // Check this matches your setup
   $user = 'root';           // Verify username
   $password = '';           // Verify password
   $database = 'omanbapa_store'; // Check database name
   ```

3. Test connection manually:
   ```php
   $conn = new mysqli('localhost', 'root', '', 'omanbapa_store');
   if ($conn->connect_error) {
       echo "Connection Error: " . $conn->connect_error;
   } else {
       echo "Connected successfully!";
   }
   ```

### Issue: "Table doesn't exist" error

**Symptoms:**
- Error: "Table 'omanbapa_store.users' doesn't exist"

**Solutions:**
1. Verify database was created:
   ```bash
   # MySQL CLI
   mysql -u root
   SHOW DATABASES;
   USE omanbapa_store;
   SHOW TABLES;
   ```

2. Create tables if missing (see README.md for schema):
   ```bash
   mysql -u root omanbapa_store < schema.sql
   ```

3. Re-import the database schema

### Issue: "Access denied for user 'root'@'localhost'"

**Symptoms:**
- Error: "Access denied for user 'root'@'localhost' (using password: NO/YES)"

**Solutions:**
1. Check config.php has correct password:
   ```php
   // If you haven't set MySQL password (default)
   $password = '';
   
   // If you set a password
   $password = 'your_password';
   ```

2. Reset MySQL password (XAMPP):
   - Stop MySQL in XAMPP Control Panel
   - Delete the password file
   - Restart MySQL
   - Login with no password

3. Verify MySQL user exists:
   ```bash
   mysql -u root -p
   SELECT user, host FROM mysql.user;
   ```

---

## 🔐 Login & Authentication Issues

### Issue: Cannot login even with correct credentials

**Symptoms:**
- Login button doesn't work or page refreshes without logging in
- No error message displayed

**Solutions:**
1. Check PHP Sessions are enabled:
   ```php
   // Add to login.php temporarily
   <?php
   session_start();
   echo "Session ID: " . session_id(); // Should output a value
   ?>
   ```

2. Verify sessions directory is writable:
   ```bash
   # Linux
   ls -la /var/lib/php/sessions/
   chmod 1733 /var/lib/php/sessions/
   
   # Windows
   Check that temp directory in php.ini exists and is writable
   ```

3. Check if prepared statements are failing:
   ```php
   $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
   if (!$stmt) {
       echo "Prepare failed: " . $conn->error;
   }
   ```

4. Verify user record exists in database:
   ```bash
   mysql> SELECT * FROM users WHERE username = 'admin';
   ```

### Issue: User logged in but immediately redirected back to login

**Symptoms:**
- Login appears to work then redirects back to login page
- $_SESSION variables not persisting

**Solutions:**
1. Check for header() redirects before session_start():
   ```php
   // WRONG - Will lose session
   header('Location: dashboard.php');
   session_start();
   
   // CORRECT
   session_start();
   // ... do work ...
   header('Location: dashboard.php');
   exit();
   ```

2. Verify cookies are accepted by browser:
   - Check browser cookie settings
   - Check if HTTPS is enforced but HTTP is being used

3. Check for PHP errors that might be preventing session:
   ```php
   error_reporting(E_ALL);
   session_start();
   // Rest of code
   ```

### Issue: "Access Denied" when trying to access admin pages as cashier

**This is not a bug - this is expected behavior!**

Only admin users can access products.php. If you're logged in as cashier and see "Access Denied", this is correct.

To access as admin:
1. Check your user's rol in the database:
   ```bash
   mysql> SELECT username, role FROM users WHERE username = 'your_username';
   ```

2. Update role if needed:
   ```bash
   mysql> UPDATE users SET role = 'admin' WHERE username = 'your_username';
   ```

---

## 📊 Database Issues

### Issue: "Duplicate entry" error when inserting data

**Symptoms:**
- Error: "Duplicate entry 'X' for key 'PRIMARY'" or "Unique constraint violation"

**Solutions:**
1. Check if record already exists:
   ```bash
   mysql> SELECT * FROM products WHERE id = 1;
   ```

2. Clear duplicate data:
   ```bash
   mysql> DELETE FROM products WHERE id > 1000;
   ```

3. Reset auto_increment:
   ```bash
   mysql> ALTER TABLE products AUTO_INCREMENT = 1;
   ```

### Issue: Product quantity not updating correctly

**Symptoms:**
- Stock quantity doesn't change after sale
- Low stock alert appears/disappears unexpectedly

**Solutions:**
1. Check if UPDATE query is executing:
   ```php
   $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
   if ($stmt->execute([$qty, $pid])) {
       echo "Updated successfully";
   } else {
       echo "Update failed: " . $stmt->error;
   }
   ```

2. Verify quantity is positive:
   ```bash
   mysql> SELECT id, quantity FROM products WHERE quantity < 0;
   ```

3. Reset quantities if needed:
   ```bash
   mysql> UPDATE products SET quantity = 50 WHERE quantity < 0;
   ```

### Issue: Lost data after server restart

**Symptoms:**
- Sales transactions disappear
- Products reset to old values

**Solutions:**
1. Check if data is persisted to disk:
   ```bash
   mysql> SELECT * FROM sales ORDER BY id DESC LIMIT 5;
   ```

2. Verify MySQL configuration is saving data:
   - Check my.cnf (MySQL data directory)
   - Ensure data directory has enough disk space

3. Restore from backup:
   ```bash
   php backup_database.php list
   php backup_database.php restore backup_filename.sql
   ```

---

## 🎨 Frontend & Display Issues

### Issue: Page loads but styles/CSS not applied

**Symptoms:**
- Page is plain white with no colors or formatting
- Layout looks broken

**Solutions:**
1. Check if style.css is loaded:
   - Open browser DevTools (F12)
   - Go to Network tab
   - Reload page
   - Check if style.css has status 200 (not 404)

2. Verify CSS file path is correct in HTML:
   ```html
   <!-- Should be this -->
   <link rel="stylesheet" href="style.css">
   
   <!-- NOT this -->
   <link rel="stylesheet" href="/style.css">
   <link rel="stylesheet" href="http://localhost/style.css">
   ```

3. Clear browser cache:
   - Press Ctrl+Shift+Delete (Chrome/Firefox)
   - Select "All time"
   - Clear cache

4. Check file permissions:
   ```bash
   ls -la style.css
   # Should show -rw-r--r-- (644)
   ```

### Issue: Images or Font Awesome icons not showing

**Symptoms:**
- Broken image icons next to text
- Font icons showing as boxes: ☐ ✓ ✗

**Solutions:**
1. Check if Font Awesome CDN is accessible:
   ```html
   <!-- In browser console (F12) -->
   fetch('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css')
     .then(r => console.log(r.status))
   ```

2. Add Font Awesome locally if CDN fails:
   - Download Font Awesome
   - Add to your assets directory
   - Update CSS link: `<link rel="stylesheet" href="assets/font-awesome.min.css">`

### Issue: Modal (popup) not appearing

**Symptoms:**
- Click button to open modal but nothing happens
- Modal appears behind other content

**Solutions:**
1. Check JavaScript console for errors (F12 → Console tab)

2. Verify modal HTML exists:
   ```bash
   grep -n "modal" dashboard.php
   # Should find modal div and button
   ```

3. Check CSS for modal display:
   ```css
   .modal {
     display: none;  /* Should be hidden by default */
   }
   .modal.active {
     display: flex;  /* Should be visible when active */
     z-index: 9999; /* Should be on top */
   }
   ```

4. Test modal manually in console:
   ```javascript
   // Open modal
   document.getElementById('your-modal-id').classList.add('active');
   
   // Close modal
   document.getElementById('your-modal-id').classList.remove('active');
   ```

### Issue: Page looks broken on mobile devices

**Symptoms:**
- Text too small or too large
- Buttons not clickable
- Layout overlapping

**Solutions:**
1. Check viewport meta tag exists in HTML:
   ```html
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   ```

2. Test responsive design:
   - Press F12 to open DevTools
   - Click device icon (top left)
   - Select different device sizes

3. Check CSS media queries are correct:
   ```css
   @media (max-width: 768px) {
     /* Mobile styles */
   }
   ```

---

## ⚡ Performance Issues

### Issue: Page loads very slowly

**Symptoms:**
- Can't make purchases or access dashboard
- Operations timeout

**Solutions:**
1. Check database is running and responsive:
   ```bash
   mysql -u root -e "SELECT 1;"
   ```

2. Check for slow queries:
   ```bash
   mysql> SHOW PROCESSLIST;  # See running queries
   ```

3. Check server resources:
   ```bash
   # Linux
   top          # View CPU/Memory
   df -h        # View disk space
   
   # Windows
   TaskManager  # View CPU/Memory
   ```

4. Clear old data:
   ```bash
   mysql> DELETE FROM sales WHERE date < DATE_SUB(NOW(), INTERVAL 1 YEAR);
   ```

### Issue: Database running out of space

**Symptoms:**
- "No space left on device" error
- MySQL stops accepting data

**Solutions:**
1. Check disk usage:
   ```bash
   df -h
   # Look for /var or partition showing 100%
   ```

2. Find large files:
   ```bash
   du -sh *
   # Look for backup directory
   ```

3. Archive/delete old backups:
   ```bash
   ls -la backups/
   rm backups/backup_*_2024-01.sql.gz  # Remove old files
   ```

4. Clean temporary files:
   ```bash
   rm -rf /tmp/*
   ```

---

## 🔒 Security Issues

### Issue: Suspicious login attempts or attacks

**Symptoms:**
- Multiple failed login attempts in logs
- User accounts locked

**Solutions:**
1. Check error logs:
   ```bash
   tail -f /var/log/php-errors.log
   tail -f /var/log/apache2/access.log
   ```

2. Block IP addresses:
   ```bash
   # via .htaccess
   Deny from 192.168.1.100
   Deny from 10.0.0.0/8
   ```

3. Enable login rate limiting (see SECURITY.md)

4. Reset user passwords:
   ```bash
   mysql> UPDATE users SET password = 'newpassword' WHERE username = 'admin';
   ```

### Issue: Session hijacking or unauthorized access

**Symptoms:**
- Seeing other users' data
- Users logged in without credentials

**Solutions:**
1. Regenerate session IDs:
   ```php
   session_regenerate_id(true);
   ```

2. Clear all sessions:
   ```bash
   # Linux
   rm -rf /var/lib/php/sessions/*
   ```

3. Force logout all users:
   ```bash
   mysql> TRUNCATE sessions;  # If sessions stored in DB
   ```

4. Review access logs:
   ```bash
   tail -100 /var/log/apache2/access.log
   ```

---

## 📋 Common Error Messages

| Error | Cause | Solution |
|-------|-------|----------|
| "Call to undefined function" | Function doesn't exist or typo | Check PHP version, check function name |
| "Cannot modify header" | Output before header() | Check for whitespace, echo, or newlines before header() |
| "Undefined variable" | Variable not set | Use isset() check, initialize variable |
| "Type error: int<->string" | Wrong data type | Use intval(), floatval() for conversion |
| "Syntax error Parse error" | PHP syntax error | Check for missing semicolon, quotes, braces |
| "Fatal error Maximum execution time" | Script taking too long | Optimize queries, increase timeout limit |
| "503 Service Unavailable" | Server overloaded or down | Restart web server, check resources |
| "Permission denied" | File permissions wrong | Use chmod 644 or 755 as needed |

---

## 🐛 Debugging Tips

### Enable Verbose Logging
```php
// Add to config.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
```

### Test Database Connection
```php
<?php
$conn = new mysqli('localhost', 'root', '', 'omanbapa_store');
if ($conn->connect_error) {
    die("Connection Error: " . $conn->connect_error);
}
echo "✓ Connected successfully!";
echo "Server version: " . $conn->server_info;
?>
```

### Check Session Values
```php
<?php
session_start();
echo '<pre>';
echo "Session ID: " . session_id() . "\n";
echo "User: " . ($_SESSION['username'] ?? 'Not set') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "\n";
echo "All Session Data:\n";
print_r($_SESSION);
echo '</pre>';
?>
```

### Monitor Real-Time Logs
```bash
# Linux
tail -f /var/log/php-errors.log
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log

# Windows
type C:\xampp\apache\logs\error.log
```

---

## 📞 When to Contact Support

If you've tried all solutions above and still have issues:

1. **Collect Information:**
   - PHP version: `php -v`
   - MySQL version: `mysql -V`
   - Error messages (exact text)
   - Recent changes made
   - Browser used (for frontend issues)

2. **Prepare Logs:**
   - PHP error logs
   - Web server access logs
   - Your steps to reproduce

3. **Provide Details:**
   - What were you trying to do?
   - What happened instead?
   - What does error message say?
   - When did this start?

---

**Need more help?** Check [FAQ](FAQ.md) or contact the system administrator.

