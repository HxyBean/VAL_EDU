// Show class detail
function showClassDetail(classId) {
    const data = classData[classId];
    if (!data) return;

    // Update class detail information
    document.getElementById('class-detail-title').textContent = data.name;
    document.getElementById('detail-class-name').textContent = data.name;
    document.getElementById('detail-class-code').textContent = data.code;
    document.getElementById('detail-teacher').textContent = data.teacher;
    document.getElementById('detail-schedule').textContent = data.schedule;
    document.getElementById('detail-total-sessions').textContent = data.totalSessions;
    document.getElementById('detail-attended').textContent = data.attendedSessions;
    document.getElementById('detail-absent').textContent = data.absentSessions;

    // Calculate attendance percentage
    const percentage = Math.round((data.attendedSessions / (data.attendedSessions + data.absentSessions)) * 100);
    document.getElementById('detail-percentage').textContent = percentage + '%';

    // Update attendance history table
    const historyTable = document.getElementById('attendance-history');
    historyTable.innerHTML = '';

    data.attendanceHistory.forEach(record => {
        const row = document.createElement('tr');
        const statusClass = record.status === 'Có mặt' ? 'text-success' : 'text-danger';
        row.innerHTML = `
                    <td>Buổi ${record.session}</td>
                    <td>${record.date}</td>
                    <td>${record.time}</td>
                    <td class="${statusClass}">
                        <i class="fas ${record.status === 'Có mặt' ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                        ${record.status}
                    </td>
                `;
        historyTable.appendChild(row);
    });

    // Show class detail section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('class-detail').classList.add('active');
}

// Go back to overview
function goBack() {
    document.getElementById('class-detail').classList.remove('active');
    document.getElementById('overview').classList.add('active');

    // Update nav link active state
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector('.nav-link[href="#overview"]').classList.add('active');
}

// Add some additional CSS for status colors
const style = document.createElement('style');
style.textContent = `
            .text-success {
                color: #28a745 !important;
            }
            .text-danger {
                color: #dc3545 !important;
            }
        `;
document.head.appendChild(style);

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

function savePersonalInfo() {
    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;

    fetch('/webapp/student/update-info', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `fullname=${encodeURIComponent(fullname)}&email=${encodeURIComponent(email)}&phone=${encodeURIComponent(phone)}`
    })
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                const headerName = document.querySelector('.tutor-info span');
                if (headerName) headerName.textContent = `Chào mừng, ${fullname}`;
            }
        })
        .catch(() => showMessage('Lỗi hệ thống!', 'error'));
}

function changePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage('Vui lòng điền đầy đủ thông tin mật khẩu!', 'error');
        return;
    }
    if (newPassword !== confirmPassword) {
        showMessage('Mật khẩu xác nhận không khớp!', 'error');
        return;
    }

    fetch('/webapp/student/change-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `current_password=${encodeURIComponent(currentPassword)}&new_password=${encodeURIComponent(newPassword)}`
    })
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) hideChangePassword();
        })
        .catch(() => showMessage('Lỗi hệ thống!', 'error'));
}

function showMessage(message, type) {
    // Remove existing messages
    const existingMessages = document.querySelectorAll('.success-message, .error-message');
    existingMessages.forEach(msg => msg.remove());

    // Create new message
    const messageDiv = document.createElement('div');
    messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
    messageDiv.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;

    // Insert at the top of settings section
    const settingsSection = document.querySelector('#settings .class-detail');
    settingsSection.insertBefore(messageDiv, settingsSection.firstChild.nextSibling);

    // Show message
    messageDiv.style.display = 'block';
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto hide after 5 seconds
    setTimeout(() => {
        messageDiv.style.display = 'none';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}

// Add input validation listeners
document.addEventListener('DOMContentLoaded', function () {
    // Real-time password validation
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function () {
            const requirements = document.querySelector('.password-requirements small');
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/;

            if (this.value && passwordRegex.test(this.value)) {
                requirements.style.color = '#28a745';
                requirements.innerHTML = '<i class="fas fa-check"></i> Mật khẩu hợp lệ';
            } else if (this.value) {
                requirements.style.color = '#dc3545';
                requirements.innerHTML = '<i class="fas fa-times"></i> Mật khẩu phải có ít nhất 6 ký tự';
            } else {
                requirements.style.color = '#666';
                requirements.innerHTML = 'Mật khẩu phải có ít nhất 6 ký tự';
            }
        });
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function () {
            const newPassword = document.getElementById('new-password').value;
            if (this.value && this.value === newPassword) {
                this.style.borderColor = '#28a745';
            } else if (this.value) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#ddd';
            }
        });
    }
});
// Logout modal functions
function showLogoutModal() {
    document.getElementById('logout-modal').style.display = 'block';
}

function closeLogoutModal() {
    document.getElementById('logout-modal').style.display = 'none';
}

function confirmLogout() {
    // Thay đổi URL này thành đường dẫn home của bạn
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
