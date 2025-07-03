<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Parent Dashboard - VAL EDU' ?></title>
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardHeader.css">
    <link rel="stylesheet" href="/webapp/View/Partial/DashboardNavbar.css">
    <link rel="stylesheet" href="/webapp/View/Parent/Parent.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>    
    <?php include __DIR__ . '/../Partial/DashboardHeader.php';?>
    <?php $user_role = 'parent'; include __DIR__ . '/../Partial/DashboardNavbar.php';?>

    <main class="main-content">        
        <!-- Overview Section -->
        <section id="overview" class="content-section active">
            <h2>Tổng Quan</h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-child"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($stats['total_children'] ?? 0) ?></h3>
                        <p>Tổng Số Con</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($stats['total_classes'] ?? 0) ?></h3>
                        <p>Lớp Học Đang Theo</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['average_attendance_rate'] ?? 0, 1) ?>%</h3>
                        <p>Tỷ Lệ Tham Gia Trung Bình</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['total_paid'] ?? 0) ?>đ</h3>
                        <p>Tổng Chi Phí Đã Thanh Toán</p>
                    </div>
                </div>
            </div>

            <!-- Children Overview -->
            <?php if (!empty($children)): ?>
                <div class="children-overview">
                    <h3>Tình Hình Học Tập Của Con</h3>
                    <div class="children-grid">
                        <?php foreach ($children as $child): ?>
                            <div class="child-overview-card">
                                <div class="child-header">
                                    <div class="child-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="child-info">
                                        <h4><?= htmlspecialchars($child['full_name']) ?></h4>
                                        <span class="relationship"><?= $child['relationship_type'] === 'father' ? '' : ($child['relationship_type'] === 'mother' ? '' : 'Người được giám hộ') ?></span>
                                    </div>
                                </div>
                                
                                <div class="child-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?= $child['enrolled_classes'] ?? 0 ?></span>
                                        <span class="stat-label">Lớp học</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?= number_format($child['academic_progress']['attendance_rate'] ?? 0, 1) ?>%</span>
                                        <span class="stat-label">Tỷ lệ tham gia</span>
                                    </div>
                                </div>
                                
                                <div class="recent-activity">
                                    <?php if (!empty($child['recent_attendance'])): ?>
                                        <p class="last-attendance">
                                            <i class="fas fa-calendar"></i>
                                            Buổi học gần nhất: <?= date('d/m/Y', strtotime($child['recent_attendance'][0]['session_date'])) ?>
                                            <span class="status <?= $child['recent_attendance'][0]['status'] === 'present' ? 'present' : 'absent' ?>">
                                                <?= $child['recent_attendance'][0]['status'] === 'present' ? 'Có mặt' : 'Vắng mặt' ?>
                                            </span>
                                        </p>
                                    <?php else: ?>
                                        <p class="no-activity"><i class="fas fa-info-circle"></i> Chưa có hoạt động học tập</p>
                                    <?php endif; ?>
                                </div>
                                
                                <button class="view-detail-btn" onclick="viewChildDetail(<?= $child['id'] ?>)">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Activities -->
            <div class="recent-activities">
                <h3>Hoạt Động Gần Đây</h3>
                <div class="activity-list">
                    <?php if (!empty($notifications)): ?>
                        <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?= htmlspecialchars($notification['message']) ?></p>
                                    <small><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></small>
                                    <?php if ($notification['student_name']): ?>
                                        <span class="student-tag"><?= htmlspecialchars($notification['student_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-activities">
                            <i class="fas fa-bell-slash"></i>
                            <p>Không có thông báo nào</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        
        <!-- My Children Section -->
        <section id="my_children" class="content-section">
            <h2>Con Của Tôi</h2>
            
            <?php if (isset($children) && !empty($children)): ?>
                <div class="children-grid">
                    <?php foreach ($children as $child): ?>
                        <div class="child-card">
                            <div class="child-header">
                                <div class="child-avatar">
                                    <?php 
                                        $initials = '';
                                        $names = explode(' ', $child['full_name']);
                                        foreach ($names as $name) {
                                            $initials .= strtoupper(substr($name, 0, 1));
                                        }
                                        echo substr($initials, 0, 2);
                                    ?>
                                </div>
                                <div class="child-info">
                                    <h4><?= htmlspecialchars($child['full_name']) ?></h4>
                                    <span class="relationship">
                                        <?php 
                                            $relationship_map = [
                                                'father' => 'Con trai/gái',
                                                'mother' => 'Con trai/gái', 
                                                'guardian' => 'Người được giám hộ'
                                            ];
                                            echo $relationship_map[$child['relationship_type']] ?? 'Con trai/gái';
                                        ?>
                                    </span>
                                    <?php if ($child['is_primary']): ?>
                                        <span class="primary-badge">Chính</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="child-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?= $child['enrolled_classes'] ?? 0 ?></span>
                                    <span class="stat-label">Lớp học</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?= number_format($child['academic_progress']['attendance_rate'] ?? 0, 1) ?>%</span>
                                    <span class="stat-label">Tỷ lệ tham gia</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?= number_format($child['total_paid'] ?? 0) ?>đ</span>
                                    <span class="stat-label">Đã thanh toán</span>
                                </div>
                            </div>
                            
                            <div class="recent-activity">
                                <?php if (!empty($child['recent_attendance'])): ?>
                                    <p class="last-attendance">
                                        <i class="fas fa-calendar"></i>
                                        Buổi học gần nhất: <?= date('d/m/Y', strtotime($child['recent_attendance'][0]['session_date'])) ?>
                                        <span class="status <?= $child['recent_attendance'][0]['status'] === 'present' ? 'present' : 'absent' ?>">
                                            <?= $child['recent_attendance'][0]['status'] === 'present' ? 'Có mặt' : 'Vắng mặt' ?>
                                        </span>
                                    </p>
                                <?php else: ?>
                                    <p class="no-activity"><i class="fas fa-info-circle"></i> Chưa có hoạt động học tập</p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="child-actions">
                                <button class="view-detail-btn" onclick="viewChildDetail(<?= $child['id'] ?>)">
                                    <i class="fas fa-eye"></i> Xem chi tiết
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-child"></i>
                    <p>Chưa có thông tin con em</p>
                    <small>Vui lòng liên hệ với trung tâm để cập nhật thông tin</small>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Child Detail Section -->
        <section id="child_detail" class="content-section">
            <div class="child-detail-header">
                <h2 id="child-detail-title">Chi Tiết Học Sinh</h2>
                <button onclick="goBackToChildren()" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </button>
            </div>
            
            <div id="child-detail-content">
                <!-- Content will be loaded dynamically -->
            </div>
        </section>
        
        <!-- Attendance Section -->
        <section id="attendance" class="content-section">
            <div class="attendance-header">
                <h2>Điểm Danh Con Em</h2>
                <div class="attendance-filters">
                    <select id="child-select" onchange="loadAttendanceData()">
                        <option value="">Chọn con</option>
                        <?php if (isset($children)): ?>
                            <?php foreach ($children as $child): ?>
                                <option value="<?= $child['id'] ?>"><?= htmlspecialchars($child['full_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select id="class-select" onchange="loadAttendanceData()">
                        <option value="">Tất cả lớp học</option>
                    </select>
                </div>
            </div>
            
            <div id="attendance-content">
                <div class="no-data">
                    <i class="fas fa-calendar-check"></i>
                    <p>Chọn con để xem điểm danh</p>
                </div>
            </div>
        </section>
        
        <!-- Payments Section -->
        <section id="payments" class="content-section">
            <h2>Thanh Toán Học Phí</h2>
            
            <!-- Payment Summary -->
            <div class="payment-summary">
                <div class="summary-card total">
                    <h4>Tổng Đã Thanh Toán</h4>
                    <span class="amount"><?= number_format($stats['total_paid'] ?? 0) ?>₫</span>
                </div>
                <div class="summary-card pending">
                    <h4>Chờ Thanh Toán</h4>
                    <span class="amount"><?= number_format($stats['pending_payments'] ?? 0) ?>₫</span>
                </div>
            </div>
            
            <!-- Payment History -->
            <div class="payment-history">
                <h3>Lịch Sử Thanh Toán</h3>
                <?php if (isset($payments) && !empty($payments)): ?>
                    <div class="table-container">
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Học Sinh</th>
                                    <th>Lớp Học</th>
                                    <th>Số Tiền</th>
                                    <th>Phương Thức</th>
                                    <th>Trạng Thái</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                                        <td><?= htmlspecialchars($payment['student_name']) ?></td>
                                        <td><?= htmlspecialchars($payment['class_name'] ?? 'N/A') ?></td>
                                        <td><?= number_format($payment['amount']) ?>₫</td>
                                        <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                        <td>
                                            <span class="status <?= $payment['status'] ?>">
                                                <?php
                                                $statusLabels = [
                                                    'completed' => 'Hoàn thành',
                                                    'pending' => 'Chờ xử lý',
                                                    'failed' => 'Thất bại'
                                                ];
                                                echo $statusLabels[$payment['status']] ?? $payment['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($payment['status'] === 'pending'): ?>
                                                <button class="pay-btn" onclick="showQRPayment(<?= $payment['id'] ?>, '<?= htmlspecialchars($payment['student_name']) ?>', '<?= htmlspecialchars($payment['class_name']) ?>', <?= $payment['final_amount'] ?? $payment['amount'] ?>, '<?= $payment['payment_method'] ?>')">
                                                    <i class="fas fa-qrcode"></i> Thanh toán
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">Đã xử lý</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-credit-card"></i>
                        <p>Chưa có lịch sử thanh toán</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Settings Section -->
        <section id="settings" class="content-section">
            <h2>Cài Đặt Tài Khoản</h2>
            
            <div class="settings-container">
                <!-- Personal Information -->
                <div class="settings-card">
                    <h3><i class="fas fa-user"></i> Thông Tin Cá Nhân</h3>
                    
                    <form id="personal-info-form">
                        <div class="form-group">
                            <label for="fullname"><i class="fas fa-user"></i> Họ và Tên</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($parent_data['full_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($parent_data['email'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Số Điện Thoại</label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($parent_data['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" onclick="savePersonalInfo()" class="save-btn">
                                <i class="fas fa-save"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="settings-card">
                    <h3><i class="fas fa-lock"></i> Đổi Mật Khẩu</h3>
                    
                    <div id="change-password-section" style="display: none;">
                        <form id="change-password-form">
                            <div class="form-group">
                                <label for="current_password"><i class="fas fa-key"></i> Mật Khẩu Hiện Tại</label>
                                <div class="password-input-group">
                                    <input type="password" id="current_password" name="current_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password"><i class="fas fa-lock"></i> Mật Khẩu Mới</label>
                                <div class="password-input-group">
                                    <input type="password" id="new_password" name="new_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-requirements">
                                    <small>Mật khẩu phải có ít nhất 8 ký tự</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password"><i class="fas fa-check"></i> Xác Nhận Mật Khẩu</label>
                                <div class="password-input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" onclick="hideChangePassword()" class="cancel-btn">
                                    <i class="fas fa-times"></i> Hủy
                                </button>
                                <button type="button" onclick="changePassword()" class="save-btn">
                                    <i class="fas fa-key"></i> Đổi Mật Khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div id="change-password-button" class="form-actions">
                        <button type="button" onclick="showChangePassword()" class="change-password-btn">
                            <i class="fas fa-key"></i> Đổi Mật Khẩu
                        </button>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div class="settings-card">
                    <h3><i class="fas fa-bell"></i> Cài Đặt Thông Báo</h3>
                    
                    <div class="notification-settings">
                        <div class="setting-item">
                            <div class="setting-info">
                                <span class="setting-title">Thông báo điểm danh</span>
                                <span class="setting-description">Nhận thông báo khi con vắng mặt</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <span class="setting-title">Thông báo thanh toán</span>
                                <span class="setting-description">Nhận thông báo về học phí và thanh toán</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <span class="setting-title">Báo cáo tiến độ</span>
                                <span class="setting-description">Nhận báo cáo tiến độ học tập hàng tuần</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Accept Connection Modal -->
    <div id="accept-connection-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-check"></i> Xác nhận kết nối</h3>
                <span class="close" onclick="closeAcceptConnectionModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="connection-student-info">
                    <!-- Student info will be populated here -->
                </div>
                <p>Bạn có muốn kết nối với học sinh này để theo dõi tiến độ học tập không?</p>
                <div class="connection-permissions">
                    <h4>Quyền truy cập sau khi kết nối:</h4>
                    <ul>
                        <li><i class="fas fa-check text-success"></i> Xem lịch học và điểm danh</li>
                        <li><i class="fas fa-check text-success"></i> Theo dõi tiến độ học tập</li>
                        <li><i class="fas fa-check text-success"></i> Nhận thông báo về học phí</li>
                        <li><i class="fas fa-check text-success"></i> Xem báo cáo học tập</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAcceptConnectionModal()">Hủy</button>
                <button class="btn btn-success" onclick="confirmAcceptConnection()">
                    <i class="fas fa-handshake"></i> Xác nhận kết nối
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-sign-out-alt"></i> Xác Nhận Đăng Xuất</h3>
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

    <!-- QR Payment Modal -->
    <div id="qr-payment-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Thanh toán QR Code</h3>
                <span class="close" onclick="closeQRPaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="payment-info">
                    <div class="payment-details">
                        <h4 id="qr-payment-title">Thông tin thanh toán</h4>
                        <div class="payment-row">
                            <span>Học viên:</span>
                            <span id="qr-student-name"></span>
                        </div>
                        <div class="payment-row">
                            <span>Lớp học:</span>
                            <span id="qr-class-name"></span>
                        </div>
                        <div class="payment-row">
                            <span>Số tiền:</span>
                            <strong id="qr-amount"></strong>
                        </div>
                        <div class="payment-row">
                            <span>Phương thức:</span>
                            <span id="qr-method"></span>
                        </div>
                    </div>
                    
                    <div class="qr-code-container">
                        <div class="qr-code">
                            <div class="qr-placeholder">
                                <i class="fas fa-qrcode"></i>
                                <p>Mã QR thanh toán</p>
                                <small>Quét mã để thanh toán</small>
                            </div>
                        </div>
                        <div class="bank-info">
                            <p><strong>Ngân hàng:</strong> VietcomBank</p>
                            <p><strong>Số tài khoản:</strong> 0123456789</p>
                            <p><strong>Chủ tài khoản:</strong> VALEDU EDUCATION</p>
                            <p><strong>Nội dung:</strong> <span id="qr-content"></span></p>
                        </div>
                    </div>
                </div>
                
                <div class="payment-status" id="payment-status" style="display: none;">
                    <i class="fas fa-check-circle"></i>
                    <p>Đang chờ thanh toán...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeQRPaymentModal()">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="simulateQRPayment()" id="confirm-payment-btn">
                    <i class="fas fa-credit-card"></i> Xác nhận đã thanh toán
                </button>
            </div>
        </div>
    </div>

    <style>
        .payment-info {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .qr-code-container {
            flex: 1;
            text-align: center;
        }
        
        .qr-code {
            width: 200px;
            height: 200px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), 
                        linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #f0f0f0 75%), 
                        linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        
        .qr-placeholder {
            text-align: center;
            color: #666;
        }
        
        .qr-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        
        .bank-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
        }
        
        .bank-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .payment-status {
            text-align: center;
            padding: 20px;
            background: #e8f5e8;
            border-radius: 8px;
            color: #28a745;
        }
        
        .payment-status i {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .pay-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .pay-btn:hover {
            background: #218838;
        }
        
        .pay-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .text-muted {
            color: #6c757d;
            font-style: italic;
        }
    </style>

    <script>
        // Pass PHP data to JavaScript
        const parentData = <?= json_encode([
            'children' => $children ?? [],
            'payments' => $payments ?? [],
            'stats' => $stats ?? [],
            'notifications' => $notifications ?? [],
            'user_name' => $user_name ?? '',
            'parent_data' => $parent_data ?? null
        ]) ?>;
        
        // Debug: Log the data to console
        console.log('Parent data loaded:', parentData);
    </script>
    
    <!-- Load scripts -->
    <script src="/webapp/View/Partial/DashboardNavbar.js"></script>
    <script src="/webapp/View/Parent/Parent.js"></script>
</body>
</html>