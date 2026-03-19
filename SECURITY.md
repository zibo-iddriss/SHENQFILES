# Security Guidelines

## Overview

This document outlines the security features, best practices, and recommendations for OBOADE NYAME HARDWARES Store Management System.

## 🔐 Implemented Security Measures

### 1. SQL Injection Prevention
- **Prepared Statements**: All database queries use prepared statements with parameterized queries
- **Type Binding**: Parameters are bound with explicit types (i=integer, s=string, d=double)
- **Example**:
  ```php
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  ```

### 2. Input Validation
- All user inputs are validated before processing
- Integer fields use `intval()` for conversion
- Float fields use `floatval()` for conversion
- String fields use `trim()` and `htmlspecialchars()`
- Required fields are checked for empty values

### 3. Output Encoding
- `htmlspecialchars()` used for all dynamic output
- Prevents XSS (Cross-Site Scripting) attacks
- Encodes special characters safely

### 4. Session Management
- `session_start()` at the beginning of each page
- Session variables used for authentication
- Access control checks on admin pages
- Session destruction on logout

### 5. Authentication
- Username and password validation
- Role-based access control (RBAC)
- Admin-only features protected with role checks
- Example:
  ```php
  if($_SESSION['role'] != 'admin') {
    die("Access Denied");
  }
  ```

## 🛡️ Recommendations for Production

### 1. Password Security
**Current:** Plain text passwords ⚠️  
**Recommended:** Use `password_hash()` and `password_verify()`

```php
// Registration
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Login
if(password_verify($password, $hashed_password)) {
  // Login successful
}
```

### 2. HTTPS/SSL
- Enable SSL certificate on Apache
- Redirect HTTP to HTTPS
- Set secure cookie flag:
  ```php
  ini_set('session.cookie_secure', 1);
  ini_set('session.cookie_httponly', 1);
  ```

### 3. CSRF Protection
Add CSRF tokens to all forms:
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In form
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

// Verify
if($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  die("CSRF token mismatch");
}
```

### 4. Rate Limiting
Implement login attempt throttling:
```php
// Track failed attempts
$_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
if($_SESSION['login_attempts'] > 5) {
  sleep(5); // Delay after multiple failures
}
```

### 5. Error Logging
- Log security events
- Don't display errors to users in production
- Set proper error handling:
  ```php
  ini_set('display_errors', 0);
  ini_set('log_errors', 1);
  ini_set('error_log', '/var/log/php-errors.log');
  ```

### 6. Database Backups
- Schedule regular automated backups
- Test restore procedures
- Keep backups secure and off-site

### 7. Access Logs
Monitor and log:
- Failed login attempts
- Unauthorized access attempts
- Database modifications
- User actions (timestamp + user + action)

## 🔒 Configuration Security

### config.php Protection
```php
// Prevent direct access
if (basename($_SERVER['PHP_SELF']) == 'config.php') {
  exit('Direct access not allowed');
}
```

### .htaccess (Apache)
Create `.htaccess` in the project root:
```apache
<FilesMatch "\.(php|txt|md|sql)$">
  Deny from all
</FilesMatch>

# Except for our PHP files
<Files "*.php">
  Allow from all
</Files>

# Enable HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## 🚨 Security Checklist for Deployment

- [ ] Change all default passwords
- [ ] Enable HTTPS/SSL
- [ ] Update error logging configuration
- [ ] Implement password hashing
- [ ] Add CSRF token protection
- [ ] Enable rate limiting on login
- [ ] Configure database backups
- [ ] Set up access logging
- [ ] Review file permissions (644 for files, 755 for directories)
- [ ] Disable PHP error display in production
- [ ] Create firewall rules
- [ ] Set up intrusion detection
- [ ] Regular security audits
- [ ] Employee training on security practices

## 📋 Future Security Enhancements

1. **Two-Factor Authentication (2FA)**
   - SMS or email OTP
   - Authenticator app support

2. **API Key Authentication**
   - For potential mobile app integration
   - Token-based authentication

3. **Audit Trails**
   - Log all user actions
   - Track data modifications
   - Export audit reports

4. **Data Encryption**
   - Encrypt sensitive data at rest
   - Encrypt data in transit

5. **Penetration Testing**
   - Regular security testing
   - Vulnerability scanning

## 📞 Reporting Security Issues

If you discover a security vulnerability:
1. Do not publish it publicly
2. Document the issue with reproduction steps
3. Contact the system administrator immediately
4. Allow reasonable time for patching

## 📚 Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [MySQL Security](https://dev.mysql.com/doc/internals/en/security.html)
- [Web Application Firewall (WAF)](https://www.cloudflare.com/learning/ddos/glossary/web-application-firewall-waf/)

---

**Version:** 1.0.0  
**Last Updated:** March 17, 2026
