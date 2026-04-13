# FixMyHostel - Complete Setup & Configuration Guide

## Table of Contents
1. [System Requirements](#system-requirements)
2. [Pre-Installation Checklist](#pre-installation-checklist)
3. [Step-by-Step Installation](#step-by-step-installation)
4. [Database Configuration](#database-configuration)
5. [Application Configuration](#application-configuration)
6. [Troubleshooting](#troubleshooting)
7. [Post-Installation Verification](#post-installation-verification)

---

## System Requirements

### Minimum Requirements
- **Operating System**: Windows 7+, macOS 10.10+, or Linux (Ubuntu 14.04+)
- **RAM**: 2GB minimum
- **Storage**: 500MB free space
- **Browser**: Modern browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)

### Software Dependencies
- **XAMPP**: Version 7.4 or higher
- **MySQL**: Version 5.7 or higher
- **PHP**: Version 7.2 or higher
- **Apache**: Version 2.4 or higher

### Browser Compatibility
- Google Chrome (Recommended)
- Mozilla Firefox
- Microsoft Edge
- Safari

---

## Pre-Installation Checklist

- [ ] XAMPP downloaded and installation media ready
- [ ] Admin access to computer for installation
- [ ] Internet connection for downloading updates
- [ ] Minimum 500MB free disk space available
- [ ] No conflicts with existing MySQL databases
- [ ] Port 80 and 3306 are available (Apache and MySQL)

---

## Step-by-Step Installation

### Phase 1: XAMPP Installation (15-20 minutes)

#### Windows Installation

1. **Download XAMPP**
   - Visit https://www.apachefriends.org/
   - Download XAMPP for Windows (7.4.x or higher)
   - Choose 32-bit or 64-bit based on your system

2. **Run Installer**
   - Double-click `xampp-windows-x64-installer.exe`
   - Click through security prompts
   - Choose installation directory (default: `C:\xampp`)
   - Select components: Apache, MySQL, PHP
   - Complete installation wizard

3. **Verify Installation**
   - Navigate to `C:\xampp\` 
   - Verify folders: `apache`, `mysql`, `htdocs` exist
   - Installation successful!

#### macOS Installation

1. **Download XAMPP**
   - Visit https://www.apachefriends.org/
   - Download XAMPP for macOS

2. **Run Installer**
   - Double-click `xampp-osx-installer.dmg`
   - Drag XAMPP to Applications folder
   - Complete installation

#### Linux Installation

1. **Download XAMPP**
   ```bash
   wget https://www.apachefriends.org/xampp-installer-linux.tar.gz
   ```

2. **Extract and Install**
   ```bash
   tar xvfz xampp-installer-linux.tar.gz -C /opt
   /opt/lampp/manager-linux.run
   ```

---

### Phase 2: Server Startup (5 minutes)

#### Windows

1. **Start XAMPP Control Panel**
   - Navigate to `C:\xampp\`
   - Run `xampp-control.exe`

2. **Start Services**
   - Click "Start" button next to **Apache**
   - Verify status shows "Running" (green)
   - Click "Start" button next to **MySQL**
   - Verify status shows "Running" (green)

#### macOS/Linux

1. **Start Services via Command Line**
   ```bash
   sudo /opt/lampp/manager-linux.run
   # Or for macOS
   sudo /Applications/XAMPP/xamppfiles/xampp start
   ```

2. **Verify Services**
   ```bash
   curl http://localhost
   # Should return Apache welcome page HTML
   ```

---

### Phase 3: Project Setup (10 minutes)

1. **Create Project Directory**
   ```
   Windows: C:\xampp\htdocs\FixMyHostel\
   macOS: /Applications/XAMPP/xamppfiles/htdocs/FixMyHostel/
   Linux: /opt/lampp/htdocs/FixMyHostel/
   ```

2. **Copy Project Files**
   - Copy all FixMyHostel files to the directory above
   - Ensure these folders exist:
     - `admin/`
     - `student/`
     - `maintenance/`
     - `includes/`
     - `css/`
     - `uploads/`
     - `Screenshots/`

3. **Set File Permissions** (Linux/macOS)
   ```bash
   chmod 755 /opt/lampp/htdocs/FixMyHostel/
   chmod 644 /opt/lampp/htdocs/FixMyHostel/*.php
   chmod 777 /opt/lampp/htdocs/FixMyHostel/uploads/
   ```

---

## Database Configuration

### Step 1: Access phpMyAdmin

1. **Open Browser**
   - Go to: http://localhost/phpmyadmin
   - You should see the phpMyAdmin login page

2. **Login to phpMyAdmin**
   - Default username: `root`
   - Default password: (leave blank)
   - Click "Go"

### Step 2: Create Database

1. **Create New Database**
   - Click on "New" in left sidebar
   - Enter database name: `fixmyhostel_db`
   - Select Collation: `utf8mb4_unicode_ci`
   - Click "Create"

2. **Verify Database Created**
   - Database should appear in left sidebar
   - Click on `fixmyhostel_db` to select it

### Step 3: Import Database Schema

1. **Navigate to SQL File**
   - Click on `fixmyhostel_db` (select it if not already)
   - Click "Import" tab at top

2. **Choose File**
   - Click "Choose File" button
   - Navigate to project root folder
   - Select `database_setup.sql`
   - Click "Open"

3. **Execute Import**
   - Verify file is selected
   - Click "Go" button
   - Wait for import to complete (should show success message)

4. **Verify Tables Created**
   - Click "Structure" tab
   - You should see two tables:
     - `users` (contains user accounts)
     - `complaints` (contains complaint records)

### Step 4: Verify Default Data

1. **Check Users Table**
   - Click on `users` table
   - Click "Browse" tab
   - Verify 4 default accounts exist:
     - Admin account (admin@fixmyhostel.com)
     - Maintenance Supervisor
     - Maintenance Associate
     - Student account

2. **Check Complaints Table**
   - Click on `complaints` table
   - Should be empty (ready for new complaints)

### Database Schema Details

#### Users Table Structure
```sql
- id (Primary Key)
- name (VARCHAR 255)
- email (VARCHAR 255, Unique)
- role (ENUM: student, maintenance_associate, maintenance_supervisor, admin)
- password (VARCHAR 255, Hashed)
- block_name, floor_number, room_number, bed_number (Location info)
- phone (VARCHAR 20)
- Created_at (Timestamp)
```

#### Complaints Table Structure
```sql
- complaint_id (Primary Key)
- student_id (Foreign Key → users.id)
- title (VARCHAR 255)
- category (VARCHAR 100)
- description (TEXT)
- priority_level (ENUM: low, medium, high, urgent)
- status (ENUM: pending, in_progress, resolved, closed)
- department (VARCHAR 100)
- issue_image (VARCHAR 255)
- created_at (Timestamp)
```

---

## Application Configuration

### Step 1: Verify Database Connection

1. **Check db.php Configuration**
   ```
   File: includes/db.php
   
   Requirements:
   - servername: "localhost"
   - username: "root"
   - password: "" (empty for default XAMPP)
   - dbname: "fixmyhostel_db"
   ```

2. **Test Connection**
   - Open browser to: http://localhost/FixMyHostel/login.php
   - If no error message, connection is working

### Step 2: Configure Upload Directory

1. **Verify Upload Folder**
   ```
   Path: htdocs/FixMyHostel/uploads/
   Permissions: 777 (writable)
   ```

2. **Check PHP Upload Settings**
   - File size limit: Should allow 2-10MB images
   - Edit `php.ini` if needed:
     ```
     upload_max_filesize = 10M
     post_max_size = 10M
     ```

### Step 3: Session Configuration

1. **Verify Session Settings**
   - Session storage: Default (file-based)
   - Session path: `C:\xampp\tmp\` (default)
   - Session lifetime: 1440 seconds (24 minutes)

---

## Troubleshooting

### Issue 1: Apache Won't Start

**Symptoms**: 
- Apache shows as stopped in XAMPP Control Panel
- Port 80 already in use message

**Solutions**:
1. Check if port 80 is in use:
   ```bash
   # Windows
   netstat -ano | findstr :80
   
   # macOS/Linux
   lsof -i :80
   ```

2. Change Apache port in `httpd.conf`:
   - Open `C:\xampp\apache\conf\httpd.conf`
   - Find: `Listen 80`
   - Change to: `Listen 8080`
   - Restart Apache
   - Access via: http://localhost:8080/

### Issue 2: MySQL Won't Start

**Symptoms**:
- MySQL shows as stopped
- "MySQL InnoDB: Cannot allocate memory" error

**Solutions**:
1. Increase system memory or close other applications
2. Restart XAMPP Control Panel
3. Delete `C:\xampp\mysql\data\ib_logfile0` and `ib_logfile1`
4. Restart MySQL service

### Issue 3: "Connection Failed" on Login

**Symptoms**:
- Error message: "Connection Failed: (error details)"
- Cannot access login page

**Solutions**:
1. Verify database name in `includes/db.php`
2. Check MySQL is running in XAMPP Control Panel
3. Verify database `fixmyhostel_db` exists in phpMyAdmin
4. Check user account has necessary permissions:
   ```sql
   GRANT ALL PRIVILEGES ON fixmyhostel_db.* TO 'root'@'localhost';
   ```

### Issue 4: "File Not Found" 404 Error

**Symptoms**:
- White page with "Object not found" message
- URL shows http://localhost/FixMyHostel/

**Solutions**:
1. Verify Apache is running
2. Check folder structure in `C:\xampp\htdocs\FixMyHostel\`
3. Verify `login.php` exists in main folder
4. Clear browser cache and reload

### Issue 5: Image Upload Not Working

**Symptoms**:
- Error uploading complaint images
- "Permission denied" message

**Solutions**:
1. Check `uploads` folder exists
2. Set permissions: `chmod 777 uploads/`
3. Verify PHP file upload settings in `php.ini`
4. Check disk space availability

### Issue 6: Password Reset Not Working

**Symptoms**:
- No email received
- Reset link doesn't work

**Solutions**:
1. Verify email configuration if SMTP is enabled
2. Check reset code expiration time
3. For testing: Use command line to set password:
   ```sql
   UPDATE users SET password='admin123' WHERE email='user@email.com';
   ```

---

## Post-Installation Verification

### Checklist for Verification

- [ ] XAMPP Control Panel shows Apache running (green)
- [ ] XAMPP Control Panel shows MySQL running (green)
- [ ] phpMyAdmin accessible at http://localhost/phpmyadmin
- [ ] Database `fixmyhostel_db` exists in phpMyAdmin
- [ ] Database tables `users` and `complaints` exist
- [ ] Default 4 user accounts visible in users table
- [ ] Can access http://localhost/FixMyHostel/login.php
- [ ] Login page loads without errors
- [ ] Can login with admin account (admin@fixmyhostel.com / admin123)
- [ ] Admin dashboard loads and displays data
- [ ] Upload folder (`uploads/`) is writable

### Quick Functionality Test

1. **Test Student Login**
   ```
   Email: student@fixmyhostel.com
   Password: student123
   Expected: Student dashboard loads with stats
   ```

2. **Test Complaint Submission**
   - Click "Submit Complaint"
   - Fill form with test data
   - Click Submit
   - Verify complaint appears in dashboard

3. **Test Admin Functions**
   ```
   Email: admin@fixmyhostel.com
   Password: admin123
   Expected: Admin can view all complaints and users
   ```

4. **Test Maintenance Workflow**
   ```
   Email: associate@fixmyhostel.com
   Password: ma123
   Expected: Can pick up pending complaints
   ```

---

## Performance Optimization

### For Production Use

1. **Enable Query Caching**
   - Edit `php.ini`
   - Set: `query_cache_size = 16M`
   - Set: `query_cache_type = 1`

2. **Enable Compression**
   - Edit `httpd.conf`
   - Enable: `mod_deflate`
   - Compresses responses for faster transfer

3. **Implement Security**
   - Use HTTPS (SSL Certificate)
   - Implement input validation
   - Use prepared statements
   - Enable CSRF tokens

4. **Database Optimization**
   - Create indexes on frequently searched fields
   - Archive old complaints
   - Regular backups

---

## Backup Strategy

### Daily Backup

```bash
# Backup database
mysqldump -u root fixmyhostel_db > backup_$(date +%Y%m%d).sql

# Backup project files
tar -czf fixmyhostel_backup_$(date +%Y%m%d).tar.gz /path/to/FixMyHostel/
```

### Weekly Full Backup

- Use phpMyAdmin Export feature
- Save to external storage
- Test restore process monthly

---

## Next Steps

1. **Read Main Documentation**: `FixMyHostel_Documentation.docx`
2. **Explore User Roles**: Login with different account types
3. **Submit Test Complaints**: Verify workflow functions
4. **Review Code**: Check `includes/functions.php` for helper functions
5. **Implement Customization**: Modify as needed for your institution

---

## Support & Maintenance

- Regular database maintenance (weekly)
- Monitor server logs for errors
- Update PHP and MySQL regularly
- Test backups monthly
- Review user access logs

---

**Last Updated**: April 2026  
**Version**: 1.0.0  
**Maintenance Contact**: Development Team
