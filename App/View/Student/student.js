
// Sample data for classes
const classData = {
    'A1': {
        name: 'Lớp A1 - Tiếng Anh Cơ Bản',
        code: 'A1012025',
        teacher: 'Phạm Hải Nam',
        schedule: '9:00 - 11:00 T2,4,6',
        totalSessions: 20,
        attendedSessions: 8,
        absentSessions: 2,
        attendanceHistory: [
            { session: 1, date: '05/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 2, date: '07/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 3, date: '09/01/2025', time: '9:00-11:00', status: 'Vắng mặt' },
            { session: 4, date: '12/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 5, date: '14/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 6, date: '16/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 7, date: '19/01/2025', time: '9:00-11:00', status: 'Vắng mặt' },
            { session: 8, date: '21/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 9, date: '23/01/2025', time: '9:00-11:00', status: 'Có mặt' },
            { session: 10, date: '26/01/2025', time: '9:00-11:00', status: 'Có mặt' }
        ]
    },
    'B2': {
        name: 'Lớp B2 - Tiếng Anh Nâng Cao',
        code: 'B1012025',
        teacher: 'Đinh Thế Minh',
        schedule: '14:00 - 16:00 T3,5,7',
        totalSessions: 25,
        attendedSessions: 12,
        absentSessions: 1,
        attendanceHistory: [
            { session: 1, date: '06/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 2, date: '08/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 3, date: '10/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 4, date: '13/01/2025', time: '14:00-16:00', status: 'Vắng mặt' },
            { session: 5, date: '15/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 6, date: '17/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 7, date: '20/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 8, date: '22/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 9, date: '24/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 10, date: '27/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 11, date: '29/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 12, date: '31/01/2025', time: '14:00-16:00', status: 'Có mặt' },
            { session: 13, date: '03/02/2025', time: '14:00-16:00', status: 'Có mặt' }
        ]
    }
};

// Navigation functionality
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();

        // Remove active class from all nav links and sections
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

        // Add active class to clicked nav link
        this.classList.add('active');

        // Show corresponding section
        const targetId = this.getAttribute('href').substring(1);
        document.getElementById(targetId).classList.add('active');
    });
});

// Navbar toggle functionality
document.getElementById('navbarToggle').addEventListener('click', function () {
    const navbar = document.getElementById('navbar');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = this.querySelector('i');
    const toggleButton = this;

    if (window.innerWidth <= 768) {
        // Mobile behavior
        navbar.classList.toggle('show');
        return;
    }

    // Desktop behavior
    navbar.classList.toggle('collapsed');
    if (navbar.classList.contains('collapsed')) {
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
        mainContent.style.marginLeft = '95px';
        toggleButton.style.left = '60px';
    } else {
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
        mainContent.style.marginLeft = '285px';
        toggleButton.style.left = '250px';
    }
});

// Handle window resize
window.addEventListener('resize', function () {
    const navbar = document.getElementById('navbar');
    const mainContent = document.querySelector('.main-content');
    const toggleButton = document.getElementById('navbarToggle');

    if (window.innerWidth <= 768) {
        navbar.classList.remove('collapsed');
        navbar.classList.remove('show');
        mainContent.style.marginLeft = '';
        toggleButton.style.left = '';
    } else {
        if (navbar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '95px';
            toggleButton.style.left = '60px';
        } else {
            mainContent.style.marginLeft = '285px';
            toggleButton.style.left = '250px';
        }
    }
});

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

    // Basic validation
    if (!fullname.trim() || !email.trim() || !phone.trim()) {
        showMessage('Vui lòng điền đầy đủ thông tin!', 'error');
        return;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Địa chỉ email không hợp lệ!', 'error');
        return;
    }

    // Phone validation
    const phoneRegex = /^[0-9]{10,11}$/;
    if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
        showMessage('Số điện thoại không hợp lệ!', 'error');
        return;
    }

    // Simulate API call
    setTimeout(() => {
        showMessage('Thông tin cá nhân đã được cập nhật thành công!', 'success');

        // Update header info if needed
        const headerName = document.querySelector('.tutor-info span');
        if (headerName) {
            const firstName = fullname.split(' ').pop();
            headerName.textContent = `Chào mừng, ${firstName}`;
        }
    }, 500);
}

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
    const passwordRegex = /^.{6,}$/;
    if (!passwordRegex.test(newPassword)) {
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

    // Simulate API call
    setTimeout(() => {
        showMessage('Mật khẩu đã được thay đổi thành công!', 'success');
        hideChangePassword();
    }, 500);
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
    window.location.href = '../Home/homepage.html';
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