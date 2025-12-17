CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) UNIQUE DEFAULT NULL,  -- Optional for non-students
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10) DEFAULT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'technician', 'admin') NOT NULL DEFAULT 'student',
    account_status INT NOT NULL DEFAULT 1,  -- 1 = active, 0 = inactive/archived (but we'll use archive table for full archiving)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE archive_users (
    id INT NOT NULL,  -- Original ID
    student_number VARCHAR(20) DEFAULT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10) DEFAULT NULL,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'technician', 'admin') NOT NULL DEFAULT 'student',
    account_status INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    archived_by INT NULL,
    archived_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),  -- Use original ID as PK for reference
    FOREIGN KEY (archived_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 2. VERIFICATION_CODES (From user side)
CREATE TABLE verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL DEFAULT (NOW() + INTERVAL 5 MINUTE),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. EQUIPMENT TABLE (Based on user side, add admin fields: serial_number, pc_id, lab_location, added_by)
CREATE TABLE equipment (
    equipment_id INT AUTO_INCREMENT PRIMARY KEY,
    added_by INT,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    pc_id VARCHAR(50) DEFAULT NULL,
    lab_location ENUM('Nexus', 'Sandbox', 'Raise', 'EdTech') NOT NULL DEFAULT 'Nexus',
    status ENUM('Available', 'Borrowed', 'With Error', 'Pulled out') DEFAULT 'Available',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by)
        REFERENCES users (id)
        ON DELETE SET NULL
);

-- Re-add your sample data
INSERT INTO equipment (name, pc_id, lab_location, status) VALUES
('Desktop PC 01', 'PC-NX-001', 'Nexus', 'Available'),
('Desktop PC 02', 'PC-NX-002', 'Nexus', 'With Error'),
('Laptop Unit', 'LAP-SB-001', 'Sandbox', 'Available'),
('Projector', 'PROJ-RAISE-01', 'Raise', 'Available'),
('Smart TV', 'TV-EDTECH-01', 'EdTech', 'Available');

-- 4. BORROW TABLE (From user side)
CREATE TABLE borrow (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    equipment_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL,
    status ENUM('Borrowed', 'Returned', 'Overdue') DEFAULT 'Borrowed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON UPDATE CASCADE ON DELETE CASCADE
);

-- 5. REQUEST TABLE (Use detailed from admin side)
CREATE TABLE request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    submitter_id INT NOT NULL,
    request_title VARCHAR(100) NOT NULL,
    request_type ENUM('Software installation', 'Purchase', 'Peripheral', 'Hardware') DEFAULT 'Hardware',
    request_priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    request_description VARCHAR(500),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Resolved', 'Rejected') DEFAULT 'Pending',
    resolved_by INT NULL,
    resolved_at DATETIME NULL,
    FOREIGN KEY (submitter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `lab_sessions` (
  `session_id` INT NOT NULL auto_increment,
  `session_code` VARCHAR(50) NOT NULL UNIQUE,
  `faculty_id` INT NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `section` VARCHAR(50) NOT NULL,
  `lab_name` VARCHAR(100) DEFAULT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 15,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `is_active` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`session_id`),
  KEY `idx_faculty` (`faculty_id`),
  KEY `idx_code` (`session_code`),
  KEY `idx_active` (`is_active`, `expires_at`),
  FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `session_attendance` (
  `attendance_id` INT NOT NULL AUTO_INCREMENT,
  `session_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `student_name` VARCHAR(255) NOT NULL,
  `student_number` VARCHAR(50) DEFAULT NULL,
  `pc_number` VARCHAR(50) NOT NULL,
  `timestamp` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_attendance` (`session_id`, `student_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_timestamp` (`timestamp`),
  FOREIGN KEY (`session_id`) REFERENCES `lab_sessions`(`session_id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
