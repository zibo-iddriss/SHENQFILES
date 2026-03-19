# Deployment Checklist

Complete this checklist before deploying OBOADE NYAME HARDWARES Store to production.

## 🔐 Security Preparation

- [ ] **Change Default Passwords**
  - [ ] Update admin password in database
  - [ ] Change MySQL root password
  - [ ] Generate secure passwords (min 12 chars, mixed case, numbers, symbols)

- [ ] **HTTPS/SSL Setup**
  - [ ] Obtain SSL certificate (Let's Encrypt recommended)
  - [ ] Install certificate on server
  - [ ] Enable HTTPS redirect in .htaccess
  - [ ] Test with https://www.ssllabs.com/ssltest/

- [ ] **Database Security**
  - [ ] Create database user with limited privileges (not root)
  - [ ] Set proper database permissions
  - [ ] Disable remote MySQL access (only local connections)
  - [ ] Change database name from 'omanbapa_store' (optional, for security through obscurity)

- [ ] **File Permissions**
  - [ ] Set 644 for all .php, .html, .css, .js files
  - [ ] Set 755 for directories
  - [ ] Set 700 for config.php (read/execute only)
  - [ ] Restrict access to backup and log directories (700)

- [ ] **Environment Configuration**
  - [ ] Copy env.example.php to env.php
  - [ ] Update all database credentials in config.php
  - [ ] Set environment to 'production' in config.php
  - [ ] Update email configuration
  - [ ] Add env.php to .gitignore

- [ ] **Disable Error Display**
  - [ ] Set `display_errors = 0` in php.ini
  - [ ] Set `log_errors = 1` and configure log file path
  - [ ] Test that errors are logged, not displayed

## 🚀 Server Setup

- [ ] **Web Server**
  - [ ] Apache 2.4+ or Nginx configured
  - [ ] PHP 7.4+ installed with required extensions:
    - [ ] mysqli (MySQL/MariaDB support)
    - [ ] gzip/zlib (for compression)
    - [ ] Sessions support
  - [ ] .htaccess enabled (Apache) or rewrite rules configured (Nginx)

- [ ] **Database**
  - [ ] MySQL 5.7+ or MariaDB 10.3+ installed
  - [ ] Database created and user provisioned
  - [ ] Tables created (check README.md for schema)
  - [ ] Initial data inserted (if needed)
  - [ ] Verify character set is utf8mb4

- [ ] **Directory Structure**
  ```
  /var/www/omanbapa/
  ├── public_html/
  │   ├── login.php
  │   ├── dashboard.php
  │   ├── products.php
  │   ├── sales.php
  │   ├── logout.php
  │   ├── config.php
  │   ├── style.css
  │   └── .htaccess
  ├── backups/          (700 permissions)
  ├── logs/             (700 permissions)
  ├── cache/            (755 permissions)
  └── .gitignore
  ```

- [ ] **Logging**
  - [ ] Create /var/log/php-errors.log
  - [ ] Set PHP error logs to this location
  - [ ] Configure log rotation
  - [ ] Verify logs are writable by web server

- [ ] **Backup**
  - [ ] Create /backups directory
  - [ ] Test backup_database.php script
  - [ ] Schedule automated backups via cron:
    ```bash
    0 2 * * * cd /var/www/omanbapa && php backup_database.php backup
    ```
  - [ ] Verify backups are being created

## 🗄️ Database

- [ ] **Initial Data**
  - [ ] Admin user created with secure password
  - [ ] Test user created for verification
  - [ ] Products inserted (if applicable)

- [ ] **Data Validation**
  - [ ] Run SELECT on each table to verify data
  - [ ] Check that users table has password hashed (if using bcrypt)
  - [ ] Verify foreign key relationships if applicable

- [ ] **Backup Verification**
  - [ ] Create first backup: `php backup_database.php backup`
  - [ ] Test restore process in test environment
  - [ ] Verify backup retention settings

## 🌐 Network & DNS

- [ ] **Domain Setup**
  - [ ] Update DNS A record to server IP
  - [ ] Update DNS records for email (if using)
  - [ ] Allow 24-48 hours for DNS propagation
  - [ ] Test DNS resolution: `nslookup yourdomain.com`

- [ ] **Firewall Rules**
  - [ ] Open port 80 (HTTP)
  - [ ] Open port 443 (HTTPS)
  - [ ] Close port 3306 (MySQL) from external access
  - [ ] Restrict admin access to trusted IPs if on VPN

- [ ] **Email (if applicable)**
  - [ ] Configure SMTP settings
  - [ ] Set SPF, DKIM records for domain
  - [ ] Test email functionality

## 📊 Application Testing

- [ ] **Functional Testing**
  - [ ] Login page works and redirects to dashboard
  - [ ] Dashboard displays correct information
  - [ ] Products page loads and displays inventory
  - [ ] Sales page processes transactions correctly
  - [ ] Logout clears session and redirects to login
  - [ ] Try access denied - verify no sensitive error messages shown

- [ ] **Security Testing**
  - [ ] SQL Injection attempt: `' OR '1'='1` in login
  - [ ] XSS attempt: `<script>alert('xss')</script>` in forms
  - [ ] Access control: Try accessing products.php as cashier (should be denied)
  - [ ] Session timeout: Leave browser idle, verify session expires

- [ ] **Performance Testing**
  - [ ] Page load time < 2 seconds
  - [ ] Database queries are efficient
  - [ ] CSS/JS loads from cache
  - [ ] No console errors in browser DevTools

- [ ] **Browser Compatibility**
  - [ ] Test on Chrome/Chromium
  - [ ] Test on Firefox
  - [ ] Test on Safari (macOS/iOS)
  - [ ] Test on Edge (Windows)
  - [ ] Mobile responsive design verified

## 📱 Mobile & Responsive

- [ ] **Mobile Testing**
  - [ ] Test on smartphone (iOS and Android)
  - [ ] Touch interactions work correctly
  - [ ] Layout responsive at all breakpoints
  - [ ] Forms are mobile-friendly

- [ ] **Different Screen Sizes**
  - [ ] 320px (small mobile)
  - [ ] 768px (tablet)
  - [ ] 1024px (laptop)
  - [ ] 1920px+ (desktop)

## 📚 Documentation

- [ ] **Documentation Complete**
  - [ ] README.md reviewed and current
  - [ ] SECURITY.md reviewed and understood
  - [ ] .htaccess configured correctly
  - [ ] env.example.php documented

- [ ] **Team Handoff**
  - [ ] Admin interface documentation provided
  - [ ] Escalation contacts listed
  - [ ] Training provided to operators
  - [ ] Emergency procedures documented

## 🔔 Monitoring & Alerts

- [ ] **Setup Monitoring** (consider services like UptimeRobot, New Relic)
  - [ ] HTTP status code monitoring
  - [ ] Page response time monitoring
  - [ ] Database connection monitoring
  - [ ] Disk space monitoring
  - [ ] CPU/Memory usage monitoring

- [ ] **Alerts Configured**
  - [ ] Email alerts for downtime
  - [ ] SMS alerts for critical issues
  - [ ] Error log monitoring setup
  - [ ] Notification contacts updated

## ✅ Final Checks Before Launch

- [ ] [ ] All security checklist items completed
- [ ] [ ] All testing checklist items completed
- [ ] [ ] Backup and restore tested successfully
- [ ] [ ] SSL certificate valid and working
- [ ] [ ] No hardcoded debug info in code
- [ ] [ ] Password hashing implemented (if applicable)
- [ ] [ ] Rate limiting configured (optional)
- [ ] [ ] Access logs enabled
- [ ] [ ] Error logs configured
- [ ] [ ] Database backups scheduled
- [ ] [ ] Monitoring alerts configured
- [ ] [ ] Runbook/documentation complete
- [ ] [ ] Emergency contacts established
- [ ] [ ] Stakeholders notified of go-live

## 📋 Post-Deployment

### Day 1
- [ ] Monitor error logs for issues
- [ ] Verify all users can access system
- [ ] Document any issues found
- [ ] Backups created and working

### Week 1
- [ ] Review usage patterns
- [ ] Check performance metrics
- [ ] Verify backup integrity
- [ ] Collect user feedback

### Month 1
- [ ] Review security logs
- [ ] Performance optimization opportunities
- [ ] Database cleanup (if needed)
- [ ] Update documentation with lessons learned

## 🆘 Rollback Plan

If critical issues discovered after deployment:

1. **Immediate (< 5 min)**
   - [ ] Stop traffic to application
   - [ ] Notify stakeholders
   - [ ] Begin rollback process

2. **Restore Previous Version (5-15 min)**
   ```bash
   # Stop web server
   sudo systemctl stop apache2
   
   # Restore database
   php backup_database.php restore backup_omanbapa_store_[PREVIOUS_DATE].sql
   
   # Restore application files
   git revert HEAD --no-edit
   
   # Start web server
   sudo systemctl start apache2
   ```

3. **Verification (5 min)**
   - [ ] Test core functionality
   - [ ] Verify database integrity
   - [ ] Notify stakeholders rollback complete

4. **Post-Mortem**
   - [ ] Document what went wrong
   - [ ] Identify root cause
   - [ ] Fix identified issues
   - [ ] Update testing procedures

## 📞 Support Contacts

| Role | Name | Contact | Availability |
|------|------|---------|--------------|
| System Administrator | | | |
| Database Administrator | | | |
| Technical Lead | | | |
| On-Call Support | | | |

## 🎉 Deployment Success Criteria

✅ System is live and accessible  
✅ All users can login and access their role's features  
✅ No critical errors in logs  
✅ Performance acceptable (< 2s page load)  
✅ HTTPS working and secure  
✅ Backups working and tested  
✅ Monitoring alerts verified  
✅ Team trained and ready  

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Approved By:** _______________  
**Deployment Status:** ☐ In Progress ☐ Complete ☐ Rolled Back

