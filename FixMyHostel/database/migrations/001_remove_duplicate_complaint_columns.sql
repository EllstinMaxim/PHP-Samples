-- FixMyHostel Database Migration
-- Updates users and complaints tables for role-based system

-- 1. Update users table role enum to support new roles
ALTER TABLE users
MODIFY COLUMN role ENUM('student','maintenance_associate','maintenance_supervisor','admin')
NOT NULL;

-- 2. Add useful timestamp columns to users table
ALTER TABLE users
ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER reset_code_expiry,
ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER created_at;

-- 3. Remove duplicate user fields from complaints table
ALTER TABLE complaints
DROP COLUMN IF EXISTS student_name,
DROP COLUMN IF EXISTS student_email,
DROP COLUMN IF EXISTS block_name,
DROP COLUMN IF EXISTS floor_number,
DROP COLUMN IF EXISTS room_number;

-- 4. Add foreign key constraint from complaints to users (optional but recommended)
ALTER TABLE complaints
ADD CONSTRAINT fk_complaints_student
FOREIGN KEY (student_id) REFERENCES users(id)
ON DELETE CASCADE
ON UPDATE CASCADE;

-- 5. Ensure status enum values are correct
ALTER TABLE complaints
MODIFY COLUMN status ENUM('Pending','In Progress','Resolved')
NOT NULL DEFAULT 'Pending';
