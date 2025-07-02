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
                    <input type="text" id="student-search" 
                           placeholder="Tìm kiếm học viên..." 
                           onkeyup="searchStudents()">
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mã học viên</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th>Ngày đăng ký</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <!-- Data will be loaded here by JavaScript -->
                    </tbody>
                </table>
                
                <!-- Loading State -->
                <div id="students-loading" class="table-loading-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Đang tải danh sách học viên...</p>
                </div>

                <!-- Empty State -->
                <div id="no-students" class="table-empty-state" style="display: none;">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Chưa có học viên nào</h3>
                    <p>Thêm học viên mới để bắt đầu</p>
                </div>
            </div>
        </section>        <!-- Manage Teachers Section -->
        <section id="manage_teachers" class="content-section">
            <h2>Quản Lý Giáo Viên</h2>
            
            <div class="section-header">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="tutor-search" 
                           placeholder="Tìm kiếm theo tên, email..." 
                           onkeyup="searchTutors()">
                </div>
                <button class="btn-primary" onclick="showAddTutorModal()">
                    <i class="fas fa-plus"></i> Thêm Giáo Viên Mới
                </button>
            </div>

            <div class="teachers-grid">
                <!-- Tutors will be loaded here by JavaScript -->
            </div>
        </section>        <!-- Manage Courses Section -->
        <section id="manage_courses" class="content-section">
            <h2>Quản Lý Khóa Học</h2>
            
            <div class="section-header">
                <div class="course-filters">
                    <div class="filter-group">
                        <label for="year-filter">Năm học:</label>
                        <select id="year-filter" onchange="filterCoursesByYear(this.value)">
                            <option value="">Tất cả năm</option>
                            <?php
                            $currentYear = date('Y');
                            for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
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
            <!-- Create Course Form -->
            <form id="create-course-form" class="course-form" onsubmit="createCourse(event)">
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
                <h3><i class="fas fa-info-circle"></i> Chi tiết khóa học</h3>
                <span class="close" onclick="closeCourseDetailModal()">&times;</span>
            </div>
            <div class="modal-body" id="course-detail-content">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div id="edit-course-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa khóa học</h3>
                <span class="close" onclick="closeEditCourseModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="edit-course-form" class="course-form" onsubmit="updateCourse(event)">
                    <!-- Form content will be populated by JavaScript -->
                </form>
            </div>
        </div>
    </div>

    <!-- Add Tutor Modal -->
    <div id="add-tutor-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Thêm Giáo Viên Mới</h3>
                <span class="close" onclick="closeAddTutorModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="add-tutor-form" class="form-grid" onsubmit="createTutor(event)">
                    <div class="form-group">
                        <label for="tutor-fullname">Họ và Tên <span class="required">*</span></label>
                        <input type="text" id="tutor-fullname" name="fullname" required
                               placeholder="Nhập họ và tên giáo viên">
                    </div>

                    <div class="form-group">
                        <label for="tutor-email">Email <span class="required">*</span></label>
                        <input type="email" id="tutor-email" name="email" required
                               placeholder="Nhập email">
                    </div>

                    <div class="form-group">
                        <label for="tutor-username">Tên đăng nhập <span class="required">*</span></label>
                        <input type="text" id="tutor-username" name="username" required
                               pattern="[a-zA-Z0-9_]+" placeholder="Chỉ chữ, số và dấu gạch dưới"
                               title="Chỉ được dùng chữ, số và dấu gạch dưới">
                    </div>

                    <div class="form-group">
                        <label for="tutor-password">Mật khẩu <span class="required">*</span></label>
                        <input type="password" id="tutor-password" name="password" required
                               minlength="6" placeholder="Tối thiểu 6 ký tự">
                    </div>

                    <div class="form-group">
                        <label for="tutor-phone">Số điện thoại</label>
                        <input type="tel" id="tutor-phone" name="phone"
                               placeholder="Nhập số điện thoại">
                    </div>

                    <div class="form-group">
                        <label for="tutor-discount">Tỷ lệ chiết khấu (%)</label>
                        <input type="number" id="tutor-discount" name="discount_percentage" 
                               min="0" max="100" step="0.1" value="0" 
                               placeholder="0.0">
                        <small class="form-help">Tỷ lệ chiết khấu từ doanh thu khóa học (0-100%)</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeAddTutorModal()">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo giáo viên
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tutor Detail Modal -->
    <div id="tutor-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-tie"></i> Thông tin giáo viên</h3>
                <span class="close" onclick="closeTutorDetailModal()">&times;</span>
            </div>
            <div class="modal-body" id="tutor-detail-content">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Edit Tutor Modal -->
    <div id="edit-tutor-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa thông tin giáo viên</h3>
                <span class="close" onclick="closeEditTutorModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="edit-tutor-form" class="form-grid" onsubmit="updateTutor(event)">
                    <input type="hidden" id="edit-tutor-id" name="tutor_id">
                    
                    <div class="form-group">
                        <label for="edit-tutor-fullname">Họ và Tên <span class="required">*</span></label>
                        <input type="text" id="edit-tutor-fullname" name="fullname" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-tutor-email">Email <span class="required">*</span></label>
                        <input type="email" id="edit-tutor-email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-tutor-phone">Số điện thoại</label>
                        <input type="tel" id="edit-tutor-phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="edit-tutor-subjects">Môn dạy <span class="required">*</span></label>
                        <select id="edit-tutor-subjects" name="subjects[]" multiple required>
                            <option value="IELTS Speaking">IELTS Speaking</option>
                            <option value="IELTS Listening">IELTS Listening</option>
                            <option value="IELTS Reading">IELTS Reading</option>
                            <option value="IELTS Writing">IELTS Writing</option>
                            <option value="TOEIC Listening/Reading">TOEIC Listening/Reading</option>
                            <option value="TOEIC 4 Skills">TOEIC 4 Skills</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditTutorModal()">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="edit-student-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa thông tin học viên</h3>
                <span class="close" onclick="closeEditStudentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="edit-student-form" class="form-grid" onsubmit="updateStudent(event)">
                    <input type="hidden" id="edit-student-id" name="student_id">
                    
                    <div class="form-group">
                        <label for="edit-student-fullname">Họ và Tên <span class="required">*</span></label>
                        <input type="text" id="edit-student-fullname" name="fullname" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-student-email">Email <span class="required">*</span></label>
                        <input type="email" id="edit-student-email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-student-phone">Số điện thoại</label>
                        <input type="tel" id="edit-student-phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="edit-student-status">Trạng thái</label>
                        <select id="edit-student-status" name="is_active">
                            <option value="1">Đang học</option>
                            <option value="0">Ngừng học</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="showAddToCourseModal()">
                            <i class="fas fa-plus-circle"></i> Thêm vào khóa học
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditStudentModal()">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Student To Course Modal -->
    <div id="add-student-course-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm vào khóa học</h3>
                <span class="close" onclick="closeAddStudentToCourseModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="available-courses-list">
                    <!-- Courses will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Student Detail Modal -->
    <div id="student-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-graduate"></i> Thông tin học viên</h3>
                <span class="close" onclick="closeStudentDetailModal()">&times;</span>
            </div>
            <div class="modal-body" id="student-detail-content">
                <!-- Content will be populated by JavaScript -->
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
