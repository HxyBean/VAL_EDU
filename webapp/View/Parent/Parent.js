// Child detail management
function viewChildDetail(childId) {
    console.log('Viewing child detail for ID:', childId);
    
    const child = parentData.children.find(c => c.id == childId);
    if (!child) {
        showMessage('Không tìm thấy thông tin học sinh', 'error');
        return;
    }
    
    // Show loading
    document.getElementById('child-detail-content').innerHTML = '<div class="loading">Đang tải...</div>';
    
    // Switch to child detail section
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById('child_detail').classList.add('active');
    
    // Update navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Update title
    document.getElementById('child-detail-title').textContent = `Chi Tiết - ${child.full_name}`;
    
    // Load child detail content
    loadChildDetailContent(child);
}

function loadChildDetailContent(child) {
    const content = `
        <div class="child-detail-grid">
            <div class="detail-section">
                <h4><i class="fas fa-user"></i> Thông Tin Cơ Bản</h4>
                <div class="detail-item">
                    <span class="label">Họ và tên:</span>
                    <span class="value">${child.full_name}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Email:</span>
                    <span class="value">${child.email}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Số điện thoại:</span>
                    <span class="value">${child.phone || 'Chưa cập nhật'}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Ngày đăng ký:</span>
                    <span class="value">${new Date(child.registration_date).toLocaleDateString('vi-VN')}</span>
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-chart-line"></i> Tiến Độ Học Tập</h4>
                <div class="progress-stats">
                    <div class="progress-item">
                        <span class="label">Số lớp đã đăng ký:</span>
                        <span class="value">${child.enrolled_classes}</span>
                    </div>
                    <div class="progress-item">
                        <span class="label">Tỷ lệ tham gia:</span>
                        <span class="value">${(child.academic_progress?.attendance_rate || 0).toFixed(1)}%</span>
                    </div>
                    <div class="progress-item">
                        <span class="label">Số buổi đã hoàn thành:</span>
                        <span class="value">${child.academic_progress?.completed_sessions || 0}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section full-width">
                <h4><i class="fas fa-book"></i> Các Lớp Học Hiện Tại</h4>
                <div id="child-classes-${child.id}">
                    <div class="loading">Đang tải danh sách lớp học...</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('child-detail-content').innerHTML = content;
    
    // Load child classes
    loadChildClasses(child.id);
}

function loadChildClasses(childId) {
    fetch(`/webapp/api/parent/child-classes?child_id=${childId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayChildClasses(data.classes, childId);
            } else {
                document.getElementById(`child-classes-${childId}`).innerHTML = 
                    '<div class="no-data"><p>Không thể tải danh sách lớp học</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading child classes:', error);
            document.getElementById(`child-classes-${childId}`).innerHTML = 
                '<div class="no-data"><p>Lỗi khi tải danh sách lớp học</p></div>';
        });
}

function displayChildClasses(classes, childId) {
    if (!classes || classes.length === 0) {
        document.getElementById(`child-classes-${childId}`).innerHTML = 
            '<div class="no-data"><i class="fas fa-book"></i><p>Chưa đăng ký lớp học nào</p></div>';
        return;
    }
    
    const classesHtml = classes.map(cls => `
        <div class="class-item">
            <div class="class-header">
                <h5>${cls.class_name}</h5>
                <span class="class-status ${cls.enrollment_status}">${getStatusText(cls.enrollment_status)}</span>
            </div>
            <div class="class-details">
                <p><i class="fas fa-user-tie"></i> Giáo viên: ${cls.tutor_name || 'Chưa phân công'}</p>
                <p><i class="fas fa-calendar"></i> Ngày đăng ký: ${new Date(cls.enrollment_date).toLocaleDateString('vi-VN')}</p>
                <p><i class="fas fa-clock"></i> Tiến độ: ${cls.completed_sessions}/${cls.total_sessions} buổi</p>
            </div>
        </div>
    `).join('');
    
    document.getElementById(`child-classes-${childId}`).innerHTML = `
        <div class="classes-grid">
            ${classesHtml}
        </div>
    `;
}

function getStatusText(status) {
    const statusMap = {
        'active': 'Đang học',
        'completed': 'Đã hoàn thành',
        'suspended': 'Tạm dừng',
        'cancelled': 'Đã hủy'
    };
    return statusMap[status] || 'Không xác định';
}

function goBackToChildren() {
    // Switch back to children section
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById('my_children').classList.add('active');
    
    // Update navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    document.querySelector('a[href="#my_children"]').classList.add('active');
}

// Attendance management
function viewChildAttendance(childId) {
    console.log('Viewing attendance for child ID:', childId);
    
    // Switch to attendance section
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById('attendance').classList.add('active');
    
    // Update navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    document.querySelector('a[href="#attendance"]').classList.add('active');
    
    // Set the child in the dropdown
    document.getElementById('child-select').value = childId;
    
    // Load attendance data
    loadAttendanceData();
}

function loadAttendanceData() {
    const childId = document.getElementById('child-select').value;
    const classId = document.getElementById('class-select').value;
    
    if (!childId) {
        document.getElementById('attendance-content').innerHTML = `
            <div class="no-data">
                <i class="fas fa-calendar-check"></i>
                <p>Chọn con để xem điểm danh</p>
            </div>
        `;
        return;
    }
    
    // Show loading
    document.getElementById('attendance-content').innerHTML = '<div class="loading">Đang tải dữ liệu điểm danh...</div>';
    
    // Load classes for selected child
    loadChildClassesForAttendance(childId);
    
    // Load attendance records
    const url = `/webapp/api/parent/child-attendance?child_id=${childId}${classId ? `&class_id=${classId}` : ''}`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendanceData(data.attendance, data.stats);
            } else {
                document.getElementById('attendance-content').innerHTML = `
                    <div class="no-data">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không thể tải dữ liệu điểm danh</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading attendance:', error);
            document.getElementById('attendance-content').innerHTML = `
                <div class="no-data">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Lỗi khi tải dữ liệu điểm danh</p>
                </div>
            `;
        });
}

function loadChildClassesForAttendance(childId) {
    fetch(`/webapp/api/parent/child-classes?child_id=${childId}`)
        .then(response => response.json())
        .then(data => {
            const classSelect = document.getElementById('class-select');
            classSelect.innerHTML = '<option value="">Tất cả lớp học</option>';
            
            if (data.success && data.classes) {
                data.classes.forEach(cls => {
                    classSelect.innerHTML += `<option value="${cls.id}">${cls.class_name}</option>`;
                });
            }
        })
        .catch(error => console.error('Error loading classes:', error));
}

function displayAttendanceData(attendance, stats) {
    if (!attendance || attendance.length === 0) {
        document.getElementById('attendance-content').innerHTML = `
            <div class="no-data">
                <i class="fas fa-calendar-check"></i>
                <p>Chưa có dữ liệu điểm danh</p>
            </div>
        `;
        return;
    }
    
    const attendanceHtml = `
        <div class="attendance-stats">
            <div class="stat-card">
                <h4>Tổng buổi học</h4>
                <span class="stat-number">${stats?.total_sessions || 0}</span>
            </div>
            <div class="stat-card">
                <h4>Có mặt</h4>
                <span class="stat-number present">${stats?.present_count || 0}</span>
            </div>
            <div class="stat-card">
                <h4>Vắng mặt</h4>
                <span class="stat-number absent">${stats?.absent_count || 0}</span>
            </div>
            <div class="stat-card">
                <h4>Tỷ lệ tham gia</h4>
                <span class="stat-number">${(stats?.attendance_rate || 0).toFixed(1)}%</span>
            </div>
        </div>
        
        <div class="attendance-table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Lớp học</th>
                        <th>Buổi học</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    ${attendance.map(record => `
                        <tr>
                            <td>${new Date(record.session_date).toLocaleDateString('vi-VN')}</td>
                            <td>${record.class_name}</td>
                            <td>${record.session_title || `Buổi ${record.session_number}`}</td>
                            <td>
                                <span class="attendance-status ${record.status}">
                                    ${getAttendanceStatusText(record.status)}
                                </span>
                            </td>
                            <td>${record.notes || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('attendance-content').innerHTML = attendanceHtml;
}

function getAttendanceStatusText(status) {
    const statusMap = {
        'present': 'Có mặt',
        'absent': 'Vắng mặt',
        'late': 'Đi muộn',
        'excused': 'Vắng có phép'
    };
    return statusMap[status] || 'Không xác định';
}

// Payment management
function viewPaymentDetail(paymentId) {
    console.log('Viewing payment detail for ID:', paymentId);
    
    const payment = parentData.payments.find(p => p.id == paymentId);
    if (!payment) {
        showMessage('Không tìm thấy thông tin thanh toán', 'error');
        return;
    }
    
    // Create modal for payment detail
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.style.display = 'block';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-receipt"></i> Chi Tiết Thanh Toán</h3>
            </div>
            <div class="modal-body">
                <div class="payment-detail">
                    <div class="detail-row">
                        <span class="label">Mã giao dịch:</span>
                        <span class="value">#${payment.id}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Học sinh:</span>
                        <span class="value">${payment.student_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Lớp học:</span>
                        <span class="value">${payment.class_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Số tiền:</span>
                        <span class="value amount">${parseInt(payment.amount).toLocaleString('vi-VN')}₫</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Ngày thanh toán:</span>
                        <span class="value">${new Date(payment.payment_date).toLocaleDateString('vi-VN')}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Trạng thái:</span>
                        <span class="value">
                            <span class="status ${payment.status}">${getPaymentStatusText(payment.status)}</span>
                        </span>
                    </div>
                    ${payment.notes ? `
                        <div class="detail-row">
                            <span class="label">Ghi chú:</span>
                            <span class="value">${payment.notes}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
            <div class="modal-footer">
                <button class="cancel-btn" onclick="closePaymentModal()">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closePaymentModal();
        }
    });
}

function getPaymentStatusText(status) {
    const statusMap = {
        'completed': 'Đã thanh toán',
        'pending': 'Chờ xử lý',
        'failed': 'Thất bại',
        'cancelled': 'Đã hủy'
    };
    return statusMap[status] || 'Không xác định';
}

function closePaymentModal() {
    const modal = document.querySelector('.modal');
    if (modal && !modal.id) { // Not the logout modal
        modal.remove();
    }
}

// Settings management
document.addEventListener('DOMContentLoaded', function() {
    // Personal info form
    document.getElementById('personal-info-form').addEventListener('submit', function(e) {
        e.preventDefault();
        savePersonalInfo();
    });
    
    // Change password form
    document.getElementById('change-password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        changePassword();
    });
});

function savePersonalInfo() {
    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;

    // Basic validation
    if (!fullname.trim() || !email.trim()) {
        showMessage('Vui lòng điền đầy đủ họ tên và email!', 'error');
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Địa chỉ email không hợp lệ!', 'error');
        return;
    }

    // Show loading
    const saveBtn = document.querySelector('#personal-info-form .save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('fullname', fullname);
    formData.append('email', email);
    formData.append('phone', phone);

    fetch('/webapp/api/parent/update-profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Cập nhật thông tin thành công!', 'success');
            // Update parentData
            if (parentData.parent_data) {
                parentData.parent_data.full_name = fullname;
                parentData.parent_data.email = email;
                parentData.parent_data.phone = phone;
            }
        } else {
            showMessage(data.message || 'Có lỗi xảy ra khi cập nhật thông tin!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối! Vui lòng thử lại.', 'error');
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

function changePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage('Vui lòng điền đầy đủ tất cả các trường!', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showMessage('Mật khẩu mới và xác nhận mật khẩu không khớp!', 'error');
        return;
    }

    if (newPassword.length < 8) {
        showMessage('Mật khẩu mới phải có ít nhất 8 ký tự!', 'error');
        return;
    }

    // Password strength validation
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(newPassword)) {
        showMessage('Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường và số!', 'error');
        return;
    }

    // Show loading
    const changeBtn = document.querySelector('#change-password-form .change-password-btn');
    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đổi mật khẩu...';
    changeBtn.disabled = true;

    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);

    fetch('/webapp/api/parent/change-password', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Đổi mật khẩu thành công!', 'success');
            // Clear form
            document.getElementById('change-password-form').reset();
        } else {
            showMessage(data.message || 'Có lỗi xảy ra khi đổi mật khẩu!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối! Vui lòng thử lại.', 'error');
    })
    .finally(() => {
        changeBtn.innerHTML = originalText;
        changeBtn.disabled = false;
    });
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Logout functions
function showLogoutModal() {
    console.log('showLogoutModal called');
    const modal = document.getElementById('logout-modal');
    console.log('Modal element:', modal);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        console.log('Modal should now be visible');
    } else {
        console.error('Modal element not found!');
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function confirmLogout() {
    window.location.href = '/webapp/logout';
}

// Close modal when clicking outside
window.onclick = function (event) {
    const modal = document.getElementById('logout-modal');
    if (event.target === modal) {
        closeLogoutModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeLogoutModal();
        closePaymentModal();
    }
});

// Utility functions
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.innerHTML = `
        <div class="message-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="message-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Insert at top of main content
    const mainContent = document.querySelector('.main-content');
    mainContent.insertBefore(messageDiv, mainContent.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageDiv && messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

// Connection Request Functions
function acceptConnectionRequest(requestId) {
    const modal = document.getElementById('accept-connection-modal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    modal.setAttribute('data-request-id', requestId);
    
    // Load student info for the modal
    loadStudentInfoForConnection(requestId);
}

function loadStudentInfoForConnection(requestId) {
    const infoContainer = document.getElementById('connection-student-info');
    infoContainer.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Đang tải thông tin...</div>';
    
    fetch(`/webapp/api/parent/get-connection-request-info?id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                infoContainer.innerHTML = `
                    <div class="student-info-card">
                        <div class="student-avatar">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="student-details">
                            <h4>${data.student.full_name}</h4>
                            <p><i class="fas fa-envelope"></i> ${data.student.email}</p>
                            <p><i class="fas fa-graduation-cap"></i> ${data.student.enrolled_classes} lớp đang học</p>
                            <p><i class="fas fa-calendar"></i> Tham gia từ ${new Date(data.student.created_at).toLocaleDateString('vi-VN')}</p>
                        </div>
                    </div>
                `;
            } else {
                infoContainer.innerHTML = '<p class="error">Không thể tải thông tin học sinh</p>';
            }
        })
        .catch(error => {
            infoContainer.innerHTML = '<p class="error">Lỗi kết nối</p>';
        });
}

function closeAcceptConnectionModal() {
    const modal = document.getElementById('accept-connection-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmAcceptConnection() {
    const modal = document.getElementById('accept-connection-modal');
    const requestId = modal.getAttribute('data-request-id');
    
    fetch('/webapp/api/parent/accept-connection-request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${requestId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                closeAcceptConnectionModal();
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message || 'Không thể chấp nhận yêu cầu', 'error');
            }
        })
        .catch(error => {
            showMessage('Lỗi kết nối', 'error');
        });
}

function rejectConnectionRequest(requestId) {
    if (!confirm('Bạn có chắc chắn muốn từ chối yêu cầu kết nối này?')) {
        return;
    }
    
    fetch('/webapp/api/parent/reject-connection-request', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `request_id=${requestId}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message || 'Không thể từ chối yêu cầu', 'error');
            }
        })
        .catch(error => {
            showMessage('Lỗi kết nối', 'error');
        });
}

// Initialize dashboard
console.log('Parent.js loaded successfully');