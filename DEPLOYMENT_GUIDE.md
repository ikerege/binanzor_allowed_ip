# 🚀 Football Betting Platform - Deployment Guide

## 📋 System Requirements

- **PHP Version**: 7.4 or higher (8.0+ recommended)
- **MySQL Version**: 5.7 or higher (8.0+ recommended)
- **Web Server**: Apache with mod_rewrite enabled
- **PHP Extensions**: PDO, PDO_MySQL, mbstring, openssl

## 📁 Project Structure

```
football-betting-platform/
├── public/              # Document root (Point your domain here)
│   ├── index.php       # Main entry point
│   └── .htaccess       # URL rewriting rules
├── config/             # Database configuration
├── models/             # Data models
├── controllers/        # Business logic
├── views/              # HTML templates
├── core/               # Core system files
├── database_schema.sql # Database setup file
└── DEPLOYMENT_GUIDE.md # This file
```

## 🌐 Shared Hosting Deployment (cPanel)

### Step 1: Upload Files

1. **Download/Clone the project** to your local machine
2. **Access cPanel File Manager** or use FTP client
3. **Navigate to public_html** (or your domain's document root)
4. **Upload all files EXCEPT the public folder contents**
   ```
   public_html/
   ├── config/
   ├── models/
   ├── controllers/
   ├── views/
   ├── core/
   └── database_schema.sql
   ```
5. **Upload public folder contents to document root**
   ```
   public_html/
   ├── index.php
   ├── .htaccess
   ├── (other folders from step 4)
   ```

### Step 2: Database Setup

1. **Access cPanel → MySQL Databases**
2. **Create a new database**: `yourusername_football_betting`
3. **Create a database user** with a strong password
4. **Grant ALL privileges** to the user on the database
5. **Access phpMyAdmin**
6. **Select your database**
7. **Import the `database_schema.sql` file**

### Step 3: Configure Database Connection

1. **Edit `config/database.php`**
2. **Update database credentials**:
   ```php
   private $host = 'localhost';           // Usually localhost
   private $dbname = 'yourusername_football_betting';
   private $username = 'yourusername_dbuser';
   private $password = 'your_secure_password';
   ```

### Step 4: Set Proper Permissions

Set these permissions via cPanel File Manager or FTP:
- **Folders**: 755 (drwxr-xr-x)
- **PHP Files**: 644 (-rw-r--r--)
- **config/ folder**: 750 (more restrictive)

### Step 5: Test Installation

1. **Visit your domain**: `https://yourdomain.com`
2. **You should see the betting platform homepage**
3. **Test registration**: Create a new account
4. **Test admin login**:
   - Email: `admin@betfootball.com`
   - Password: `admin123`

## 🔧 Configuration Options

### Environment-Specific Settings

For **production**, update these settings in `config/database.php`:

```php
// Enable error reporting for development only
// ini_set('display_errors', 0);        // Disable in production
// error_reporting(E_ALL);              // Disable in production

// Security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);    // Only if using HTTPS
ini_set('session.use_strict_mode', 1);
```

### Custom Domain Configuration

If using a subdomain or subfolder:

1. **Update routes in `public/index.php`** if needed
2. **Modify `.htaccess` RewriteBase** if in subfolder:
   ```apache
   RewriteEngine On
   RewriteBase /your-subfolder/
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

## 🔒 Security Checklist

### Essential Security Steps

1. **Change default admin password** immediately
2. **Update database credentials** with strong passwords
3. **Enable HTTPS** (SSL certificate)
4. **Hide sensitive files**:
   ```apache
   # Add to .htaccess
   <Files "*.sql">
       Order allow,deny
       Deny from all
   </Files>
   ```

5. **Set secure file permissions**
6. **Regular backups** of database and files
7. **Keep PHP and MySQL updated**

### Additional Security Headers

Add to `.htaccess`:
```apache
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

## 🐛 Troubleshooting

### Common Issues

**1. "Database connection failed"**
- Check database credentials in `config/database.php`
- Verify database exists and user has permissions
- Check if MySQL server is running

**2. "404 Not Found" for pages**
- Ensure `.htaccess` file is uploaded to document root
- Check if `mod_rewrite` is enabled (most shared hosts have it)
- Verify file permissions

**3. "500 Internal Server Error"**
- Check PHP error logs in cPanel
- Verify PHP version compatibility
- Check file permissions (755 for folders, 644 for files)

**4. Blank page/white screen**
- Enable error reporting temporarily:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Check PHP error logs

### Log File Locations

- **cPanel Error Logs**: `/public_html/error_logs/`
- **PHP Errors**: Usually in cPanel → Error Logs section

## 📊 Performance Optimization

### Caching (if available)

1. **Enable OPcache** in cPanel if available
2. **Use browser caching** (already configured in `.htaccess`)
3. **Enable compression** (configured in `.htaccess`)

### Database Optimization

1. **Regular database maintenance**
2. **Monitor slow queries**
3. **Index optimization** (already included in schema)

## 🔄 Updates and Maintenance

### Regular Tasks

1. **Database backups** (weekly recommended)
2. **File backups** (before any updates)
3. **Monitor logs** for errors
4. **Update admin password** regularly
5. **Clean up old session data**

### Backup Commands

For manual database backup:
```bash
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql
```

## 🎯 Admin Features

### Default Admin Access

- **URL**: `https://yourdomain.com/admin`
- **Email**: `admin@betfootball.com`
- **Password**: `admin123` (CHANGE IMMEDIATELY)

### Admin Capabilities

- **User Management**: View and manage user accounts
- **Match Management**: Add, edit, delete matches
- **Bet Management**: View all bets and settle results
- **Balance Management**: Adjust user balances
- **Statistics**: Platform analytics and reports

## 📞 Support

### Getting Help

1. **Check error logs** first
2. **Verify configuration** settings
3. **Test with sample data**
4. **Contact hosting provider** for server-specific issues

### Common Hosting Providers

**Shared Hosting Recommendations**:
- SiteGround (PHP 8.0+, good performance)
- Bluehost (cPanel, easy setup)
- HostGator (affordable, reliable)
- A2 Hosting (fast, developer-friendly)

---

## 🎉 Congratulations!

Your Football Betting Platform is now deployed and ready for use! 

**Next Steps**:
1. Test all functionality
2. Add more matches
3. Customize styling if needed
4. Set up monitoring and backups
5. Promote your platform!

---

**⚠️ Important**: This platform is for educational/demonstration purposes. Ensure compliance with local gambling laws and regulations before operating a real betting platform.