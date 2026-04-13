# FixMyHostel - Hostel Complaint Management System

## Quick Start Guide

A comprehensive hostel complaint management system that streamlines the process of submitting, tracking, and resolving complaints across students, maintenance staff, and administrators.

---

## System Requirements

- **XAMPP** (Apache, MySQL, PHP 7.2+)
- **MySQL Server** (Version 5.7+)
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **PHP 7.2+**

---

## Installation & Setup

### Step 1: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP with Apache and MySQL components
3. Choose installation directory (default: `C:\xampp` on Windows)

### Step 2: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** server
4. Verify both show green status indicators

### Step 3: Prepare the Project

1. Navigate to XAMPP htdocs folder: `C:\xampp\htdocs\`
2. If FixMyHostel folder doesn't exist, create it
3. Place all project files in `C:\xampp\htdocs\FixMyHostel\`

### Step 4: Create Database

1. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on **Create new database**
3. Database name: `fixmyhostel_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click **Create**

### Step 5: Import Database Structure

1. In phpMyAdmin, select `fixmyhostel_db` database
2. Click on **Import** tab
3. Choose file: `database_setup.sql` (located in project root)
4. Click **Go** to import tables and initial data

### Step 6: Verify Database Configuration

Check that `includes/db.php` has correct settings:
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fixmyhostel_db";
```

### Step 7: Access the Application

Open your browser and navigate to:
```
http://localhost/FixMyHostel/
```

---

## Default Login Credentials

### Student Account
- Email: `student@fixmyhostel.com`
- Password: `student123`

### Maintenance Associate
- Email: `associate@fixmyhostel.com`
- Password: `ma123`

### Maintenance Supervisor
- Email: `supervisor@fixmyhostel.com`
- Password: `ms123`

### Administrator
- Email: `admin@fixmyhostel.com`
- Password: `admin123`

---

## File Structure

```
FixMyHostel/
├── admin/                      # Admin pages
│   ├── dashboard.php
│   ├── manage_complaints.php
│   ├── manage_users.php
│   └── edit_student.php
├── student/                    # Student pages
│   ├── dashboard.php
│   ├── my_complaints.php
│   ├── submit_complaint.php
│   ├── edit_complaint.php
│   └── delete_complaint.php
├── maintenance/                # Maintenance pages
│   ├── dashboard.php
│   └── manage_complaints.php
├── includes/                   # Common files
│   ├── db.php                 # Database connection
│   ├── functions.php          # Helper functions
│   └── sidebar.php            # Navigation sidebar
├── css/                        # Stylesheets
│   └── style.css
├── database/
│   └── migrations/            # Database migrations
├── uploads/                    # Uploaded complaint images
├── Screenshots/                # System screenshots
├── database_setup.sql         # Database schema
├── login.php                  # Main login page
├── register_step1.php         # Student registration
├── register_step2.php         # Registration confirmation
├── verify_code.php            # Code verification
├── view_complaint.php         # View complaint details
├── forgot_password.php        # Password recovery
└── logout.php                 # Logout handler
```

---

## User Roles & Permissions

### 1. Student
- **Actions**: Submit complaints, track status, edit/delete pending complaints
- **Access**: Dashboard, Submit Complaint, My Complaints

### 2. Maintenance Associate
- **Actions**: View assigned complaints, pick up work (change to In Progress)
- **Access**: Dashboard, Manage Complaints

### 3. Maintenance Supervisor
- **Actions**: Review in-progress complaints, mark as resolved
- **Access**: Dashboard, Manage Complaints

### 4. Administrator
- **Actions**: Full system control, manage all complaints, manage users, reset passwords
- **Access**: All pages

---

## Complaint Status Flow

```
Pending → In Progress → Resolved → Closed
```

- **Pending**: New complaint submitted by student
- **In Progress**: Associate picked up the complaint
- **Resolved**: Supervisor verified completion
- **Closed**: Admin final status (if needed)

---

## Common Issues & Solutions

### Issue: "Connection Failed" on login page
**Solution**: 
- Verify MySQL server is running in XAMPP Control Panel
- Check `includes/db.php` for correct database name: `fixmyhostel_db`
- Ensure database was imported correctly with `database_setup.sql`

### Issue: 404 Error accessing the site
**Solution**:
- Verify Apache server is running in XAMPP Control Panel
- Check project folder is in `C:\xampp\htdocs\FixMyHostel\`
- Restart Apache server

### Issue: File upload not working
**Solution**:
- Ensure `uploads/` folder exists and has write permissions
- Check PHP configuration for upload_max_filesize

### Issue: Password reset not working
**Solution**:
- Verify email server configuration if SMTP is used
- Check browser console for JavaScript errors

---

## Database Backup & Recovery

### Create Backup
1. Open phpMyAdmin
2. Select `fixmyhostel_db` database
3. Click **Export** tab
4. Select format: **SQL**
5. Click **Go** and save file

### Restore Backup
1. Open phpMyAdmin
2. Create new database: `fixmyhostel_db_restore`
3. Click **Import** tab
4. Select backup SQL file
5. Click **Go**

---

## Support & Documentation

For detailed documentation including:
- Complete workflow diagrams
- Screenshot walkthroughs for each role
- Advanced configuration
- Troubleshooting guide

Refer to: `FixMyHostel_Documentation.docx`

---

## Version Info

- **Version**: 1.0.0
- **Last Updated**: April 2026
- **Database**: MySQL 5.7+
- **Framework**: PHP 7.2+ (No external framework)

---

## License & Disclaimer

This system is designed for educational and hostel management purposes. 
For production use, ensure proper security measures are implemented.

---
