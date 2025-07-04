<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Giao Diện Giáo Viên - VAL EDU' ?></title>
    <link rel="stylesheet" href="/webapp/View/Partial/Footer.css">
    <link rel="stylesheet" href="/webapp/View/Partial/HomeHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardNavbar.css">
    <link rel="stylesheet" href="/webapp/View/Tutor/Tutor.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../Partial/DashboardHeader.php';?>
    <?php $user_role = 'tutor'; include __DIR__ . '/../Partial/DashboardNavbar.php';?>

    <main class="main-content">
        <section id="overview" class="content-section active">
            <h2>Lớp Giáo Viên Đang Dạy</h2>
            <div class="cards-container">
                <?php if (isset($classes) && !empty($classes)): ?>
                    <?php foreach ($classes as $class): ?>
                        <div class="class-card" onclick="showClassDetail('<?= htmlspecialchars($class['id'] ?? '') ?>')">
                            <h3><?= htmlspecialchars($class['class_name'] ?? 'Tên lớp không có') ?> - <?= htmlspecialchars($class['subject'] ?? '') ?></h3>
                            <div class="class-info">
                                <p><i class="fas fa-code"></i> Mã lớp: <?= htmlspecialchars($class['class_name'] . '.' . $class['class_year']) ?></p>
                                <p><i class="fas fa-calendar-alt"></i> Thời gian: <?= htmlspecialchars($class['schedule_time'] ?? '') ?> - <?= htmlspecialchars($class['schedule_days'] ?? '') ?></p>
                                <p><i class="fas fa-users"></i> Số học sinh: <?= htmlspecialchars($class['student_count'] ?? 0) ?></p>
                                <p><i class="fas fa-chart-line"></i> Tiến độ: <?= htmlspecialchars($class['sessions_completed'] ?? 0) ?>/<?= htmlspecialchars($class['sessions_total'] ?? 0) ?> buổi</p>
                            </div>
                            <button>Quản Lý Lớp</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <p>Bạn chưa được phân công dạy lớp học nào.</p>
                        <p>Vui lòng liên hệ với quản trị viên để được phân công lớp học.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="class-detail" class="content-section">
            <div class="class-detail">
                <div class="class-detail-header">
                    <h2 class="class-detail-title" id="class-detail-title">Quản lý lớp học</h2>
                    <button class="back-btn" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                </div>

                <div class="detail-grid">
                    <div class="detail-card">
                        <h4><i class="fas fa-info-circle"></i> Thông tin lớp học</h4>
                        <p><i class="fas fa-tag"></i> <strong>Tên lớp:</strong> <span id="detail-class-name"></span></p>
                        <p><i class="fas fa-code"></i> <strong>Mã lớp:</strong> <span id="detail-class-code"></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <strong>Thời gian:</strong> <span id="detail-schedule"></span></p>
                        <p><i class="fas fa-users"></i> <strong>Số học sinh:</strong> <span id="detail-student-count"></span></p>
                    </div>

                    <div class="detail-card">
                        <h4><i class="fas fa-chart-bar"></i> Thống kê tiến độ</h4>
                        <p><i class="fas fa-list-ol"></i> <strong>Tổng số buổi:</strong> <span id="detail-total-sessions"></span></p>
                        <p><i class="fas fa-check-circle"></i> <strong>Đã dạy:</strong> <span id="detail-completed-sessions"></span> buổi</p>
                        <p><i class="fas fa-clock"></i> <strong>Còn lại:</strong> <span id="detail-remaining-sessions"></span> buổi</p>
                        <p><i class="fas fa-percentage"></i> <strong>Tiến độ:</strong> <span id="detail-progress"></span>%</p>
                    </div>
                </div>

                <div class="action-buttons">
                    <button class="action-btn" onclick="startAttendance()">
                        <i class="fas fa-user-check"></i> Điểm Danh Hôm Nay
                    </button>
                    <button class="action-btn" style="background: #6f42c1;" onclick="viewStudentList()">
                        <i class="fas fa-users"></i> Xem Danh Sách Học Sinh
                    </button>
                    <button class="action-btn" style="background: #fd7e14;" onclick="viewAttendanceHistory()">
                        <i class="fas fa-history"></i> Lịch Sử Điểm Danh
                    </button>
                </div>
            </div>
        </section>

        <section id="attendance" class="content-section">
            <div class="attendance-section">
                <div class="attendance-header">
                    <h2>Điểm Danh Học Sinh</h2>
                    <button class="back-btn" onclick="backToClassDetail()">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                </div>

                <div class="session-info">
                    <h4>Thông tin buổi học</h4>
                    <p><strong>Lớp:</strong> <span id="attendance-class-name"></span></p>
                    <p><strong>Ngày:</strong> <span id="attendance-date"></span></p>
                    <p><strong>Buổi:</strong> <span id="attendance-session"></span></p>
                </div>

                <div class="success-message" id="attendance-success">
                    <i class="fas fa-check-circle"></i> Điểm danh đã được lưu thành công!
                </div>

                <div class="students-list" id="students-list">
                    <!-- Students will be populated here -->
                </div>

                <button class="complete-session-btn" onclick="completeSession()">
                    <i class="fas fa-check-circle"></i> Hoàn Thành Buổi Học
                </button>
            </div>
        </section>

        <section id="student-list" class="content-section">
            <div class="student-list-section">
                <div class="history-header">
                    <h2>Danh Sách Học Sinh</h2>
                    <button class="back-btn" onclick="backToClassDetail()">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="search-container">
                    <div class="search-input-group">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="student-search" placeholder="Tìm kiếm học sinh theo tên..." onkeyup="filterStudents()">
                        <button class="clear-search-btn" id="clear-search" onclick="clearSearch()" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="search-results-info" id="search-results-info" style="display: none;">
                        <span id="search-results-count">0</span> kết quả được tìm thấy
                    </div>
                </div>

                <div class="student-stats">
                    <div class="stat-card">
                        <h4>Tổng Học Sinh</h4>
                        <div class="stat-number" id="total-students">0</div>
                    </div>
                    <div class="stat-card">
                        <h4>Tỷ Lệ Tham Gia Trung Bình</h4>
                        <div class="stat-number" id="avg-attendance">0%</div>
                    </div>
                    <div class="stat-card">
                        <h4>Học Sinh Tham Gia Tốt</h4>
                        <div class="stat-number" id="good-attendance">0</div>
                    </div>
                </div>

                <div class="student-grid" id="student-grid">
                    <!-- Students will be populated here -->
                </div>

                <!-- No search results message -->
                <div class="no-search-results" id="no-search-results" style="display: none;">
                    <i class="fas fa-user-slash"></i>
                    <h3>Không tìm thấy học sinh</h3>
                    <p>Không có học sinh nào phù hợp với từ khóa tìm kiếm "<span id="search-term"></span>"</p>
                    <button onclick="clearSearch()" class="btn-secondary">
                        <i class="fas fa-redo"></i> Hiển thị tất cả
                    </button>
                </div>
            </div>
        </section>

        <section id="attendance-history" class="content-section">
            <div class="history-section">
                <div class="history-header">
                    <h2>Lịch Sử Điểm Danh</h2>
                    <button class="back-btn" onclick="backToClassDetail()">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                </div>

                <!-- Legend -->
                <div class="attendance-legend">
                    <div class="legend-item">
                        <i class="fas fa-check-circle legend-icon" style="color: #28a745;"></i>
                        <span>Có mặt</span>
                    </div>
                    <div class="legend-item">
                        <i class="fas fa-times-circle legend-icon" style="color: #dc3545;"></i>
                        <span>Vắng mặt</span>
                    </div>
                    <div class="legend-item">
                        <i class="fas fa-clock legend-icon" style="color: #ccc;"></i>
                        <span>Chưa diễn ra</span>
                    </div>
                    <div class="legend-item">
                        <i class="fas fa-question-circle legend-icon" style="color: #6c757d;"></i>
                        <span>Chưa điểm danh</span>
                    </div>
                </div>

                <div class="table-container">
                    <table class="attendance-table" id="attendance-history-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Họ và tên</th>
                                <!-- Session columns will be populated here -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Attendance history will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section id="settings" class="content-section">
            <div class="class-detail">
                <h2>Cài đặt</h2>

                <!-- Personal Information Form -->
                <div class="detail-card">
                    <h4><i class="fas fa-user-cog"></i> Thông tin cá nhân</h4>
                    <form id="personal-info-form">
                        <div class="form-group">
                            <label for="fullname"><i class="fas fa-user"></i> Họ và tên:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($tutor_data['full_name'] ?? $user_name ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Địa chỉ email:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($tutor_data['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($tutor_data['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="subject"><i class="fas fa-book"></i> Môn giảng dạy:</label>
                            <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($classes[0]['subject'] ?? 'N/A') ?>" readonly>
                            <small style="color: #666; font-style: italic;">Môn học không thể thay đổi</small>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="change-password-btn" onclick="showChangePassword()">
                                <i class="fas fa-key"></i> Đổi mật khẩu
                            </button>
                            <button type="button" class="save-btn" onclick="savePersonalInfo()">
                                <i class="fas fa-save"></i> Lưu thông tin
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="detail-card" id="change-password-section" style="display: none;">
                    <h4><i class="fas fa-key"></i> Đổi mật khẩu</h4>
                    <form id="change-password-form">
                        <div class="form-group">
                            <label for="current-password"><i class="fas fa-lock"></i> Mật khẩu hiện tại:</label>
                            <div class="password-input-group">
                                <input type="password" id="current-password" name="current-password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('current-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new-password"><i class="fas fa-lock"></i> Mật khẩu mới:</label>
                            <div class="password-input-group">
                                <input type="password" id="new-password" name="new-password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('new-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-requirements">
                                <small>Mật khẩu phải có ít nhất 6 ký tự</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm-password"><i class="fas fa-lock"></i> Xác nhận mật khẩu mới:</label>
                            <div class="password-input-group">
                                <input type="password" id="confirm-password" name="confirm-password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('confirm-password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="cancel-btn" onclick="hideChangePassword()">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                            <button type="button" class="save-btn" onclick="changePassword()">
                                <i class="fas fa-check"></i> Đổi mật khẩu
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Notification Settings -->
                <div class="detail-card">
                    <h4><i class="fas fa-bell"></i> Cài đặt thông báo</h4>
                    <div class="notification-settings">
                        <div class="setting-item">
                            <label class="switch">
                                <input type="checkbox" id="email-notifications" checked>
                                <span class="slider"></span>
                            </label>
                            <div class="setting-info">
                                <span class="setting-title">Thông báo qua email</span>
                                <span class="setting-description">Nhận thông báo về lịch dạy và thông tin lớp học</span>
                            </div>
                        </div>
                        <div class="setting-item">
                            <label class="switch">
                                <input type="checkbox" id="schedule-reminders" checked>
                                <span class="slider"></span>
                            </label>
                            <div class="setting-info">
                                <span class="setting-title">Nhắc nhở lịch dạy</span>
                                <span class="setting-description">Nhận nhắc nhở trước 30 phút khi có lớp học</span>
                            </div>
                        </div>
                        <div class="setting-item">
                            <label class="switch">
                                <input type="checkbox" id="attendance-alerts">
                                <span class="slider"></span>
                            </label>
                            <div class="setting-info">
                                <span class="setting-title">Cảnh báo điểm danh</span>
                                <span class="setting-description">Nhận thông báo khi học sinh vắng mặt nhiều</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="schedule" class="content-section">
            <div class="schedule-section">
                <div class="schedule-header">
                    <h2>Lịch Dạy</h2>
                    <div class="schedule-controls">
                        <button class="btn-secondary" onclick="previousMonth()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="current-month-year" class="month-year-display"></span>
                        <button class="btn-secondary" onclick="nextMonth()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button class="btn-primary" onclick="goToToday()">
                            <i class="fas fa-calendar-day"></i> Hôm nay
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-container">
                    <div class="calendar-header">
                        <div class="day-header">Chủ Nhật</div>
                        <div class="day-header">Thứ Hai</div>
                        <div class="day-header">Thứ Ba</div>
                        <div class="day-header">Thứ Tư</div>
                        <div class="day-header">Thứ Năm</div>
                        <div class="day-header">Thứ Sáu</div>
                        <div class="day-header">Thứ Bảy</div>
                    </div>
                    <div class="calendar-grid" id="calendar-grid">
                        <!-- Calendar days will be generated here -->
                    </div>
                </div>

                <!-- Legend -->
                <div class="calendar-legend">
                    <div class="legend-item">
                        <div class="legend-dot today-dot"></div>
                        <span>Hôm nay</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot schedule-dot"></div>
                        <span>Có lịch dạy</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot selected-dot"></div>
                        <span>Ngày được chọn</span>
                    </div>
                </div>

                <!-- Schedule Details Panel -->
                <div class="schedule-details" id="schedule-details" style="display: none;">
                    <div class="details-header">
                        <h3 id="selected-date-title">Lịch dạy ngày...</h3>
                        <button class="close-details-btn" onclick="closeScheduleDetails()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="details-content" id="schedule-details-content">
                        <!-- Schedule details will be populated here -->
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sign-out-alt"></i> Xác nhận đăng xuất</h3>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closeLogoutModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button class="logout-confirm-btn" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const tutorData = <?= json_encode([
            'classes' => $classes ?? [],
            'sessions' => $sessions ?? [],
            'stats' => $stats ?? [],
            'payments' => $payments ?? [],
            'user_name' => $user_name ?? '',
            'tutor_data' => $tutor_data ?? null
        ]) ?>;
        
        // Debug: Log the data to console
        console.log('Tutor data loaded:', tutorData);
    </script>
    <script src="/webapp/View/Tutor/Tutor.js" defer></script>
    <script src="/webapp/View/Partial/DashboardNavbar.js" defer></script>
    <?php include __DIR__ . '/../Partial/Footer.php';?>
</body>
</html>