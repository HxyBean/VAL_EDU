<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Giao Diện Học Sinh - VAL EDU' ?></title>
    <link rel="stylesheet" href="/webapp/View/Partial/Footer.css">
    <link rel="stylesheet" href="/webapp/View/Partial/HomeHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardNavbar.css">
    <link rel="stylesheet" href="/webapp/View/Student/Student.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include __DIR__ . '/../Partial/DashboardHeader.php';?>
    <?php $user_role = 'student'; include __DIR__ . '/../Partial/DashboardNavbar.php';?>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sign-out-alt"></i> Xác nhận đăng xuất</h3>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn đăng xuất?</p>
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

    <main class="main-content">
        <section id="overview" class="content-section active">
            <h2>Lớp đang theo học</h2>
            <div class="cards-container">
                <?php if (isset($courses) && !empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="class-card" onclick="showClassDetail('<?= htmlspecialchars($course['id'] ?? '') ?>')">
                            <h3><?= htmlspecialchars($course['class_name'] ?? 'Tên lớp không có') ?></h3>
                            <div class="class-info">
                                <p><i class="fas fa-code"></i> Mã lớp: <?= htmlspecialchars($course['class_name'] . '.' . $course['class_year']) ?></p>
                                <p><i class="fas fa-user-tie"></i> Giảng viên: <?= htmlspecialchars($course['instructor_name'] ?? 'Chưa phân công') ?></p>
                                <p><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($course['schedule_time'] ?? '') ?> - <?= htmlspecialchars($course['schedule_days'] ?? '') ?></p>
                                <p><i class="fas fa-chart-line"></i> Tiến độ: <?= htmlspecialchars($course['sessions_completed'] ?? 0) ?>/<?= htmlspecialchars($course['sessions_total'] ?? 0) ?> buổi</p>
                            </div>
                            <button>Xem Chi Tiết</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-graduation-cap"></i>
                        <p>Bạn chưa đăng ký lớp học nào.</p>
                        <a href="/webapp/" class="btn-primary">Khám phá khóa học</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Rest of the sections remain the same for now -->
        <section id="class-detail" class="content-section">
            <div class="class-detail">
                <div class="class-detail-header">
                    <h2 class="class-detail-title" id="class-detail-title">Thông tin lớp học</h2>
                    <button class="back-btn" onclick="goBack()">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </button>
                </div>

                <div class="detail-grid">
                    <div class="detail-card">
                        <h4><i class="fas fa-info-circle"></i> Thông tin cơ bản</h4>
                        <p><i class="fas fa-tag"></i> <strong>Tên lớp:</strong> <span id="detail-class-name"></span></p>
                        <p><i class="fas fa-code"></i> <strong>Mã lớp:</strong> <span id="detail-class-code"></span></p>
                        <p><i class="fas fa-user-tie"></i> <strong>Giảng viên:</strong> <span id="detail-teacher"></span></p>
                        <p><i class="fas fa-calendar-alt"></i> <strong>Lịch học:</strong> <span id="detail-schedule"></span></p>
                    </div>

                    <div class="detail-card">
                        <h4><i class="fas fa-chart-bar"></i> Thống kê điểm danh</h4>
                        <p><i class="fas fa-list-ol"></i> <strong>Tổng số buổi:</strong> <span id="detail-total-sessions"></span></p>
                        <div class="attendance-stats">
                            <div class="stat-item">
                                <div class="stat-number" id="detail-attended">0</div>
                                <div class="stat-label">Đã tham gia</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="detail-absent">0</div>
                                <div class="stat-label">Vắng mặt</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number" id="detail-percentage">0%</div>
                                <div class="stat-label">Tỷ lệ tham gia</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <h4><i class="fas fa-calendar-check"></i> Lịch sử điểm danh</h4>
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Buổi</th>
                                <th>Ngày</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="attendance-history">
                            <!-- Attendance history will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Parent Connections Section -->
        <section id="parent_connections" class="content-section">
            <h2>Kết nối với phụ huynh</h2>
            <div class="cards-container">
                <!-- Existing linked parents -->
                <?php if (isset($linked_parents) && !empty($linked_parents)): ?>
                    <?php foreach ($linked_parents as $parent): ?>
                        <div class="parent-card">
                            <div class="parent-avatar">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h4><?= htmlspecialchars($parent['full_name']) ?></h4>
                            <div class="parent-info">
                                <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($parent['email']) ?></p>
                                <p><i class="fas fa-phone"></i> <?= htmlspecialchars($parent['phone'] ?? 'Chưa có') ?></p>
                                <span class="status-badge connected">Đã kết nối</span>
                            </div>
                            <div class="parent-actions">
                                <button class="btn-secondary" onclick="disconnectParent(<?= $parent['id'] ?>)">
                                    <i class="fas fa-unlink"></i> Hủy kết nối
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Add new parent connection -->
                <div class="parent-card add-parent-card" onclick="showAddParentModal()">
                    <div class="add-parent-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h4>Kết nối phụ huynh</h4>
                    <p>Thêm phụ huynh để theo dõi tiến độ học tập và nhận thông báo quan trọng</p>
                </div>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="content-section">
            <div class="class-detail">
                <h2>Cài đặt</h2>

                <!-- Personal Information Form -->
                <div class="detail-card">
                    <h4><i class="fas fa-user-cog"></i> Thông tin cá nhân</h4>
                    <form id="personal-info-form">
                        <div class="form-group">
                            <label for="fullname"><i class="fas fa-user"></i> Họ và tên:</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($student_data['full_name'] ?? $user_name ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Địa chỉ email:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($student_data['email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Số điện thoại:</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($student_data['phone'] ?? '') ?>">
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
                                <span class="setting-description">Nhận thông báo về lịch học và điểm danh</span>
                            </div>
                        </div>
                        <div class="setting-item">
                            <label class="switch">
                                <input type="checkbox" id="schedule-reminders" checked>
                                <span class="slider"></span>
                            </label>
                            <div class="setting-info">
                                <span class="setting-title">Nhắc nhở lịch học</span>
                                <span class="setting-description">Nhận nhắc nhở trước 30 phút khi có lớp học</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Add Parent Connection Modal -->
    <div id="add-parent-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Kết nối với phụ huynh</h3>
                <span class="close" onclick="closeAddParentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Invite Form -->
                <div id="invite-form">
                    <h4>Gửi lời mời kết nối</h4>
                    <form id="parent-invite-form">
                        <div class="form-group">
                            <label for="parent-email">Email phụ huynh <span class="required">*</span></label>
                            <input type="email" id="parent-email" name="parent_email" required>
                        </div>
                        <div class="form-group">
                            <label for="parent-phone">Số điện thoại (tùy chọn)</label>
                            <input type="tel" id="parent-phone" name="parent_phone">
                        </div>
                        <div class="form-group">
                            <label for="parent-name">Tên phụ huynh</label>
                            <input type="text" id="parent-name" name="parent_name" placeholder="Ví dụ: Bố/Mẹ của [Tên học sinh]">
                        </div>
                        <div class="form-group">
                            <label for="connection-message">Lời nhắn (tùy chọn)</label>
                            <textarea id="connection-message" name="message" rows="3" placeholder="Xin chào, con muốn kết nối tài khoản để bố/mẹ có thể theo dõi tiến độ học tập..."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" onclick="closeAddParentModal()">Hủy</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi lời mời
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript (ensure it's only declared once)
        if (typeof studentData === 'undefined') {
            window.studentData = <?= json_encode([
                'courses' => $courses ?? [],
                'attendance' => $attendance ?? [],
                'stats' => $stats ?? [],
                'payments' => $payments ?? [],
                'user_name' => $user_name ?? '',
                'student_data' => $student_data ?? null
            ]) ?>;
            
            // Debug: Log the data to console
            console.log('Student data loaded:', window.studentData);
        }
    </script>
    
    <?php if (!isset($scripts_loaded)): ?>
        <script src="/webapp/View/Student/Student.js" defer></script>
        <script src="/webapp/View/Partial/DashboardNavbar.js" defer></script>
        <?php $scripts_loaded = true; ?>
    <?php endif; ?>
    
    <?php include __DIR__ . '/../Partial/Footer.php';?>
</body>
</html>