CREATE DATABASE IF NOT EXISTS ValEduDatabase;
USE ValEduDatabase;

-- Users table (Admin, Tutor, Student, Parent)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'tutor', 'student', 'parent') NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    birthdate DATE,
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Parent-Student relationship (FIXED)
CREATE TABLE parent_student (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NOT NULL,
    student_id INT NOT NULL,
    relationship_type ENUM('father', 'mother', 'guardian') DEFAULT 'father', -- Fixed: use valid ENUM value
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_parent_student (parent_id, student_id)
);

-- Classes (year-specific naming system like 1.1, 1.2, 1.3 for year 2021)
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL, -- e.g., "1.1", "1.2", "Advanced_IELTS"
    class_year INT NOT NULL, -- e.g., 2021, 2022
    class_level VARCHAR(50), -- e.g., "Beginner", "Intermediate", "Advanced"
    subject VARCHAR(100), -- e.g., "IELTS", "TOEIC", "General English"
    description TEXT,
    max_students INT DEFAULT 20,
    sessions_total INT NOT NULL, -- Total number of sessions planned
    sessions_completed INT DEFAULT 0,
    price_per_session DECIMAL(10,2),
    schedule_time TIME, -- e.g., "09:00:00"
    schedule_duration INT, -- Duration in minutes
    schedule_days VARCHAR(20), -- e.g., "T2,T4,T6" (Monday, Wednesday, Friday)
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'closed', 'completed') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_class_year (class_name, class_year)
);

-- Class enrollments (Students in classes)
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    sessions_attended INT DEFAULT 0,
    total_fee DECIMAL(10,2), -- Total amount student should pay
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_class (student_id, class_id)
);

-- Tutor assignments to classes
CREATE TABLE class_tutors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    class_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    salary_per_session DECIMAL(10,2),
    status ENUM('active', 'inactive') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tutor_class (tutor_id, class_id)
);

-- Teaching sessions (actual lessons)
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    tutor_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    duration_minutes INT DEFAULT 120,
    topic VARCHAR(200),
    notes TEXT,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Attendance tracking
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('present', 'absent', 'late') NOT NULL,
    notes TEXT,
    recorded_by INT NOT NULL, -- tutor_id who recorded
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id),
    UNIQUE KEY unique_session_student (session_id, student_id)
);

-- Payments from students/parents
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    payer_id INT NOT NULL, -- Could be student or parent
    class_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card') DEFAULT 'cash',
    discount_code VARCHAR(20),
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (payer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Tutor salary payments
CREATE TABLE tutor_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    class_id INT NOT NULL,
    sessions_count INT NOT NULL,
    amount_per_session DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('cash', 'bank_transfer') DEFAULT 'bank_transfer',
    status ENUM('pending', 'paid') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- Discount codes for tutors
CREATE TABLE discount_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL, -- e.g., 15.00 for 15%
    max_uses INT DEFAULT 1,
    used_count INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Infrastructure and utility costs
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_type ENUM('infrastructure', 'utilities', 'equipment', 'maintenance', 'other') NOT NULL,
    description VARCHAR(200) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(100), -- e.g., "Electricity", "Rent", "Internet"
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Monthly financial summaries (for quick reporting)
CREATE TABLE financial_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month INT NOT NULL, -- 1-12
    year INT NOT NULL,
    total_revenue DECIMAL(12,2) DEFAULT 0,
    total_tutor_payments DECIMAL(12,2) DEFAULT 0,
    total_expenses DECIMAL(12,2) DEFAULT 0,
    net_profit DECIMAL(12,2) DEFAULT 0,
    total_students INT DEFAULT 0,
    total_active_classes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_month_year (month, year)
);

-- Insert initial admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role, phone) 
VALUES (
    'admin', 
    'admin@valedu.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Administrator', 
    'admin',
    '0123456789'
);

-- Insert sample tutor (password: tutor123)
INSERT INTO users (username, email, password, full_name, role, phone, birthdate) 
VALUES (
    'tutor1', 
    'tutor1@valedu.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Nguyễn Văn Minh', 
    'tutor',
    '0987654321',
    '1990-05-15'
);

-- Insert sample student (password: student123)
INSERT INTO users (username, email, password, full_name, role, phone, birthdate) 
VALUES (
    'student1', 
    'student1@valedu.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Trần Thị Hoa', 
    'student',
    '0123987654',
    '2005-08-20'
);

-- Insert sample parent (password: parent123)
INSERT INTO users (username, email, password, full_name, role, phone, birthdate) 
VALUES (
    'parent1', 
    'parent1@valedu.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'Trần Văn Nam', 
    'parent',
    '0987123456',
    '1980-03-10'
);

-- Link parent to student (FIXED)
INSERT INTO parent_student (parent_id, student_id, relationship_type, is_primary) 
VALUES (4, 3, 'father', TRUE);

-- Sample class
INSERT INTO classes (class_name, class_year, class_level, subject, description, max_students, sessions_total, price_per_session, schedule_time, schedule_duration, schedule_days, start_date, end_date) 
VALUES (
    '1.1', 
    2025, 
    'Beginner', 
    'IELTS Foundation', 
    'Basic IELTS preparation course for beginners',
    15,
    40,
    300000,
    '09:00:00',
    120,
    'T2,T4,T6',
    '2025-01-15',
    '2025-05-15'
);

-- Sample discount code for tutor
INSERT INTO discount_codes (tutor_id, code, discount_percentage, max_uses, valid_from, valid_until) 
VALUES (2, 'TUTOR1DISC', 10.00, 10, '2025-01-01', '2025-12-31');