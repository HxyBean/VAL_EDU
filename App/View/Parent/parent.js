
// Sample data for children
const childrenData = {
    child1: {
        name: 'Nguyễn Minh Hiếu',
        avatar: 'H',
        classes: [
            {
                name: 'A1 - Tiếng Anh Cơ Bản',
                teacher: 'Phạm Hải Nam',
                schedule: '9:00 - 11:00 T2,4,6',
                progress: { current: 10, total: 20 },
                startDate: '01/10/2024',
                endDate: '20/02/2025',
                monthlyFee: 1200000,
                discount: 120000,
                unpaidMonths: ['12/2024', '01/2025'],
                attendanceHistory: [
                    { session: 1, date: '02/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 2, date: '04/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 3, date: '07/10/2024', time: '9:00-11:00', status: 'Vắng mặt', note: 'Con bị ốm' },
                    { session: 4, date: '09/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 5, date: '11/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 6, date: '14/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 7, date: '16/10/2024', time: '9:00-11:00', status: 'Vắng mặt', note: 'Gia đình có việc' },
                    { session: 8, date: '18/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 9, date: '21/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 10, date: '23/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 11, date: '25/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' },
                    { session: 12, date: '28/10/2024', time: '9:00-11:00', status: 'Có mặt', note: '' }
                ]
            },
            {
                name: 'Math Basic - Toán Cơ Bản',
                teacher: 'Nguyễn Thị Mai',
                schedule: '14:00 - 16:00 T3,5,7',
                progress: { current: 7, total: 15 },
                startDate: '15/11/2024',
                endDate: '15/02/2025',
                monthlyFee: 1000000,
                discount: 100000,
                unpaidMonths: ['01/2025'],
                attendanceHistory: [
                    { session: 1, date: '17/11/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 2, date: '19/11/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 3, date: '21/11/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 4, date: '24/11/2024', time: '14:00-16:00', status: 'Vắng mặt', note: 'Con đi du lịch cùng gia đình' },
                    { session: 5, date: '26/11/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 6, date: '28/11/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 7, date: '01/12/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 8, date: '03/12/2024', time: '14:00-16:00', status: 'Có mặt', note: '' }
                ]
            }
        ],
        payments: [
            { month: '12/2024', class: 'A1 - Tiếng Anh Cơ Bản', fee: 1200000, discount: 120000, final: 1080000, status: 'unpaid' },
            { month: '01/2025', class: 'A1 - Tiếng Anh Cơ Bản', fee: 1200000, discount: 120000, final: 1080000, status: 'unpaid' },
            { month: '01/2025', class: 'Math Basic - Toán Cơ Bản', fee: 1000000, discount: 100000, final: 900000, status: 'unpaid' }
        ]
    },
    child2: {
        name: 'Nguyễn Minh Anh',
        avatar: 'A',
        classes: [
            {
                name: 'B2 - Tiếng Anh Nâng Cao',
                teacher: 'Đinh Thế Minh',
                schedule: '14:00 - 16:00 T3,5,7',
                progress: { current: 15, total: 25 },
                startDate: '01/09/2024',
                endDate: '01/03/2025',
                monthlyFee: 1200000,
                discount: 120000,
                unpaidMonths: [],
                attendanceHistory: [
                    { session: 1, date: '03/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 2, date: '05/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 3, date: '07/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 4, date: '10/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 5, date: '12/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 6, date: '14/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 7, date: '17/09/2024', time: '14:00-16:00', status: 'Vắng mặt', note: 'Con tham gia hoạt động trường' },
                    { session: 8, date: '19/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 9, date: '21/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 10, date: '24/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 11, date: '26/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 12, date: '28/09/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 13, date: '01/10/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 14, date: '03/10/2024', time: '14:00-16:00', status: 'Có mặt', note: '' },
                    { session: 15, date: '05/10/2024', time: '14:00-16:00', status: 'Có mặt', note: '' }
                ]
            }
        ],
        payments: [
            { month: '12/2024', class: 'B2 - Tiếng Anh Nâng Cao', fee: 1200000, discount: 120000, final: 1080000, status: 'paid' },
            { month: '01/2025', class: 'B2 - Tiếng Anh Nâng Cao', fee: 1200000, discount: 120000, final: 1080000, status: 'paid' }
        ]
    }
};

// Navigation functionality
document.addEventListener('DOMContentLoaded', function () {
    const navbarToggle = document.getElementById('navbarToggle');
    const navbar = document.getElementById('navbar');
    const mainContent = document.getElementById('mainContent');
    const navLinks = document.querySelectorAll('.nav-link');

    // Toggle navbar
    navbarToggle.addEventListener('click', function () {
        navbar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');

        const icon = navbarToggle.querySelector('i');
        if (navbar.classList.contains('collapsed')) {
            icon.className = 'fas fa-chevron-right';
        } else {
            icon.className = 'fas fa-chevron-left';
        }
    });

    // Navigation link handling
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all links
            navLinks.forEach(l => l.classList.remove('active'));

            // Add active class to clicked link
            this.classList.add('active');

            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show target section
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).classList.add('active');
        });
    });

    // Mobile navbar toggle
    if (window.innerWidth <= 768) {
        navbarToggle.addEventListener('click', function () {
            navbar.classList.toggle('show');
        });
    }
});

// Child detail functions
function showChildDetail(childId) {
    const child = childrenData[childId];
    if (!child) return;

    // Update child info
    document.getElementById('detail-avatar').textContent = child.avatar;
    document.getElementById('detail-child-name').textContent = child.name;

    // Populate classes
    const classesContainer = document.getElementById('classes-container');
    classesContainer.innerHTML = '';

    child.classes.forEach((classInfo, index) => {
        const progressPercent = Math.round((classInfo.progress.current / classInfo.progress.total) * 100);
        const attendedCount = classInfo.attendanceHistory.filter(record => record.status === 'Có mặt').length;
        const absentCount = classInfo.attendanceHistory.filter(record => record.status === 'Vắng mặt').length;
        const attendancePercent = Math.round((attendedCount / classInfo.attendanceHistory.length) * 100);

        const classCard = document.createElement('div');
        classCard.className = 'class-card';
        classCard.style.cursor = 'pointer';
        classCard.innerHTML = `
                    <h4><i class="fas fa-graduation-cap"></i> ${classInfo.name}</h4>
                    <p><i class="fas fa-user-tie"></i> <strong>Giáo viên:</strong> ${classInfo.teacher}</p>
                    <p><i class="fas fa-calendar-alt"></i> <strong>Lịch học:</strong> ${classInfo.schedule}</p>
                    <p><i class="fas fa-calendar-check"></i> <strong>Thời gian:</strong> ${classInfo.startDate} - ${classInfo.endDate}</p>
                    <p><i class="fas fa-chart-line"></i> <strong>Tiến độ:</strong> ${classInfo.progress.current}/${classInfo.progress.total} buổi (${progressPercent}%)</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${progressPercent}%"></div>
                    </div>
                    <div style="margin-top: 1rem; padding: 0.75rem; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #108AB1;">
                        <p style="margin: 0; margin-bottom: 0.5rem;"><i class="fas fa-calendar-check"></i> <strong>Điểm danh:</strong></p>
                        <div style="display: flex; gap: 1rem; font-size: 0.9rem;">
                            <span><i class="fas fa-check-circle" style="color: #28a745;"></i> Có mặt: <strong>${attendedCount}</strong></span>
                            <span><i class="fas fa-times-circle" style="color: #dc3545;"></i> Vắng: <strong>${absentCount}</strong></span>
                            <span><i class="fas fa-percentage" style="color: #108AB1;"></i> Tỷ lệ: <strong>${attendancePercent}%</strong></span>
                        </div>
                    </div>

                `;

        // Add click event to show attendance detail
        classCard.addEventListener('click', function (e) {
            if (e.target.tagName !== 'BUTTON') {
                showAttendanceDetail(childId, index);
            }
        });

        classesContainer.appendChild(classCard);
    });

    // Populate class fees
    const classFeesContainer = document.getElementById('class-fees');
    classFeesContainer.innerHTML = '';

    child.classes.forEach(classInfo => {
        const hasUnpaid = classInfo.unpaidMonths.length > 0;
        const finalAmount = classInfo.monthlyFee - classInfo.discount;
        const totalUnpaid = hasUnpaid ? classInfo.unpaidMonths.length * finalAmount : 0;

        const classFeeCard = document.createElement('div');
        classFeeCard.className = `class-fee-card ${hasUnpaid ? 'unpaid' : 'paid'}`;
        classFeeCard.innerHTML = `
                    <div class="class-fee-header">
                        <h4 class="class-fee-title">${classInfo.name}</h4>
                        <span class="fee-status ${hasUnpaid ? 'unpaid' : 'paid'}">
                            ${hasUnpaid ? 'Chưa đóng' : 'Đã đóng'}
                        </span>
                    </div>
                    <div class="fee-breakdown">
                        <div class="fee-item">
                            <span>Học phí hàng tháng:</span>
                            <span class="fee-amount">${formatCurrency(classInfo.monthlyFee)}</span>
                        </div>
                        <div class="fee-item">
                            <span>Giảm giá (10%):</span>
                            <span class="fee-amount discount">-${formatCurrency(classInfo.discount)}</span>
                        </div>
                        <div class="fee-item">
                            <span>Thành tiền/tháng:</span>
                            <span class="fee-amount">${formatCurrency(finalAmount)}</span>
                        </div>
                        ${hasUnpaid ? `
                        <div class="fee-item">
                            <span>Số tháng chưa đóng:</span>
                            <span class="fee-amount unpaid">${classInfo.unpaidMonths.length} tháng</span>
                        </div>
                        <div class="fee-item total">
                            <span><strong>Tổng cần đóng:</strong></span>
                            <span class="fee-amount unpaid"><strong>${formatCurrency(totalUnpaid)}</strong></span>
                        </div>
                        <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                            Các tháng: ${classInfo.unpaidMonths.join(', ')}
                        </div>
                        ` : `
                        <div class="fee-item total">
                            <span><strong>Trạng thái:</strong></span>
                            <span class="fee-amount paid"><strong>Đã thanh toán đầy đủ</strong></span>
                        </div>
                        `}
                    </div>
                `;
        classFeesContainer.appendChild(classFeeCard);
    });

    // Populate payment history
    const paymentHistory = document.getElementById('payment-history');
    paymentHistory.innerHTML = '';

    child.payments.forEach(payment => {
        const row = document.createElement('tr');
        row.innerHTML = `
                    <td>${payment.month}</td>
                    <td>${payment.class}</td>
                    <td>${formatCurrency(payment.fee)}</td>
                    <td>${formatCurrency(payment.discount)}</td>
                    <td>${formatCurrency(payment.final)}</td>
                    <td><span class="status-${payment.status}">${payment.status === 'paid' ? 'Đã đóng' : 'Chưa đóng'}</span></td>
                `;
        paymentHistory.appendChild(row);
    });

    // Show detail section
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById('child-detail').classList.add('active');
}

// Attendance detail modal functions
function showAttendanceDetail(childId, classIndex) {
    const child = childrenData[childId];
    const classInfo = child.classes[classIndex];

    if (!classInfo || !classInfo.attendanceHistory) return;

    // Update modal title
    document.getElementById('modal-class-name').textContent = `Chi tiết điểm danh - ${classInfo.name}`;

    // Calculate statistics
    const totalSessions = classInfo.attendanceHistory.length;
    const attendedSessions = classInfo.attendanceHistory.filter(record => record.status === 'Có mặt').length;
    const absentSessions = classInfo.attendanceHistory.filter(record => record.status === 'Vắng mặt').length;
    const attendancePercentage = Math.round((attendedSessions / totalSessions) * 100);

    // Update statistics
    document.getElementById('modal-total-sessions').textContent = totalSessions;
    document.getElementById('modal-attended').textContent = attendedSessions;
    document.getElementById('modal-absent').textContent = absentSessions;
    document.getElementById('modal-percentage').textContent = attendancePercentage + '%';

    // Update attendance history table
    const historyTable = document.getElementById('modal-attendance-history');
    historyTable.innerHTML = '';

    classInfo.attendanceHistory.forEach(record => {
        const row = document.createElement('tr');
        const statusClass = record.status === 'Có mặt' ? 'status-paid' : 'status-unpaid';
        const statusIcon = record.status === 'Có mặt' ? 'fa-check-circle' : 'fa-times-circle';

        row.innerHTML = `
                    <td>Buổi ${record.session}</td>
                    <td>${record.date}</td>
                    <td>${record.time}</td>
                    <td>
                        <span class="${statusClass}">
                            <i class="fas ${statusIcon}"></i> ${record.status}
                        </span>
                    </td>
                    <td>${record.note || '-'}</td>
                `;
        historyTable.appendChild(row);
    });

    // Show modal
    document.getElementById('attendance-modal').style.display = 'block';
}

function closeAttendanceModal() {
    document.getElementById('attendance-modal').style.display = 'none';
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

function savePersonalInfo() {
    const fullname = document.getElementById('fullname').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const address = document.getElementById('address').value;

    // Basic validation
    if (!fullname.trim() || !email.trim() || !phone.trim() || !address.trim()) {
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
        const headerName = document.querySelector('.parent-info span');
        if (headerName) {
            const firstName = fullname.split(' ').slice(-2).join(' ');
            headerName.textContent = `Chào ${firstName}`;
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
    const settingsSection = document.querySelector('#settings .settings-container');
    settingsSection.insertBefore(messageDiv, settingsSection.firstChild);

    // Show message
    messageDiv.style.display = 'block';
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto hide after 5 seconds
    setTimeout(() => {
        messageDiv.style.display = 'none';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}

// Logout modal functions
function showLogoutModal() {
    document.getElementById('logout-modal').style.display = 'block';
}

function closeLogoutModal() {
    document.getElementById('logout-modal').style.display = 'none';
}

function confirmLogout() {
    // Redirect to login page
    window.location.href = '../Home/homepage.html';
}

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function goBack() {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById('overview').classList.add('active');
}

// Close modal when clicking outside
window.onclick = function (event) {
    const logoutModal = document.getElementById('logout-modal');
    const attendanceModal = document.getElementById('attendance-modal');

    if (event.target === logoutModal) {
        logoutModal.style.display = 'none';
    }
    if (event.target === attendanceModal) {
        attendanceModal.style.display = 'none';
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeLogoutModal();
        closeAttendanceModal();
    }
});

// Add input validation listeners
document.addEventListener('DOMContentLoaded', function () {
    // Real-time password validation
    const newPasswordInput = document.getElementById('new-password');
    const confirmPasswordInput = document.getElementById('confirm-password');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function () {
            const requirements = document.querySelector('.password-requirements small');
            const passwordRegex = /^.{6,}$/;

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
