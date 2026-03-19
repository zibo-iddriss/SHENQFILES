# OBOADE NYAME HARDWARES Store Management System

A modern, production-ready inventory and sales management system for hardware stores built with PHP, MySQL, and contemporary web technologies.

## 🚀 Features

- **Modern Dashboard** - Real-time statistics and key metrics
- **Inventory Management** - Add, update, delete products with stock tracking
- **Low Stock Alerts** - Automatic warnings when inventory falls below thresholds
- **Sales Management** - Process sales, print receipts, track stock remaining
- **Sales Reports** - Comprehensive sales history and analytics
- **User Authentication** - Secure login system with role-based access
- **Admin Controls** - Product management restricted to administrators
- **Responsive Design** - Beautiful UI that works on all devices
- **Real-time Inventory Updates** - Stock levels update instantly after sales

## 📋 System Requirements

- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher
- **Apache** with mod_rewrite enabled
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)

## 🔧 Installation & Deployment

### 1. Database Setup

```sql
-- Create Database
CREATE DATABASE omanbapa_store;
USE omanbapa_store;

-- Users Table
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'cashier') NOT NULL DEFAULT 'cashier',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  quantity INT NOT NULL DEFAULT 0,
  price DECIMAL(10, 2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sales Table
CREATE TABLE sales (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  quantity_sold INT NOT NULL,
  total_price DECIMAL(10, 2) NOT NULL,
  sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert Default Admin User
INSERT INTO users (username, password, role) VALUES ('admin', 'admin123', 'admin');
INSERT INTO users (username, password, role) VALUES ('cashier', 'cashier123', 'cashier');

-- Insert Sample Products
INSERT INTO products (name, category, quantity, price) VALUES 
  ('Cement Bag 50kg', 'Cement', 50, 25.00),
  ('Rebar 16mm', 'Steel', 100, 8.50),
  ('Paint 20L', 'Paint', 30, 45.00),
  ('Nails 1kg', 'Hardware', 200, 3.50);
```

### 2. File Upload

1. Extract all files to your web server directory:
   ```
   /xampp/htdocs/Group 3/
   ```

2. Ensure proper permissions on all files:
   ```bash
   chmod 755 /xampp/htdocs/Group\ 3/
   chmod 644 /xampp/htdocs/Group\ 3/*.php
   ```

### 3. Configuration

Update `config.php` with your database credentials:

```php
<?php
session_start();
$conn = new mysqli("localhost", "root", "password", "omanbapa_store");
if($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
```

### 4. Access the System

- **URL:** `http://localhost/Group%203/login.php`
- **Default Admin:** username: `admin`, password: `admin123`
- **Default Cashier:** username: `cashier`, password: `cashier123`

## 📂 File Structure

```
Group 3/
├── README.md              # Documentation
├── config.php             # Database configuration
├── login.php              # Login page
├── dashboard.php          # Main dashboard
├── products.php           # Product management
├── sales.php              # Sales processing
├── logout.php             # Logout handler
├── style.css              # Styling and animations
└── SECURITY.md            # Security guidelines
```

## 🔐 Security Features

- ✅ **SQL Injection Protection** - Prepared statements on all queries
- ✅ **Input Validation** - All user inputs validated before processing
- ✅ **Session Management** - Secure session handling
- ✅ **Role-Based Access Control** - Restricted admin features
- ✅ **CSRF Protection Ready** - Structure for token implementation
- ✅ **Data Sanitization** - htmlspecialchars() for output encoding

## 🎨 UI/UX Features

- **Modern Gradient Design** - Purple to violet gradient theme
- **Smooth Animations** - Subtle transitions and effects
- **Responsive Layout** - Mobile-first design approach
- **Glassmorphic Cards** - Contemporary card designs with blur effects
- **Color-Coded Alerts** - Green for good, red for warnings
- **Interactive Modals** - For confirmations and updates
- **Real-time Feedback** - Success/error messages with icons

## 📊 Key Pages

### Dashboard
- Total products count
- Total revenue
- Sales transactions count
- Best-selling products

### Products Management
- Add new products
- Update stock levels
- Delete products with confirmation
- Low stock alerts
- Inventory value reports

### Sales
- Process product sales
- Print receipts with remaining stock
- Sales history table
- Sales statistics
- Best seller tracking

### Login
- Secure authentication
- Role-based access
- Error feedback

## 🚀 Production Deployment Checklist

- [ ] Update database credentials in `config.php`
- [ ] Change default admin password
- [ ] Enable HTTPS/SSL
- [ ] Set up regular database backups
- [ ] Configure error logging
- [ ] Test all features thoroughly
- [ ] Set up monitoring and alerts
- [ ] Create admin backup account
- [ ] Document custom configurations
- [ ] Test on target server

## 🔄 Database Maintenance

### Regular Backups
```bash
mysqldump -u root -p omanbapa_store > backup_$(date +%Y%m%d).sql
```

### Restore Backup
```bash
mysql -u root -p omanbapa_store < backup_20260317.sql
```

## 📱 Browser Compatibility

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Android)

## 🆘 Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in `config.php`
- Ensure database exists

### Login Issues
- Clear browser cookies
- Verify user exists in database
- Check password (case-sensitive)

### Upload Issues
- Check file permissions (755 for directories, 644 for files)
- Verify Apache mod_rewrite is enabled
- Check disk space

## 💡 Tips for Optimization

1. **Enable Caching** - Configure browser caching for static assets
2. **Database Indexing** - Index frequently queried columns
3. **Minimize CSS/JS** - Reduce file sizes in production
4. **Content Delivery** - Use CDN for static resources
5. **Monitoring** - Set up performance monitoring

## 📞 Support

For issues or questions:
1. Check this README
2. Review SECURITY.md for security guidelines
3. Check browser console for errors
4. Verify database connection

## 📝 License

Private System - All rights reserved

## 🎉 Version

**Version:** 1.0.0  
**Release Date:** March 17, 2026  
**Last Updated:** March 17, 2026

---

**Built with ❤️ for OBOADE NYAME HARDWARES Store**
