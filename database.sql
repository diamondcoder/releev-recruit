-- ============================================================
-- Releev Recruitment — Database Schema
-- Import this in phpMyAdmin to create the database
-- ============================================================

CREATE DATABASE IF NOT EXISTS surely_sci_tech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE surely_sci_tech;

-- ── ADMINS ─────────────────────────────────────────
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username = admin, password = admin123 (CHANGE THIS AFTER LOGIN)
INSERT INTO admins (username, email, password_hash, full_name)
VALUES ('admin', 'admin@company.se', '$2y$10$f7G3y9kZxX0XzB8OyHzFHeLqz5j2Q9iH2.8bU.cY1fK5hQ1iH7XHO', 'Site Administrator');

-- ── EMPLOYERS ──────────────────────────────────────
DROP TABLE IF EXISTS employers;
CREATE TABLE employers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(200) NOT NULL,
    contact_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(50),
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── JOBS ───────────────────────────────────────────
DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employer_id INT DEFAULT NULL,
    posted_by ENUM('admin','employer') NOT NULL DEFAULT 'admin',
    title VARCHAR(200) NOT NULL,
    sector ENUM('Life Sciences','Engineering','Management') NOT NULL,
    location VARCHAR(150) NOT NULL,
    contract_type ENUM('Permanent','Contract','Temporary','Internship') DEFAULT 'Permanent',
    hours ENUM('Full-time','Part-time') DEFAULT 'Full-time',
    salary VARCHAR(150),
    description TEXT NOT NULL,
    requirements TEXT,
    status ENUM('pending','approved','rejected','closed') DEFAULT 'pending',
    rejection_reason TEXT,
    posted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at DATETIME DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_sector (sector),
    FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── CONTACT MESSAGES ───────────────────────────────
DROP TABLE IF EXISTS contacts;
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    enquiry_type VARCHAR(50),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ── CANDIDATE CV SUBMISSIONS ───────────────────────
DROP TABLE IF EXISTS candidates;
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    sector VARCHAR(100),
    current_role VARCHAR(200),
    message TEXT,
    linkedin VARCHAR(255),
    cv_filename VARCHAR(255),
    cv_original_name VARCHAR(255),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ── JOB APPLICATIONS ───────────────────────────────
DROP TABLE IF EXISTS applications;
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    linkedin VARCHAR(255),
    cover_message TEXT,
    cv_filename VARCHAR(255),
    cv_original_name VARCHAR(255),
    status ENUM('new','reviewed','shortlisted','rejected') DEFAULT 'new',
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_job (job_id),
    INDEX idx_status (status),
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── SAMPLE JOBS (so the public page isn't empty) ───
INSERT INTO jobs (posted_by, title, sector, location, contract_type, hours, salary, description, status, approved_at) VALUES
('admin', 'Research Scientist – Bioanalytical', 'Life Sciences', 'Göteborg, Sweden', 'Permanent', 'Full-time', 'Competitive', 'Lead bioanalytical research projects in a cutting-edge biotech environment. PhD in life sciences preferred.', 'approved', NOW()),
('admin', 'Clinical Research Associate (CRA)', 'Life Sciences', 'Stockholm, Sweden', 'Contract', 'Full-time', 'Market Rate', 'Manage clinical trials end-to-end. ICH-GCP certified candidates with 2+ years CRA experience.', 'approved', NOW()),
('admin', 'Automation Engineer – PLC/SCADA', 'Engineering', 'Göteborg, Sweden', 'Permanent', 'Full-time', 'Competitive', 'Design and commission automation systems for pharmaceutical manufacturing facilities.', 'approved', NOW()),
('admin', 'Mechanical Design Engineer', 'Engineering', 'Remote / Sweden', 'Contract', 'Full-time', 'Market Rate', 'Mechanical design for medical device development. SolidWorks expertise required.', 'approved', NOW()),
('admin', 'Project Manager – R&D Programs', 'Management', 'Göteborg, Sweden', 'Permanent', 'Full-time', 'Competitive', 'Lead cross-functional R&D programs in life sciences. PMP or equivalent certification required.', 'approved', NOW()),
('admin', 'Business Development Manager – Life Sciences', 'Management', 'Sweden (National)', 'Permanent', 'Full-time', 'Competitive + OTE', 'Drive business growth across the Nordic life sciences market. 5+ years BD experience needed.', 'approved', NOW());

