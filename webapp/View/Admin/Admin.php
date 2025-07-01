<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Dashboard - VAL EDU' ?></title>
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardNavbar.css">
    <link rel="stylesheet" href="/webapp/View/Admin/Admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>    
    <?php include __DIR__ . '/../Partial/DashboardHeader.php';?>
    <?php $user_role = 'admin'; include __DIR__ . '/../Partial/DashboardNavbar.php';?>

    <main class="main-content">        
        <!-- Overview Section -->
        <section id="overview" class="content-section active">
            <h2>Tổng Quan Bảng Điều Khiển</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($stats['total_students'] ?? 0) ?></h3>
                        <p>Tổng Số Học Viên</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($stats['total_tutors'] ?? 0) ?></h3>
                        <p>Giáo Viên Đang Hoạt Động</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($stats['total_classes'] ?? 0) ?></h3>
                        <p>Lớp Học Đang Hoạt Động</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_revenue'] ?? 0) ?>đ</h3>
                        <p>Tổng Doanh Thu</p>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-line"></i> Xu Hướng Đăng Ký Học Viên</h3>
                        <div class="chart-controls">
                            <select id="registration-year-select" onchange="updateRegistrationChart()">
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="studentRegistrationChart"></canvas>
                    </div>
                    <div class="chart-info">
                        <div class="info-item">
                            <span class="info-label">Tháng này:</span>
                            <span class="info-value" id="students-this-month">
                                <?= htmlspecialchars($current_month_stats['new_students_this_month'] ?? 0) ?> học viên
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Đăng ký mới:</span>
                            <span class="info-value" id="enrollments-this-month">
                                <?= htmlspecialchars($current_month_stats['new_enrollments_this_month'] ?? 0) ?> lượt
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3><i class="fas fa-chart-pie"></i> Phân Bố Cấp Độ Lớp Học</h3>
                        <div class="chart-controls">
                            <button onclick="refreshClassDistribution()" class="refresh-btn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="classDistributionChart"></canvas>
                    </div>
                    <div class="chart-legend" id="class-distribution-legend">
                        <!-- Legend will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <div class="recent-activities">
                <h3>Hoạt Động Gần Đây</h3>
                <div class="activity-list">
                    <?php if (isset($activities) && !empty($activities)): ?>
                        <?php foreach (array_slice($activities, 0, 5) as $activity): ?>
                            <div class="activity-item">
                                <?php if ($activity['type'] === 'student_registration'): ?>
                                    <i class="fas fa-user-plus"></i>
                                    <span>Đăng ký học viên mới: <?= htmlspecialchars($activity['name']) ?></span>
                                <?php elseif ($activity['type'] === 'enrollment'): ?>
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Đăng ký lớp học: <?= htmlspecialchars($activity['name']) ?></span>
                                <?php else: ?>
                                    <i class="fas fa-info-circle"></i>
                                    <span><?= htmlspecialchars($activity['name']) ?></span>
                                <?php endif; ?>
                                <small><?= timeAgo($activity['activity_date']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <i class="fas fa-info-circle"></i>
                            <span>Chưa có hoạt động nào gần đây</span>
                            <small>Bây giờ</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>        
        
        <!-- Manage Students Section -->
        <section id="manage_students" class="content-section">
            <h2>Quản Lý Học Viên</h2>
            
            <div class="section-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm học viên...">
                </div>
                <button class="btn-primary"><i class="fas fa-plus"></i> Thêm Học Viên Mới</button>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Họ Tên</th>
                            <th>Email</th>
                            <th>Cấp Độ Khóa Học</th>
                            <th>Ngày Đăng Ký</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>Sarah Johnson</td>
                            <td>sarah.j@email.com</td>
                            <td>Trung Cấp</td>
                            <td>15/01/2024</td>
                            <td><span class="status active">Hoạt Động</span></td>
                            <td>
                                <button class="btn-edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>John Doe</td>
                            <td>john.d@email.com</td>
                            <td>Nâng Cao</td>
                            <td>20/02/2024</td>
                            <td><span class="status active">Hoạt Động</span></td>
                            <td>
                                <button class="btn-edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-delete"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>        <!-- Manage Teachers Section -->
        <section id="manage_teachers" class="content-section">
            <h2>Quản Lý Giáo Viên</h2>
            
            <div class="section-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Tìm kiếm giáo viên...">
                </div>
                <button class="btn-primary"><i class="fas fa-plus"></i> Thêm Giáo Viên Mới</button>
            </div>

            <div class="teachers-grid">
                <div class="teacher-card">
                    <div class="teacher-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4>Emma Watson</h4>
                    <p>Giáo Viên Tiếng Anh Chính</p>
                    <div class="teacher-stats">
                        <span><i class="fas fa-users"></i> 25 Học Viên</span>
                        <span><i class="fas fa-star"></i> 4.9 Đánh Giá</span>
                    </div>
                    <div class="teacher-actions">
                        <button class="btn-edit">Chỉnh Sửa</button>
                        <button class="btn-view">Xem Hồ Sơ</button>
                    </div>
                </div>
                
                <div class="teacher-card">
                    <div class="teacher-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4>David Smith</h4>
                    <p>Chuyên Gia Hội Thoại</p>
                    <div class="teacher-stats">
                        <span><i class="fas fa-users"></i> 18 Học Viên</span>
                        <span><i class="fas fa-star"></i> 4.7 Đánh Giá</span>
                    </div>
                    <div class="teacher-actions">
                        <button class="btn-edit">Chỉnh Sửa</button>
                        <button class="btn-view">Xem Hồ Sơ</button>
                    </div>
                </div>
            </div>
        </section>        <!-- Manage Courses Section -->
        <section id="manage_courses" class="content-section">
            <h2>Quản Lý Khóa Học</h2>
            
            <div class="section-header">
                <div class="course-filters">
                    <div class="filter-group">
                        <label for="year-filter">Năm học:</label>
                        <select id="year-filter" onchange="filterCoursesByYear()">
                            <option value="">Tất cả năm</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                        </select>
                    </div>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="course-search" placeholder="Tìm kiếm khóa học..." onkeyup="searchCourses()">
                    </div>
                </div>
                <button class="btn-primary" onclick="showCreateCourseModal()">
                    <i class="fas fa-plus"></i> Tạo Khóa Học Mới
                </button>
            </div>

            <!-- Course Statistics -->
            <div class="course-stats-grid">
                <div class="course-stat-card">
                    <div class="stat-icon"><i class="fas fa-book-open"></i></div>
                    <div class="stat-info">
                        <h3 id="total-courses">0</h3>
                        <p>Tổng Khóa Học</p>
                    </div>
                </div>
                <div class="course-stat-card">
                    <div class="stat-icon"><i class="fas fa-play"></i></div>
                    <div class="stat-info">
                        <h3 id="active-courses">0</h3>
                        <p>Đang Hoạt Động</p>
                    </div>
                </div>
                <div class="course-stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3 id="completed-courses">0</h3>
                        <p>Đã Hoàn Thành</p>
                    </div>
                </div>
                <div class="course-stat-card">
                    <div class="stat-icon"><i class="fas fa-lock"></i></div>
                    <div class="stat-info">
                        <h3 id="closed-courses">0</h3>
                        <p>Đã Đóng</p>
                    </div>
                </div>
            </div>

            <!-- Courses Grid -->
            <div id="courses-container">
                <div class="courses-grid" id="courses-grid">
                    <!-- Course cards will be loaded here -->
                </div>
                
                <!-- No courses message -->
                <div id="no-courses-message" class="no-results" style="display: none;">
                    <i class="fas fa-book-open"></i>
                    <h3>Chưa có khóa học nào</h3>
                    <p>Nhấn "Tạo Khóa Học Mới" để bắt đầu</p>
                </div>
                
                <!-- Loading -->
                <div id="courses-loading" class="loading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Đang tải khóa học...</p>
                </div>
            </div>
        </section>

        <!-- Reports Section -->
        <section id="view_reports" class="content-section">
            <h2>Báo Cáo & Phân Tích</h2>
            
            <div class="reports-grid">
                <div class="report-card">
                    <h4>Báo Cáo Tiến Độ Học Viên</h4>
                    <p>Theo dõi hiệu suất cá nhân và lớp học</p>
                    <button class="btn-primary">Tạo Báo Cáo</button>
                </div>
                
                <div class="report-card">
                    <h4>Báo Cáo Tài Chính</h4>
                    <p>Phân tích doanh thu, chi phí và lợi nhuận</p>
                    <button class="btn-primary">Tạo Báo Cáo</button>
                </div>
                
                <div class="report-card">
                    <h4>Hiệu Suất Giáo Viên</h4>
                    <p>Hiệu quả giảng dạy và phản hồi học viên</p>
                    <button class="btn-primary">Tạo Báo Cáo</button>
                </div>
                
                <div class="report-card">
                    <h4>Đăng Ký Khóa Học</h4>
                    <p>Xu hướng phổ biến và đăng ký khóa học</p>
                    <button class="btn-primary">Tạo Báo Cáo</button>
                </div>
            </div>
        </section>        
        
        <!-- Settings Section -->
        <section id="settings" class="content-section">
            <h2>Cài Đặt Hệ Thống</h2>
            
            <div class="settings-grid">
                <!-- Personal Information Form -->
                <div class="settings-card">
                    <h4><i class="fas fa-user-cog"></i> Thông tin cá nhân</h4>
                    <form id="personal-info-form">
                        <div class="setting-item">
                            <label for="fullname"><i class="fas fa-user"></i> Họ và tên:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($admin_data['full_name'] ?? $user_name ?? '') ?>">
                        </div>
                        <div class="setting-item">
                            <label for="email"><i class="fas fa-envelope"></i> Địa chỉ email:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($admin_data['email'] ?? '') ?>">
                        </div>
                        <div class="setting-item">
                            <label for="phone"><i class="fas fa-phone"></i> Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($admin_data['phone'] ?? '') ?>">
                        </div>
                        <button type="button" class="btn-primary" onclick="savePersonalInfo()">
                            <i class="fas fa-save"></i> Lưu Thay Đổi
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Form -->
                <div class="settings-card">
                    <h4><i class="fas fa-key"></i> Đổi mật khẩu</h4>
                    <form id="change-password-form">
                        <div class="setting-item">
                            <label for="current-password">Mật khẩu hiện tại:</label>
                            <input type="password" id="current-password" name="current-password" required>
                        </div>
                        <div class="setting-item">
                            <label for="new-password">Mật khẩu mới:</label>
                            <input type="password" id="new-password" name="new-password" required>
                        </div>
                        <div class="setting-item">
                            <label for="confirm-password">Xác nhận mật khẩu mới:</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <button type="button" class="btn-primary" onclick="changePassword()">
                            <i class="fas fa-key"></i> Đổi Mật Khẩu
                        </button>
                    </form>
                </div>
                
                <div class="settings-card">
                    <h4>Cài Đặt Khóa Học</h4>
                    <div class="setting-item">
                        <label>Thời Lượng Khóa Học Mặc Định:</label>
                        <select>
                            <option>8 tuần</option>
                            <option>10 tuần</option>
                            <option>12 tuần</option>
                        </select>
                    </div>
                    <div class="setting-item">
                        <label>Số Lượng Học Viên Tối Đa:</label>
                        <input type="number" value="15">
                    </div>
                    <button class="btn-primary">Lưu Thay Đổi</button>
                </div>
            </div>
        </section>
    </main>

    <!-- Create Course Modal -->
    <div id="create-course-modal" class="modal">
        <div class="modal-content course-modal">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Tạo khóa học mới</h3>
                <span class="close" onclick="closeCreateCourseModal()">&times;</span>
            </div>
            <form id="create-course-form" onsubmit="createCourse(event)">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="class-name">Tên lớp <span class="required">*</span></label>
                        <input type="text" id="class-name" name="class_name" required placeholder="Ví dụ: IR, SP, LS...">
                    </div>

                    <div class="form-group">
                        <label for="class-year">Năm học <span class="required">*</span></label>
                        <select id="class-year" name="class_year" required>
                            <option value="">Chọn năm học</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="class-level">Cấp lớp <span class="required">*</span></label>
                        <select id="class-level" name="class_level" required>
                            <option value="">Chọn cấp lớp</option>
                            <option value="Sơ cấp">Sơ cấp</option>
                            <option value="Trung cấp">Trung cấp</option>
                            <option value="Nâng cao">Nâng cao</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Môn học <span class="required">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="">Chọn môn học</option>
                            <option value="IELTS Speaking">IELTS Speaking</option>
                            <option value="IELTS Listening">IELTS Listening</option>
                            <option value="IELTS Reading">IELTS Reading</option>
                            <option value="IELTS Writing">IELTS Writing</option>
                            <option value="TOEIC Listening/Reading">TOEIC Listening/Reading</option>
                            <option value="TOEIC 4 Skills">TOEIC 4 Skills</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tutor-id">Giảng viên</label>
                        <select id="tutor-id" name="tutor_id">
                            <option value="">Chọn sau</option>
                            <!-- Will be populated by JavaScript -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="max-students">Số học sinh tối đa <span class="required">*</span></label>
                        <input type="number" id="max-students" name="max_students" required min="1" max="50" placeholder="15">
                    </div>

                    <div class="form-group">
                        <label for="sessions-total">Số buổi học <span class="required">*</span></label>
                        <input type="number" id="sessions-total" name="sessions_total" required min="1" placeholder="30">
                    </div>

                    <div class="form-group">
                        <label for="price-per-session">Giá tiền mỗi buổi (VNĐ) <span class="required">*</span></label>
                        <input type="number" id="price-per-session" name="price_per_session" required min="0" step="1000" placeholder="300000">
                    </div>

                    <div class="form-group">
                        <label for="schedule-time">Thời gian học <span class="required">*</span></label>
                        <input type="time" id="schedule-time" name="schedule_time" required>
                    </div>

                    <div class="form-group">
                        <label for="schedule-duration">Thời lượng (phút) <span class="required">*</span></label>
                        <input type="number" id="schedule-duration" name="schedule_duration" required min="30" step="15" placeholder="120">
                    </div>

                    <div class="form-group full-width">
                        <label>Ngày học trong tuần <span class="required">*</span></label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T2">
                                <span class="checkmark"></span>
                                Thứ 2
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T3">
                                <span class="checkmark"></span>
                                Thứ 3
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T4">
                                <span class="checkmark"></span>
                                Thứ 4
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T5">
                                <span class="checkmark"></span>
                                Thứ 5
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T6">
                                <span class="checkmark"></span>
                                Thứ 6
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="T7">
                                <span class="checkmark"></span>
                                Thứ 7
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="schedule_days" value="CN">
                                <span class="checkmark"></span>
                                Chủ nhật
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="start-date">Ngày khai giảng <span class="required">*</span></label>
                        <input type="date" id="start-date" name="start_date" required>
                    </div>

                    <div class="form-group">
                        <label for="end-date">Ngày kết thúc <span class="required">*</span></label>
                        <input type="date" id="end-date" name="end_date" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Mô tả lớp học</label>
                        <textarea id="description" name="description" rows="3" placeholder="Mô tả chi tiết về khóa học..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateCourseModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tạo khóa học
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Course Detail Modal -->
    <div id="course-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="course-detail-title">Chi tiết khóa học</h2>
                <span class="close" onclick="closeCourseDetailModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="course-detail-content">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const adminData = <?= json_encode([
            'stats' => $stats ?? [],
            'students' => $students ?? [],
            'tutors' => $tutors ?? [],
            'classes' => $classes ?? [],
            'activities' => $activities ?? [],
            'user_name' => $user_name ?? '',
            'admin_data' => $admin_data ?? null,
            // Add chart data
            'student_registration_trend' => $student_registration_trend ?? [],
            'class_level_distribution' => $class_level_distribution ?? [],
            'enrollment_trend' => $enrollment_trend ?? [],
            'current_month_stats' => $current_month_stats ?? []
        ]) ?>;
        
        // Debug: Log the data to console
        console.log('Admin data loaded:', adminData);
    </script>
    <script src="/webapp/View/Partial/DashboardNavbar.js" defer></script>
    <script src="/webapp/View/Admin/Admin.js" defer></script>
</body>
</html>

<?php
// Helper function for time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Vừa xong';
    if ($time < 3600) return floor($time/60) . ' phút trước';
    if ($time < 86400) return floor($time/3600) . ' giờ trước';
    if ($time < 2592000) return floor($time/86400) . ' ngày trước';
    if ($time < 31104000) return floor($time/2592000) . ' tháng trước';
    return floor($time/31104000) . ' năm trước';
}
?>
