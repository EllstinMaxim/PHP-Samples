-- Database setup script for FixMyHostel
-- This script drops existing tables, creates new ones, and inserts admin records

-- Drop tables if they exist
DROP TABLE IF EXISTS complaint_notifications;
DROP TABLE IF EXISTS complaint_comment_reads;
DROP TABLE IF EXISTS complaint_comments;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ic_number VARCHAR(20),
    registration_number VARCHAR(50),
    email VARCHAR(255) UNIQUE NOT NULL,
    block_name VARCHAR(50),
    floor_number VARCHAR(10),
    room_number VARCHAR(10),
    bed_number VARCHAR(10) DEFAULT NULL,
    role ENUM('student', 'maintenance_associate', 'maintenance_supervisor', 'admin') NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_code VARCHAR(20) DEFAULT NULL,
    reset_code_expiry DATETIME DEFAULT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create complaints table
CREATE TABLE complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    phone VARCHAR(20),
    priority_level ENUM('low', 'medium', 'high', 'urgent') NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    department VARCHAR(100),
    issue_image VARCHAR(255),
    status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    comment_count INT NOT NULL DEFAULT 0,
    last_commented_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Complaint comments (chat/comments)
CREATE TABLE complaint_comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    commented_by INT NOT NULL,
    comment TEXT NOT NULL,
    commented_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (commented_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_complaint_comments_lookup (complaint_id, commented_at, commented_by)
);

-- Tracks each user's last read timestamp per complaint chat
CREATE TABLE complaint_comment_reads (
    complaint_id INT NOT NULL,
    user_id INT NOT NULL,
    last_read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (complaint_id, user_id),
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notification entries generated per new comment and recipient
CREATE TABLE complaint_notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_user_id INT NOT NULL,
    complaint_id INT NOT NULL,
    comment_id INT NOT NULL,
    actor_user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES complaint_comments(comment_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_recipient_comment (recipient_user_id, comment_id),
    INDEX idx_recipient_created (recipient_user_id, created_at)
);

-- Insert admin records
-- Passwords are hashed using password_hash() with PASSWORD_DEFAULT
-- For demo purposes, password for all admins is 'admin123'
INSERT INTO users (name, email, role, password, phone) VALUES
('Admin', 'admin@fixmyhostel.com', 'admin', 'admin123', '1234567890'),
('Associate', 'associate@fixmyhostel.com', 'maintenance_associate', 'ma123', '1234567892'),      
('Student', 'student@fixmyhostel.com', 'student', 'student123', '1234567893');
