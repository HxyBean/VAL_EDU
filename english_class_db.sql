-- Tạo database
CREATE DATABASE IF NOT EXISTS english_class_management;
USE english_class_management;

-- Bảng vai trò người dùng
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng người dùng chính
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) DEFAULT NULL UNIQUE,
    phone VARCHAR(15) DEFAULT NULL,
    full_name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Bảng giáo viên (mở rộng thông tin từ users)
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    teacher_code VARCHAR(20) DEFAULT NULL UNIQUE,
    qualification TEXT,
    experience_years INT DEFAULT 0,
    hourly_rate DECIMAL(10,2) DEFAULT NULL,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng phụ huynh
CREATE TABLE parents (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    parent_code VARCHAR(20) DEFAULT NULL UNIQUE,
    address TEXT,
    occupation VARCHAR(100),
    facebook_id VARCHAR(100),
    zalo_id VARCHAR(100),
    preferred_contact_method ENUM('phone', 'email', 'facebook', 'zalo') DEFAULT 'phone',
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Bảng học sinh
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    student_code VARCHAR(20) DEFAULT NULL UNIQUE,
    date_of_birth DATE DEFAULT NULL,
    address TEXT,
    parent_id INT NOT NULL,
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES parents(id)
);

-- Bảng lớp học
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_code VARCHAR(20) NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    grade_level INT NOT NULL, -- Lớp 1, 2, 3, 4...
    academic_year INT NOT NULL, -- 2018, 2019, 2020...
    class_section VARCHAR(10) DEFAULT '1', -- 1, 2, 3 (cho lớp 3.1, 3.2, 3.3)
    max_students INT DEFAULT 20,
    fee_per_session DECIMAL(10,2) NOT NULL,
    sessions_per_month INT DEFAULT 8,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_closed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_class (grade_level, academic_year, class_section)
);

-- Bảng phân công giáo viên cho lớp
CREATE TABLE class_teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    teacher_id INT NOT NULL,
    assigned_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    UNIQUE KEY unique_assignment (class_id, teacher_id, assigned_date),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Bảng đăng ký học sinh vào lớp
CREATE TABLE class_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    fee_per_session DECIMAL(10,2) DEFAULT NULL, -- Có thể khác với fee của lớp do giảm giá
    discount_percentage DECIMAL(5,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
);

-- Bảng buổi học
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    teacher_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME DEFAULT NULL,
    duration_minutes INT DEFAULT 90,
    topic VARCHAR(200) DEFAULT NULL,
    notes TEXT,
    is_cancelled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Bảng điểm danh
CREATE TABLE attendances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    is_present BOOLEAN NOT NULL,
    notes TEXT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    marked_by INT DEFAULT NULL, -- ID của giáo viên điểm danh
    UNIQUE KEY unique_attendance (session_id, student_id),
    FOREIGN KEY (session_id) REFERENCES sessions(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Bảng thanh toán
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    payment_month INT NOT NULL, -- Tháng thanh toán (1-12)
    payment_year INT NOT NULL,
    total_sessions INT NOT NULL, -- Tổng số buổi trong tháng
    attended_sessions INT NOT NULL, -- Số buổi đã học
    amount_due DECIMAL(10,2) NOT NULL, -- Số tiền phải trả
    amount_paid DECIMAL(10,2) DEFAULT 0.00, -- Số tiền đã trả
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_date DATE DEFAULT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'other') DEFAULT 'cash',
    is_fully_paid BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Bảng lương giáo viên
CREATE TABLE teacher_salaries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    salary_month INT NOT NULL,
    salary_year INT NOT NULL,
    total_sessions INT NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    payment_date DATE DEFAULT NULL,
    is_paid BOOLEAN DEFAULT FALSE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Bảng thông báo quảng cáo
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('popup', 'slider', 'banner') DEFAULT 'popup',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    target_audience ENUM('all', 'parents', 'students', 'teachers') DEFAULT 'all',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Bảng tin nhắn tự động
CREATE TABLE auto_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_type ENUM('parent', 'student') NOT NULL,
    recipient_id INT NOT NULL,
    message_type ENUM('absence_fee', 'class_cancelled', 'general') NOT NULL,
    platform ENUM('facebook', 'zalo', 'sms') NOT NULL,
    message_content TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_sent BOOLEAN DEFAULT FALSE,
    response_received TEXT,
    notes TEXT
);

-- ============================
-- DỮ LIỆU MẪU
-- ============================

-- Thêm vai trò
INSERT INTO roles (role_name, description) VALUES
('admin', 'Quản trị viên hệ thống'),
('teacher', 'Giáo viên'),
('parent', 'Phụ huynh'),
('student', 'Học sinh');

-- Thêm tài khoản người dùng
INSERT INTO users (username, password, email, phone, full_name, role_id) VALUES
-- Admin
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@englishcenter.com', '0901234567', 'Nguyễn Văn Admin', 1),

-- Giáo viên
('teacher001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher1@englishcenter.com', '0912345678', 'Nguyễn Thị Hương', 2),
('teacher002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher2@englishcenter.com', '0923456789', 'Trần Văn Nam', 2),
('teacher003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher3@englishcenter.com', '0934567890', 'Lê Thị Lan', 2),

-- Phụ huynh
('parent001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent1@gmail.com', '0945678901', 'Phạm Văn Tùng', 3),
('parent002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent2@gmail.com', '0956789012', 'Hoàng Thị Mai', 3),
('parent003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent3@gmail.com', '0967890123', 'Đỗ Văn Hùng', 3),
('parent004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent4@gmail.com', '0978901234', 'Vũ Thị Linh', 3),

-- Học sinh
('student001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Phạm Minh An', 4),
('student002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Phạm Minh Khôi', 4),
('student003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Hoàng Tuấn Anh', 4),
('student004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Đỗ Thùy Linh', 4),
('student005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'Vũ Minh Đức', 4);

-- Thêm thông tin giáo viên
INSERT INTO teachers (user_id, teacher_code, qualification, experience_years, hourly_rate) VALUES
(2, 'GV001', 'Cử nhân Sư phạm Tiếng Anh, IELTS 7.5', 5, 150000),
(3, 'GV002', 'Thạc sĩ Ngôn ngữ Anh, TOEIC 950', 3, 120000),
(4, 'GV003', 'Cử nhân Tiếng Anh, TOEFL iBT 100', 2, 100000);

-- Thêm thông tin phụ huynh
INSERT INTO parents (user_id, parent_code, address, occupation, facebook_id, zalo_id, preferred_contact_method) VALUES
(5, 'PH001', '123 Đường ABC, Quận 1, TP.HCM', 'Kỹ sư', 'phamvantung123', '0945678901', 'zalo'),
(6, 'PH002', '456 Đường DEF, Quận 2, TP.HCM', 'Nhân viên văn phòng', 'hoangthimai456', '0956789012', 'facebook'),
(7, 'PH003', '789 Đường GHI, Quận 3, TP.HCM', 'Kinh doanh', 'dovanhung789', '0967890123', 'phone'),
(8, 'PH004', '321 Đường JKL, Quận 4, TP.HCM', 'Giáo viên', 'vuthilinh321', '0978901234', 'zalo');

-- Thêm thông tin học sinh
INSERT INTO students (user_id, student_code, date_of_birth, address, parent_id, discount_percentage) VALUES
(9, 'HS001', '2015-03-15', '123 Đường ABC, Quận 1, TP.HCM', 1, 10.00),
(10, 'HS002', '2016-07-22', '123 Đường ABC, Quận 1, TP.HCM', 1, 10.00),
(11, 'HS003', '2015-11-08', '456 Đường DEF, Quận 2, TP.HCM', 2, 0.00),
(12, 'HS004', '2016-01-30', '789 Đường GHI, Quận 3, TP.HCM', 3, 5.00),
(13, 'HS005', '2015-09-12', '321 Đường JKL, Quận 4, TP.HCM', 4, 15.00);

-- Thêm lớp học
INSERT INTO classes (class_code, class_name, grade_level, academic_year, class_section, max_students, fee_per_session, sessions_per_month, start_date, end_date, is_active) VALUES
('L3-2024-1', 'Lớp 3 năm 2024 - Lớp 1', 3, 2024, '1', 15, 200000, 8, '2024-09-01', '2025-05-31', TRUE),
('L3-2024-2', 'Lớp 3 năm 2024 - Lớp 2', 3, 2024, '2', 15, 200000, 8, '2024-09-01', '2025-05-31', TRUE),
('L4-2024-1', 'Lớp 4 năm 2024 - Lớp 1', 4, 2024, '1', 12, 250000, 8, '2024-09-01', '2025-05-31', TRUE),
('L3-2023-1', 'Lớp 3 năm 2023 - Lớp 1', 3, 2023, '1', 15, 180000, 8, '2023-09-01', '2024-05-31', FALSE);

-- Phân công giáo viên
INSERT INTO class_teachers (class_id, teacher_id, assigned_date, is_active) VALUES
(1, 1, '2024-09-01', TRUE),
(2, 2, '2024-09-01', TRUE),
(3, 3, '2024-09-01', TRUE),
(4, 1, '2023-09-01', FALSE);

-- Đăng ký học sinh vào lớp
INSERT INTO class_enrollments (class_id, student_id, enrollment_date, fee_per_session, discount_percentage, is_active) VALUES
(1, 1, '2024-09-01', 180000, 10.00, TRUE), -- Phạm Minh An giảm 10%
(1, 2, '2024-09-01', 180000, 10.00, TRUE), -- Phạm Minh Khôi giảm 10%
(2, 3, '2024-09-01', 200000, 0.00, TRUE),  -- Hoàng Tuấn Anh không giảm
(3, 4, '2024-09-01', 237500, 5.00, TRUE),  -- Đỗ Thùy Linh giảm 5%
(1, 5, '2024-09-01', 170000, 15.00, TRUE); -- Vũ Minh Đức giảm 15%

-- Thêm một số buổi học mẫu
INSERT INTO sessions (class_id, teacher_id, session_date, session_time, duration_minutes, topic) VALUES
(1, 1, '2024-09-05', '14:00:00', 90, 'Unit 1: Greetings and Introductions'),
(1, 1, '2024-09-07', '14:00:00', 90, 'Unit 1: Family Members'),
(1, 1, '2024-09-12', '14:00:00', 90, 'Unit 2: Colors and Numbers'),
(1, 1, '2024-09-14', '14:00:00', 90, 'Unit 2: School Subjects'),
(2, 2, '2024-09-06', '16:00:00', 90, 'Unit 1: Daily Activities'),
(2, 2, '2024-09-08', '16:00:00', 90, 'Unit 1: Time and Schedule');

-- Thêm điểm danh mẫu
INSERT INTO attendances (session_id, student_id, is_present, marked_by) VALUES
-- Buổi 1 lớp 1
(1, 1, TRUE, 2),  -- Phạm Minh An có mặt
(1, 2, TRUE, 2),  -- Phạm Minh Khôi có mặt  
(1, 5, FALSE, 2), -- Vũ Minh Đức vắng

-- Buổi 2 lớp 1
(2, 1, TRUE, 2),  -- Phạm Minh An có mặt
(2, 2, FALSE, 2), -- Phạm Minh Khôi vắng
(2, 5, TRUE, 2),  -- Vũ Minh Đức có mặt

-- Buổi 3 lớp 1
(3, 1, TRUE, 2),  -- Phạm Minh An có mặt
(3, 2, TRUE, 2),  -- Phạm Minh Khôi có mặt
(3, 5, TRUE, 2),  -- Vũ Minh Đức có mặt

-- Buổi 1 lớp 2
(5, 3, TRUE, 3);  -- Hoàng Tuấn Anh có mặt

-- Thêm thanh toán mẫu
INSERT INTO payments (student_id, class_id, payment_month, payment_year, total_sessions, attended_sessions, amount_due, amount_paid, discount_amount, payment_date, is_fully_paid) VALUES
(1, 1, 9, 2024, 8, 6, 1440000, 1440000, 160000, '2024-09-25', TRUE), -- Phạm Minh An đã đóng đủ tháng 9
(2, 1, 9, 2024, 8, 5, 1440000, 1000000, 160000, '2024-09-30', FALSE), -- Phạm Minh Khôi chưa đóng đủ
(3, 2, 9, 2024, 8, 7, 1600000, 0, 0, NULL, FALSE), -- Hoàng Tuấn Anh chưa đóng
(5, 1, 9, 2024, 8, 8, 1360000, 800000, 240000, '2024-09-28', FALSE); -- Vũ Minh Đức đóng một phần

-- Thêm lương giáo viên mẫu
INSERT INTO teacher_salaries (teacher_id, salary_month, salary_year, total_sessions, hourly_rate, total_amount, paid_amount, payment_date, is_paid) VALUES
(1, 9, 2024, 20, 150000, 3000000, 3000000, '2024-10-01', TRUE),
(2, 9, 2024, 15, 120000, 1800000, 0, NULL, FALSE),
(3, 9, 2024, 12, 100000, 1200000, 1200000, '2024-10-01', TRUE);

-- Thêm thông báo mẫu
INSERT INTO announcements (title, content, announcement_type, start_date, end_date, is_active, target_audience, created_by) VALUES
('Khai giảng lớp mới tháng 10/2024', 'Trung tâm sẽ khai giảng thêm lớp 2 và lớp 5 vào đầu tháng 10. Phụ huynh quan tâm vui lòng liên hệ để đăng ký.', 'popup', '2024-09-15', '2024-10-05', TRUE, 'parents', 1),
('Thông báo nghỉ lễ Quốc khánh', 'Trung tâm thông báo nghỉ lễ Quốc khánh 2/9, các lớp sẽ được bù vào cuối tuần.', 'slider', '2024-08-30', '2024-09-03', FALSE, 'all', 1);

-- Tạo các index để tăng hiệu suất truy vấn
CREATE INDEX idx_users_role ON users(role_id);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_classes_year ON classes(academic_year);
CREATE INDEX idx_classes_active ON classes(is_active);
CREATE INDEX idx_sessions_date ON sessions(session_date);
CREATE INDEX idx_sessions_class ON sessions(class_id);
CREATE INDEX idx_payments_month_year ON payments(payment_month, payment_year);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_attendances_session ON attendances(session_id);
CREATE INDEX idx_attendances_student ON attendances(student_id);