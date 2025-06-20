
// Sample data for classes
const classData = {
    'A1': {
        name: 'Lớp A1 - Tiếng Anh Cơ Bản',
        code: 'A1012025',
        schedule: '9:00 - 11:00 T2,4,6',
        studentCount: 25,
        totalSessions: 20,
        completedSessions: 8,
        students: [
            { id: 1, name: 'Nguyễn Văn A', email: 'nguyenvana@email.com' },
            { id: 2, name: 'Trần Thị B', email: 'tranthib@email.com' },
            { id: 3, name: 'Lê Văn C', email: 'levanc@email.com' },
            { id: 4, name: 'Phạm Thị D', email: 'phamthid@email.com' },
            { id: 5, name: 'Hoàng Văn E', email: 'hoangvane@email.com' },
            { id: 6, name: 'Vũ Thị F', email: 'vuthif@email.com' },
            { id: 7, name: 'Đặng Văn G', email: 'dangvang@email.com' },
            { id: 8, name: 'Bùi Thị H', email: 'buithih@email.com' }
        ],
        attendanceHistory: {
            1: { 1: 'present', 2: 'present', 3: 'absent', 4: 'present', 5: 'present', 6: 'present', 7: 'absent', 8: 'present' },
            2: { 1: 'present', 2: 'absent', 3: 'present', 4: 'present', 5: 'absent', 6: 'present', 7: 'present', 8: 'present' },
            3: { 1: 'absent', 2: 'present', 3: 'present', 4: 'present', 5: 'present', 6: 'absent', 7: 'present', 8: 'present' },
            4: { 1: 'present', 2: 'present', 3: 'present', 4: 'absent', 5: 'present', 6: 'present', 7: 'present', 8: 'absent' },
            5: { 1: 'present', 2: 'present', 3: 'present', 4: 'present', 5: 'present', 6: 'present', 7: 'present', 8: 'present' },
            6: { 1: 'present', 2: 'absent', 3: 'present', 4: 'present', 5: 'present', 6: 'present', 7: 'absent', 8: 'present' },
            7: { 1: 'present', 2: 'present', 3: 'absent', 4: 'present', 5: 'absent', 6: 'present', 7: 'present', 8: 'present' },
            8: { 1: 'present', 2: 'present', 3: 'present', 4: 'present', 5: 'present', 6: 'present', 7: 'present', 8: 'present' }
        }
    },
    'B2': {
        name: 'Lớp B2 - Tiếng Anh Nâng Cao',
        code: 'B2012025',
        schedule: '14:00 - 16:00 T3,5,7',
        studentCount: 20,
        totalSessions: 25,
        completedSessions: 12,
        students: [
            { id: 9, name: 'Nguyễn Thị I', email: 'nguyenthii@email.com' },
            { id: 10, name: 'Trần Văn J', email: 'tranvanj@email.com' },
            { id: 11, name: 'Lê Thị K', email: 'lethik@email.com' },
            { id: 12, name: 'Phạm Văn L', email: 'phamvanl@email.com' },
            { id: 13, name: 'Hoàng Thị M', email: 'hoangthim@email.com' },
            { id: 14, name: 'Vũ Văn N', email: 'vuvann@email.com' }
        ],
        attendanceHistory: {
            1: { 9: 'present', 10: 'present', 11: 'present', 12: 'present', 13: 'absent', 14: 'present' },
            2: { 9: 'present', 10: 'present', 11: 'absent', 12: 'present', 13: 'present', 14: 'present' },
            3: { 9: 'present', 10: 'absent', 11: 'present', 12: 'present', 13: 'present', 14: 'present' }
        }
    },
    'C1': {
        name: 'Lớp C1 - Giao Tiếp Tiếng Anh',
        code: 'C1012025',
        schedule: '19:00 - 21:00 T2,4,6',
        studentCount: 18,
        totalSessions: 15,
        completedSessions: 5,
        students: [
            { id: 15, name: 'Đỗ Văn O', email: 'dovano@email.com' },
            { id: 16, name: 'Cao Thị P', email: 'caothip@email.com' },
            { id: 17, name: 'Lý Văn Q', email: 'lyvanq@email.com' },
            { id: 18, name: 'Mai Thị R', email: 'maithir@email.com' }
        ],
        attendanceHistory: {
            1: { 15: 'present', 16: 'present', 17: 'present', 18: 'absent' },
            2: { 15: 'present', 16: 'absent', 17: 'present', 18: 'present' },
            3: { 15: 'absent', 16: 'present', 17: 'present', 18: 'present' },
            4: { 15: 'present', 16: 'present', 17: 'present', 18: 'present' },
            5: { 15: 'present', 16: 'present', 17: 'absent', 18: 'present' }
        }
    }
};

let currentClass = null;
let attendanceData = {};

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
        mainContent.style.width = 'calc(100vw - 95px)';
        mainContent.classList.add('collapsed');
        mainContent.classList.remove('expanded');
        toggleButton.style.left = '60px';
    } else {
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
        mainContent.style.marginLeft = '285px';
        mainContent.style.width = 'calc(100vw - 285px)';
        mainContent.classList.add('expanded');
        mainContent.classList.remove('collapsed');
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
        mainContent.style.width = '';
        mainContent.classList.remove('collapsed', 'expanded');
        toggleButton.style.left = '';
    } else {
        if (navbar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '95px';
            mainContent.style.width = 'calc(100vw - 95px)';
            mainContent.classList.add('collapsed');
            mainContent.classList.remove('expanded');
            toggleButton.style.left = '60px';
        } else {
            mainContent.style.marginLeft = '285px';
            mainContent.style.width = 'calc(100vw - 285px)';
            mainContent.classList.add('expanded');
            mainContent.classList.remove('collapsed');
            toggleButton.style.left = '250px';
        }
    }
});

// Show class detail
function showClassDetail(classId) {
    const data = classData[classId];
    if (!data) return;

    currentClass = classId;

    // Update class detail information
    document.getElementById('class-detail-title').textContent = data.name;
    document.getElementById('detail-class-name').textContent = data.name;
    document.getElementById('detail-class-code').textContent = data.code;
    document.getElementById('detail-schedule').textContent = data.schedule;
    document.getElementById('detail-student-count').textContent = data.studentCount;
    document.getElementById('detail-total-sessions').textContent = data.totalSessions;
    document.getElementById('detail-completed-sessions').textContent = data.completedSessions;
    document.getElementById('detail-remaining-sessions').textContent = data.totalSessions - data.completedSessions;

    // Calculate progress percentage
    const progress = Math.round((data.completedSessions / data.totalSessions) * 100);
    document.getElementById('detail-progress').textContent = progress;

    // Show class detail section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('class-detail').classList.add('active');
}

// Go back to overview
function goBack() {
    // Update the cards with latest data before showing overview
    updateClassCards();

    document.getElementById('class-detail').classList.remove('active');
    document.getElementById('overview').classList.add('active');

    // Update nav link active state
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector('.nav-link[href="#overview"]').classList.add('active');
}

// Update class cards with latest data
function updateClassCards() {
    Object.keys(classData).forEach(classId => {
        const data = classData[classId];
        const classCard = document.querySelector(`[onclick="showClassDetail('${classId}')"]`);

        if (classCard) {
            // Update progress info
            const progressElement = classCard.querySelector('p:nth-of-type(4)');
            if (progressElement) {
                progressElement.innerHTML = `<i class="fas fa-chart-line"></i> Tiến độ: ${data.completedSessions}/${data.totalSessions} buổi`;
            }
        }
    });
}

// Start attendance
function startAttendance() {
    if (!currentClass) return;

    const data = classData[currentClass];
    const today = new Date();
    const dateStr = today.toLocaleDateString('vi-VN');
    const sessionNumber = data.completedSessions + 1;

    // Update attendance info
    document.getElementById('attendance-class-name').textContent = data.name;
    document.getElementById('attendance-date').textContent = dateStr;
    document.getElementById('attendance-session').textContent = `Buổi ${sessionNumber}`;

    // Populate students list
    const studentsList = document.getElementById('students-list');
    studentsList.innerHTML = '';
    attendanceData = {};

    data.students.forEach(student => {
        const studentItem = document.createElement('div');
        studentItem.className = 'student-item';
        studentItem.innerHTML = `
                    <div class="student-info">
                        <div class="student-avatar">${student.name.charAt(0)}</div>
                        <div class="student-details">
                            <h5>${student.name}</h5>
                            <p>${student.email}</p>
                        </div>
                    </div>
                    <div class="attendance-controls">
                        <button class="attendance-btn present-btn" onclick="markAttendance(${student.id}, 'present')">
                            <i class="fas fa-check"></i> Có mặt
                        </button>
                        <button class="attendance-btn absent-btn" onclick="markAttendance(${student.id}, 'absent')">
                            <i class="fas fa-times"></i> Vắng mặt
                        </button>
                    </div>
                `;
        studentsList.appendChild(studentItem);
    });

    // Show attendance section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('attendance').classList.add('active');
}

// Mark attendance for a student
function markAttendance(studentId, status) {
    attendanceData[studentId] = status;

    // Update button states
    const studentItem = document.querySelector(`button[onclick="markAttendance(${studentId}, 'present')"]`).closest('.student-item');
    const presentBtn = studentItem.querySelector('.present-btn');
    const absentBtn = studentItem.querySelector('.absent-btn');

    // Remove active class from both buttons
    presentBtn.classList.remove('active');
    absentBtn.classList.remove('active');

    // Add active class to selected button
    if (status === 'present') {
        presentBtn.classList.add('active');
    } else {
        absentBtn.classList.add('active');
    }
}

// Save attendance
function completeSession() {
    if (!currentClass) return;

    const data = classData[currentClass];
    const markedStudents = Object.keys(attendanceData).length;

    if (markedStudents === 0) {
        alert('Vui lòng điểm danh ít nhất một học sinh!');
        return;
    }

    if (markedStudents < data.students.length) {
        const confirmed = confirm(`Bạn chỉ điểm danh ${markedStudents}/${data.students.length} học sinh. Bạn có muốn tiếp tục hoàn thành buổi học?`);
        if (!confirmed) return;
    }

    // Simulate saving to server
    setTimeout(() => {
        // Show success message
        const successMessage = document.getElementById('attendance-success');
        successMessage.innerHTML = '<i class="fas fa-check-circle"></i> Buổi học đã được hoàn thành thành công!';
        successMessage.style.display = 'block';

        // Save attendance to history
        const sessionNumber = data.completedSessions + 1;
        if (!data.attendanceHistory) {
            data.attendanceHistory = {};
        }
        data.attendanceHistory[sessionNumber] = { ...attendanceData };

        // Update completed sessions
        data.completedSessions += 1;

        // Scroll to top to show success message
        document.querySelector('.attendance-section').scrollIntoView({ behavior: 'smooth' });

        // Hide success message and redirect after 2 seconds
        setTimeout(() => {
            successMessage.style.display = 'none';
            // Go back to class detail and update overview
            backToClassDetail();
            updateClassCards(); // Update the overview cards
            // Update the class detail view with new data
            showClassDetail(currentClass);
        }, 2000);

        // Log attendance data (in real app, this would be sent to server)
        console.log('Session completed:', {
            class: currentClass,
            session: sessionNumber,
            date: new Date().toISOString(),
            attendance: attendanceData
        });

    }, 500);
}

// View student list
function viewStudentList() {
    if (!currentClass) return;

    const data = classData[currentClass];

    // Update stats
    document.getElementById('total-students').textContent = data.students.length;

    // Calculate average attendance
    let totalAttendance = 0;
    let totalSessions = 0;
    let goodAttendanceCount = 0;

    data.students.forEach(student => {
        let attendedSessions = 0;
        let studentTotalSessions = 0;

        for (let session = 1; session <= data.completedSessions; session++) {
            if (data.attendanceHistory && data.attendanceHistory[session] && data.attendanceHistory[session][student.id]) {
                studentTotalSessions++;
                if (data.attendanceHistory[session][student.id] === 'present') {
                    attendedSessions++;
                }
            }
        }

        if (studentTotalSessions > 0) {
            const attendanceRate = (attendedSessions / studentTotalSessions) * 100;
            totalAttendance += attendanceRate;
            totalSessions++;

            if (attendanceRate >= 80) {
                goodAttendanceCount++;
            }
        }
    });

    const avgAttendance = totalSessions > 0 ? Math.round(totalAttendance / totalSessions) : 0;
    document.getElementById('avg-attendance').textContent = avgAttendance + '%';
    document.getElementById('good-attendance').textContent = goodAttendanceCount;

    // Populate student grid
    const studentGrid = document.getElementById('student-grid');
    studentGrid.innerHTML = '';

    data.students.forEach(student => {
        let attendedSessions = 0;
        let studentTotalSessions = 0;

        for (let session = 1; session <= data.completedSessions; session++) {
            if (data.attendanceHistory && data.attendanceHistory[session] && data.attendanceHistory[session][student.id]) {
                studentTotalSessions++;
                if (data.attendanceHistory[session][student.id] === 'present') {
                    attendedSessions++;
                }
            }
        }

        const attendanceRate = studentTotalSessions > 0 ? Math.round((attendedSessions / studentTotalSessions) * 100) : 0;

        const studentCard = document.createElement('div');
        studentCard.className = 'student-card';
        studentCard.innerHTML = `
                    <div class="student-avatar">${student.name.charAt(0)}</div>
                    <div class="student-details" style="flex: 1;">
                        <h5 style="margin: 0; color: #073A4B;">${student.name}</h5>
                        <p style="margin: 0; color: #666; font-size: 0.9rem;">${student.email}</p>
                        <p style="margin: 0; color: #108AB1; font-weight: 600;">
                            Tham gia: ${attendedSessions}/${studentTotalSessions} buổi (${attendanceRate}%)
                        </p>
                    </div>
                `;
        studentGrid.appendChild(studentCard);
    });

    // Show student list section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('student-list').classList.add('active');
}

// View attendance history
function viewAttendanceHistory() {
    if (!currentClass) return;

    const data = classData[currentClass];
    const table = document.getElementById('attendance-history-table');

    // Clear existing content
    table.innerHTML = '';

    // Create header
    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    headerRow.innerHTML = '<th>Học Sinh</th>';

    for (let session = 1; session <= data.totalSessions; session++) {
        const th = document.createElement('th');
        th.textContent = `Buổi ${session}`;
        th.style.minWidth = '80px';
        headerRow.appendChild(th);
    }
    thead.appendChild(headerRow);
    table.appendChild(thead);

    // Create body
    const tbody = document.createElement('tbody');

    data.students.forEach(student => {
        const row = document.createElement('tr');
        const nameCell = document.createElement('td');
        nameCell.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: #108AB1; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                            ${student.name.charAt(0)}
                        </div>
                        ${student.name}
                    </div>
                `;
        row.appendChild(nameCell);

        for (let session = 1; session <= data.totalSessions; session++) {
            const cell = document.createElement('td');

            if (session <= data.completedSessions) {
                const status = data.attendanceHistory && data.attendanceHistory[session] && data.attendanceHistory[session][student.id];
                if (status === 'present') {
                    cell.innerHTML = '<span class="status-present">Có mặt</span>';
                } else if (status === 'absent') {
                    cell.innerHTML = '<span class="status-absent">Vắng</span>';
                } else {
                    cell.innerHTML = '<span class="status-not-started">-</span>';
                }
            } else {
                cell.innerHTML = '<span class="status-not-started">Chưa học</span>';
            }

            row.appendChild(cell);
        }

        tbody.appendChild(row);
    });

    table.appendChild(tbody);

    // Show attendance history section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('attendance-history').classList.add('active');
}
// Back to class detail from attendance/student-list/history
function backToClassDetail() {
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('class-detail').classList.add('active');
}

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

// Add modal styles
const modalStyle = document.createElement('style');
modalStyle.textContent = `
            .modal {
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(3px);
            }

            .modal-content {
                background-color: white;
                margin: 15% auto;
                padding: 0;
                border-radius: 10px;
                width: 90%;
                max-width: 400px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                animation: modalFadeIn 0.3s ease;
            }

            @keyframes modalFadeIn {
                from { opacity: 0; transform: translateY(-50px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .modal-header {
                padding: 1.5rem;
                border-bottom: 1px solid #ddd;
                background-color: #f8f9fa;
                border-radius: 10px 10px 0 0;
            }

            .modal-header h3 {
                margin: 0;
                color: #073A4B;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .modal-body {
                padding: 1.5rem;
                text-align: center;
            }

            .modal-body p {
                margin: 0;
                color: #333;
                font-size: 1.1rem;
            }

            .modal-footer {
                padding: 1rem 1.5rem;
                border-top: 1px solid #ddd;
                display: flex;
                gap: 1rem;
                justify-content: flex-end;
                background-color: #f8f9fa;
                border-radius: 0 0 10px 10px;
            }

            .cancel-btn {
                background: white;
                color: #6c757d;
                border: 2px solid #6c757d;
                padding: 0.75rem 1.5rem;
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
                transition: all 0.3s;
                font-weight: 500;
            }

            .cancel-btn:hover {
                background: #6c757d;
                color: white;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
            }

            .logout-confirm-btn {
                background: #dc3545;
                color: white;
                border: none;
                padding: 0.75rem 1.5rem;
                border-radius: 6px;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 1rem;
                transition: background-color 0.3s;
            }

            .logout-confirm-btn:hover {
                background: #c82333;
            }
        `;
document.head.appendChild(modalStyle);
