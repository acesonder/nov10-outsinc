# OUTSINC Deployment Guide

This guide will help you deploy OUTSINC to a production environment.

## Prerequisites

- Web server (Apache 2.4+ or Nginx 1.18+)
- PHP 7.4 or higher with extensions:
  - mysqli
  - pdo_mysql
  - mbstring
  - json
  - session
- MySQL 5.7 or higher (or MariaDB 10.2+)
- HTTPS certificate (recommended for production)
- Domain name

## Quick Deployment (5 minutes)

### Method 1: Using the Installation Wizard (Recommended)

1. **Upload files to your server**
   ```bash
   # Via FTP, SFTP, or Git
   git clone https://github.com/acesonder/nov10-outsinc.git /var/www/html/outsinc
   cd /var/www/html/outsinc
   ```

2. **Set proper permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 public/uploads/
   chown -R www-data:www-data .  # For Apache/Nginx on Ubuntu
   ```

3. **Configure web server** (see section below)

4. **Run the installation wizard**
   - Open your browser and navigate to: `https://yourdomain.com/install.php`
   - Follow the on-screen instructions
   - Enter your MySQL database credentials
   - Click "Install OUTSINC"

5. **Login with default admin account**
   - Email: `admin@outsinc.org`
   - Password: `admin123`
   - **Important**: Change this immediately!

6. **Delete installation file**
   ```bash
   rm install.php
   ```

### Method 2: Manual Installation

1. **Create MySQL database**
   ```bash
   mysql -u root -p
   CREATE DATABASE outsinc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   exit;
   ```

2. **Import schema**
   ```bash
   mysql -u root -p outsinc < database/schema.sql
   ```

3. **Configure database connection**
   - Edit `includes/config.php`
   - Update `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
   - Update `SITE_URL` to your domain

4. **Set permissions**
   ```bash
   chmod -R 777 public/uploads/
   ```

## Web Server Configuration

### Apache (.htaccess already included)

If using Apache, ensure mod_rewrite is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Create a virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/outsinc/public
    
    <Directory /var/www/html/outsinc/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/outsinc_error.log
    CustomLog ${APACHE_LOG_DIR}/outsinc_access.log combined
</VirtualHost>
```

### Nginx

Create a server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/outsinc/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Security Hardening (Production)

### 1. Update Configuration

Edit `includes/config.php`:

```php
// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

// Enable HTTPS only cookies
ini_set('session.cookie_secure', 1);

// Set proper site URL
define('SITE_URL', 'https://yourdomain.com');
```

### 2. Enable HTTPS

Using Let's Encrypt (free SSL):

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Get certificate
sudo certbot --apache -d yourdomain.com          # For Apache
# OR
sudo certbot --nginx -d yourdomain.com           # For Nginx
```

### 3. File Permissions

```bash
# Set strict permissions
chmod -R 755 /var/www/html/outsinc
chmod -R 750 /var/www/html/outsinc/includes
chmod -R 750 /var/www/html/outsinc/api
chmod 440 /var/www/html/outsinc/includes/config.php

# Only uploads directory needs write access
chmod -R 777 /var/www/html/outsinc/public/uploads
```

### 4. Database Security

```sql
-- Create dedicated database user
CREATE USER 'outsinc_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT SELECT, INSERT, UPDATE, DELETE ON outsinc.* TO 'outsinc_user'@'localhost';
FLUSH PRIVILEGES;
```

Update `includes/config.php` with new credentials.

### 5. Hide Sensitive Files

Add to `.htaccess` (Apache) or nginx config:

```apache
# Apache
<FilesMatch "^(config\.php|\.env)$">
    Require all denied
</FilesMatch>
```

```nginx
# Nginx
location ~ /\.(env|git|htaccess) {
    deny all;
}
```

## Post-Deployment Tasks

### 1. Change Default Admin Password
- Login as admin@outsinc.org
- Go to Settings → Change Password
- Use a strong, unique password

### 2. Add Resources
- Go to Admin Panel → Manage Resources
- Add local shelters, food banks, clinics, etc.
- Include accurate addresses for map functionality

### 3. Create Staff Accounts
- Admin Panel → Manage Users
- Create accounts for your staff members
- Assign appropriate roles

### 4. Configure Announcements
- Admin Panel → Announcements
- Create welcome message or important notices

### 5. Test All Features
- Register test accounts for each role
- Submit test cases
- Create test events
- Send test messages
- Verify notifications work

### 6. Backup Strategy

Set up automated backups:

```bash
# Database backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p outsinc > /backups/outsinc_$DATE.sql
find /backups -name "outsinc_*.sql" -mtime +7 -delete

# Add to crontab (daily at 2 AM)
0 2 * * * /path/to/backup_script.sh
```

## Performance Optimization

### 1. Enable PHP OpCache

Edit `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 2. MySQL Optimization

```sql
-- Add indexes for better performance
ALTER TABLE cases ADD INDEX idx_created_updated (created_at, updated_at);
ALTER TABLE messages ADD INDEX idx_conversation_time (sender_id, recipient_id, created_at);
ALTER TABLE notifications ADD INDEX idx_user_time (user_id, created_at);
```

### 3. Enable Gzip Compression (Apache)

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

## Monitoring

### Setup Monitoring Tools

1. **Server Monitoring**
   - Install monitoring (e.g., Netdata, Prometheus)
   - Monitor CPU, memory, disk usage

2. **Application Monitoring**
   - Check database size: `SELECT table_name, ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = 'outsinc';`
   - Monitor error logs
   - Track user growth

3. **Uptime Monitoring**
   - Use services like UptimeRobot or Pingdom
   - Set up alerts for downtime

## Troubleshooting

### Database Connection Issues
```bash
# Test MySQL connection
mysql -h localhost -u outsinc_user -p outsinc

# Check PHP MySQL extensions
php -m | grep mysql
```

### File Upload Issues
```bash
# Check permissions
ls -la public/uploads/

# Check PHP upload settings
php -i | grep upload

# Increase limits in php.ini if needed
upload_max_filesize = 10M
post_max_size = 10M
```

### Session Issues
```bash
# Check session directory
php -i | grep session.save_path

# Ensure directory is writable
ls -la /var/lib/php/sessions/
```

## Maintenance

### Regular Tasks

**Weekly:**
- Review audit logs in Admin Panel
- Check for spam or inappropriate content
- Update resources if needed

**Monthly:**
- Backup database
- Review user feedback
- Update success stories
- Check disk space usage

**Quarterly:**
- Update PHP and MySQL versions
- Review security best practices
- Analyze usage statistics
- Plan new features

## Support

For deployment issues or questions:
- Check the README.md for basic setup
- Review logs in `/var/log/apache2/` or `/var/log/nginx/`
- Contact: info@outsinc.org

## License

MIT License - See LICENSE file for details
