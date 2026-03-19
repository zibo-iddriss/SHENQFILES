# Server Configuration Guide

## Production Server Setup

This guide covers configuring your server for production deployment of OBOADE NYAME HARDWARES Store Management System.

## System Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.14+
- **PHP**: 7.4+ (Recommended 8.1+)
- **MySQL**: 5.7+ or MariaDB 10.3+
- **RAM**: 2GB minimum
- **Disk Space**: 5GB minimum
- **Processor**: 2-core minimum

### PHP Extensions Required
```
- mysqli (MySQL Database)
- PDO (Database Abstraction)
- OpenSSL (HTTPS)
- JSON (Data Format)
- Mbstring (Character Encoding)
- Zip (Compression)
- GD (Image Processing)
- Curl (HTTP Requests)
- Fileinfo (File Type Detection)
```

## Apache Configuration

### Enable Required Modules
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo a2enmod headers
sudo a2enmod expires
sudo systemctl restart apache2
```

### Create Virtual Host Configuration

Create file: `/etc/apache2/sites-available/omanbapa.conf`

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    ServerAdmin admin@yourdomain.com
    
    DocumentRoot /var/www/omanbapa
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    <Directory /var/www/omanbapa>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/omanbapa_error.log
    CustomLog ${APACHE_LOG_DIR}/omanbapa_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    ServerAdmin admin@yourdomain.com
    
    DocumentRoot /var/www/omanbapa
    
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/certificate.crt
    SSLCertificateKeyFile /etc/ssl/private/private.key
    SSLCertificateChainFile /etc/ssl/certs/chain.crt
    
    <Directory /var/www/omanbapa>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security Headers
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/omanbapa_error.log
    CustomLog ${APACHE_LOG_DIR}/omanbapa_access.log combined
</VirtualHost>
```

### Enable Virtual Host
```bash
sudo a2ensite omanbapa.conf
sudo apache2ctl configtest
sudo systemctl restart apache2
```

## Nginx Configuration

Create file: `/etc/nginx/sites-available/omanbapa`

```nginx
upstream php {
    server unix:/run/php/php8.1-fpm.sock;
}

server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    root /var/www/omanbapa;
    
    ssl_certificate /etc/ssl/certs/certificate.crt;
    ssl_certificate_key /etc/ssl/private/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Gzip Compression
    gzip on;
    gzip_types text/plain text/css text/javascript application/json;
    
    # File Upload Limit
    client_max_body_size 50M;
    
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass php;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    location ~ /\. {
        deny all;
    }
    
    location ~ ~$ {
        deny all;
    }
}
```

### Enable Nginx Site
```bash
sudo ln -s /etc/nginx/sites-available/omanbapa /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## PHP Configuration

### Update `php.ini`

```ini
# Security
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Performance
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M

# Database
mysqli.default_port = 3306
mysqli.reconnect = On
mysqli.reconnect_timeout = 10

# Session
session.use_strict_mode = On
session.use_cookies = On
session.cookie_httponly = On
session.cookie_secure = On
session.cookie_samesite = Strict
session.gc_maxlifetime = 1440
session.gc_probability = 1
session.gc_divisor = 1000
```

### Create PHP-FPM Pool Configuration

Create file: `/etc/php/8.1/fpm/pool.d/omanbapa.conf`

```ini
[omanbapa]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 10

security.limit_extensions = .php .php3 .php4 .php5 .php6 .php7 .php8 .phtml .phar
```

## MySQL Configuration

### Database Optimization

```sql
-- Character Set
ALTER DATABASE omanbapa_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create Indexes
ALTER TABLE users ADD UNIQUE INDEX idx_username (username);
ALTER TABLE products ADD INDEX idx_category (category);
ALTER TABLE products ADD INDEX idx_quantity (quantity);
ALTER TABLE sales ADD INDEX idx_product_id (product_id);
ALTER TABLE sales ADD INDEX idx_sale_date (sale_date);
ALTER TABLE sales ADD INDEX idx_user_id (user_id);

-- Enable Query Cache (if not using Redis/Memcached)
SET GLOBAL query_cache_type = 1;
SET GLOBAL query_cache_size = 268435456;
```

### MyISAM to InnoDB Conversion

```sql
ALTER TABLE users ENGINE=InnoDB;
ALTER TABLE products ENGINE=InnoDB;
ALTER TABLE sales ENGINE=InnoDB;
```

## SSL/TLS Certificate Installation

### Using Let's Encrypt (Recommended)

```bash
sudo apt update
sudo apt install certbot python3-certbot-apache

# For Apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# For Nginx
sudo apt install python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer
```

### Manual Certificate Installation

If using existing certificates:
```bash
sudo cp certificate.crt /etc/ssl/certs/
sudo cp private.key /etc/ssl/private/
sudo cp chain.crt /etc/ssl/certs/
sudo chmod 600 /etc/ssl/private/private.key
```

## Firewall Configuration

### UFW (Ubuntu)

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

### iptables

```bash
# Allow SSH
sudo iptables -A INPUT -p tcp --dport 22 -j ACCEPT

# Allow HTTP
sudo iptables -A INPUT -p tcp --dport 80 -j ACCEPT

# Allow HTTPS
sudo iptables -A INPUT -p tcp --dport 443 -j ACCEPT

# Drop everything else
sudo iptables -P INPUT DROP
```

## Backup Configuration

### Automated Daily Backups

Create file: `/usr/local/bin/backup-omanbapa.sh`

```bash
#!/bin/bash

BACKUP_DIR="/backups/omanbapa"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="root"
MYSQL_PASS="your_password"
DB_NAME="omanbapa_store"

mkdir -p $BACKUP_DIR

# Backup Database
mysqldump -u $MYSQL_USER -p$MYSQL_PASS $DB_NAME | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup Application Files
tar -czf "$BACKUP_DIR/app_$DATE.tar.gz" /var/www/omanbapa

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

### Schedule with Cron

```bash
sudo chmod +x /usr/local/bin/backup-omanbapa.sh

# Add to crontab
0 2 * * * /usr/local/bin/backup-omanbapa.sh >> /var/log/omanbapa-backup.log 2>&1
```

## Monitoring & Logging

### Application Error Logging

```bash
sudo mkdir -p /var/log/omanbapa
sudo chown www-data:www-data /var/log/omanbapa
sudo chmod 755 /var/log/omanbapa
```

### System Monitoring

```bash
# Install Monit
sudo apt install monit

# Create configuration for Omanbapa
sudo nano /etc/monit/conf.d/omanbapa
```

### Log Rotation

Create file: `/etc/logrotate.d/omanbapa`

```
/var/log/omanbapa/*.log {
    daily
    compress
    rotate 30
    missingok
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## Performance Optimization

### Caching Strategy

1. **Browser Caching** (via .htaccess or Nginx config)
```apache
<FilesMatch "\\.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|eot)$">
    Header set Cache-Control "max-age=2592000, public"
</FilesMatch>
```

2. **PHP Opcode Cache**
```ini
zend_extension = opcache.so
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

## Deployment Checklist

- [ ] Install system dependencies
- [ ] Configure PHP.ini
- [ ] Setup database with optimization
- [ ] Configure web server (Apache/Nginx)
- [ ] Install SSL certificate
- [ ] Configure firewall
- [ ] Setup backup script
- [ ] Configure monitoring
- [ ] Test email functionality
- [ ] Setup error logging
- [ ] Configure cron jobs
- [ ] Load test application
- [ ] Setup CDN (optional)
- [ ] Configure DNS
- [ ] Monitor uptime and performance

## Troubleshooting

### Low Disk Space
```bash
sudo find /var/log -name "*.log" -mtime +30 -delete
sudo apt clean && apt autoclean
```

### High CPU Usage
```bash
top -b -n 1 | head -20
ps aux --sort=-%cpu | head -10
```

### MySQL Connection Issues
```bash
sudo systemctl restart mysql
sudo mysql -u root -p
SHOW PROCESSLIST;
```

## Support & Documentation

For additional support, refer to:
- [Apache Documentation](https://httpd.apache.org/docs/)
- [Nginx Documentation](http://nginx.org/en/docs/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
