
// Show class detail with real data
function showClassDetail(classId) {
    console.log('Showing class detail for ID:', classId);
    console.log('Available student data:', studentData);
    
    // Find the class data from studentData passed from PHP
    const classData = studentData.courses.find(course => course.id == classId);
    if (!classData) {
        console.error('Class data not found for ID:', classId);
        showMessage('Không tìm thấy thông tin lớp học', 'error');
        return;
    }

    console.log('Class data found:', classData);

    // Update class detail information
    document.getElementById('class-detail-title').textContent = classData.class_name || 'Không có tên lớp';
    document.getElementById('detail-class-name').textContent = classData.class_name || 'Không có tên lớp';
    document.getElementById('detail-class-code').textContent = (classData.class_name || '') + '.' + (classData.class_year || '');
    document.getElementById('detail-teacher').textContent = classData.instructor_name || 'Chưa phân công';
    
    // Format schedule
    const scheduleTime = classData.schedule_time || '';
    const scheduleDays = classData.schedule_days || '';
    const scheduleText = scheduleTime && scheduleDays ? `${scheduleTime} - ${scheduleDays}` : 'Chưa có lịch học';
    document.getElementById('detail-schedule').textContent = scheduleText;
    
    document.getElementById('detail-total-sessions').textContent = classData.sessions_total || classData.total_sessions_scheduled || 0;

    // Filter attendance for this class
    const classAttendance = studentData.attendance.filter(att => att.class_id == classId);
    console.log('Class attendance:', classAttendance);
    
    const presentCount = classAttendance.filter(att => att.status === 'present').length;
    const absentCount = classAttendance.filter(att => att.status === 'absent').length;
    
    document.getElementById('detail-attended').textContent = presentCount;
    document.getElementById('detail-absent').textContent = absentCount;

    // Calculate attendance percentage
    const totalAttended = presentCount + absentCount;
    const percentage = totalAttended > 0 ? Math.round((presentCount / totalAttended) * 100) : 0;
    document.getElementById('detail-percentage').textContent = percentage + '%';

    // Update attendance history table
    const historyTable = document.getElementById('attendance-history');
    historyTable.innerHTML = '';

    if (classAttendance.length > 0) {
        classAttendance.forEach((record, index) => {
            const row = document.createElement('tr');
            const statusClass = record.status === 'present' ? 'text-success' : 'text-danger';
            const statusText = record.status === 'present' ? 'Có mặt' : 'Vắng mặt';
            const statusIcon = record.status === 'present' ? 'fa-check-circle' : 'fa-times-circle';
            
            // Format date
            const sessionDate = new Date(record.session_date);
            const formattedDate = sessionDate.toLocaleDateString('vi-VN');
            
            // Format time
            const sessionTime = record.session_time || 'N/A';
            
            row.innerHTML = `
                <td>Buổi ${index + 1}</td>
                <td>${formattedDate}</td>
                <td>${sessionTime}</td>
                <td class="${statusClass}">
                    <i class="fas ${statusIcon}"></i>
                    ${statusText}
                </td>
            `;
            historyTable.appendChild(row);
        });
    } else {
        // Show no data message if no attendance records
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="4" style="text-align: center; color: #666; font-style: italic;">
                Chưa có dữ liệu điểm danh
            </td>
        `;
        historyTable.appendChild(row);
    }

    // Show class detail section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('class-detail').classList.add('active');
    
    // Update navigation
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
}

// Go back to overview
function goBack() {
    document.getElementById('class-detail').classList.remove('active');
    document.getElementById('overview').classList.add('active');

    // Update nav link active state
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector('.nav-link[href="#overview"]').classList.add('active');
}

// Settings functions
function showChangePassword() {
    const changePasswordSection = document.getElementById('change-password-section');
    changePasswordSection.style.display = 'block';
    changePasswordSection.scrollIntoView({ behavior: 'smooth' });
}

function hideChangePassword() {
    const changePasswordSection = document.getElementById('change-password-section');
    changePasswordSection.style.display = 'none';
    
    // Clear form
    document.getElementById('change-password-form').reset();
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.querySelector(`button[onclick="togglePassword('${inputId}')"] i`);
    
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

// Save personal information with API call
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

    // Phone validation (optional but if provided, must be valid)
    if (phone && !/^[0-9]{10,11}$/.test(phone.replace(/\s/g, ''))) {
        showMessage('Số điện thoại không hợp lệ!', 'error');
        return;
    }

    // Show loading
    const saveBtn = document.querySelector('.save-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    saveBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.append('full_name', fullname);
    formData.append('email', email);
    formData.append('phone', phone);

    // Call API
    fetch('/webapp/api/student/update-profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            
            // Update header info if needed
            const headerName = document.querySelector('.tutor-info span');
            if (headerName) {
                const firstName = fullname.split(' ').pop();
                headerName.textContent = `Chào mừng, ${firstName}`;
            }
        } else {
            showMessage(data.message || 'Cập nhật thất bại', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    })
    .finally(() => {
        // Restore button
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Change password with API call
function changePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    // Validation
    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage('Vui lòng điền đầy đủ thông tin mật khẩu!', 'error');
        return;
    }

    // Password strength validation
    if (newPassword.length < 6) {
        showMessage('Mật khẩu mới phải có ít nhất 6 ký tự!', 'error');
        return;
    }

    // Confirm password validation
    if (newPassword !== confirmPassword) {
        showMessage('Mật khẩu xác nhận không khớp!', 'error');
        return;
    }

    // Check if new password is different from current
    if (currentPassword === newPassword) {
        showMessage('Mật khẩu mới phải khác mật khẩu hiện tại!', 'error');
        return;
    }

    // Show loading
    const changeBtn = document.querySelector('#change-password-section .save-btn');
    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thay đổi...';
    changeBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    // Call API
    fetch('/webapp/api/student/change-password', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            hideChangePassword();
        } else {
            showMessage(data.message || 'Đổi mật khẩu thất bại', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    })
    .finally(() => {
        // Restore button
        changeBtn.innerHTML = originalText;
        changeBtn.disabled = false;
    });
}

// Logout modal functions
function showLogoutModal() {
    document.getElementById('logout-modal').style.display = 'block';
}

function closeLogoutModal() {
    document.getElementById('logout-modal').style.display = 'none';
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
    }
});

// Show message function
function showMessage(message, type = 'info') {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.alert-message');
    existingMessages.forEach(msg => msg.remove());
    
    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert-message alert-${type}`;
    messageDiv.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        max-width: 400px;
        animation: slideInRight 0.3s ease-out;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    `;
    
    if (type === 'success') {
        messageDiv.style.backgroundColor = '#28a745';
    } else if (type === 'error') {
        messageDiv.style.backgroundColor = '#dc3545';
    } else {
        messageDiv.style.backgroundColor = '#17a2b8';
    }
    
    messageDiv.textContent = message;
    
    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(messageDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 3000);
}

// Add CSS for status colors
const style = document.createElement('style');
style.textContent = `
    .text-success {
        color: #28a745 !important;
        font-weight: 600;
    }
    .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }
`;
document.head.appendChild(style);