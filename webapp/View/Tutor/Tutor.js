// Show class detail with real data
function showClassDetail(classId) {
    console.log('Showing class detail for ID:', classId);
    console.log('Available tutor data:', tutorData);

    // Find the class data from tutorData passed from PHP
    const classData = tutorData.classes.find(cls => cls.id == classId);
    if (!classData) {
        console.error('Class data not found for ID:', classId);
        showMessage('Không tìm thấy thông tin lớp học', 'error');
        return;
    }

    console.log('Class data found:', classData);
    currentClass = classId;

    // Update class detail information
    document.getElementById('class-detail-title').textContent = `${classData.class_name} - ${classData.subject}`;
    document.getElementById('detail-class-name').textContent = `${classData.class_name} - ${classData.subject}`;
    document.getElementById('detail-class-code').textContent = `${classData.class_name}.${classData.class_year}`;

    // Format schedule
    const scheduleTime = classData.schedule_time || '';
    const scheduleDays = classData.schedule_days || '';
    const scheduleText = scheduleTime && scheduleDays ? `${scheduleTime} - ${scheduleDays}` : 'Chưa có lịch học';
    document.getElementById('detail-schedule').textContent = scheduleText;

    document.getElementById('detail-student-count').textContent = classData.student_count || 0;
    document.getElementById('detail-total-sessions').textContent = classData.sessions_total || 0;
    document.getElementById('detail-completed-sessions').textContent = classData.sessions_completed || 0;

    const remaining = (classData.sessions_total || 0) - (classData.sessions_completed || 0);
    document.getElementById('detail-remaining-sessions').textContent = remaining;

    // Calculate progress percentage
    const total = classData.sessions_total || 0;
    const completed = classData.sessions_completed || 0;
    const progress = total > 0 ? Math.round((completed / total) * 100) : 0;
    document.getElementById('detail-progress').textContent = progress;

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

// Start attendance
function startAttendance() {
    if (!currentClass) {
        showMessage('Vui lòng chọn lớp học trước', 'error');
        return;
    }

    const classData = tutorData.classes.find(cls => cls.id == currentClass);
    if (!classData) {
        showMessage('Không tìm thấy thông tin lớp học', 'error');
        return;
    }

    // Show loading
    const studentsList = document.getElementById('students-list');
    studentsList.innerHTML = `
        <div style="text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Đang tải danh sách học sinh...</p>
        </div>
    `;

    // Show attendance section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('attendance').classList.add('active');

    // Load students from server
    loadStudentsForAttendance();
}

function loadStudentsForAttendance() {
    const formData = new FormData();
    formData.append('class_id', currentClass);

    fetch('/webapp/api/tutor/get-students', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentStudents = data.students;
                displayAttendanceForm(data);
            } else {
                showMessage(data.message || 'Lỗi khi tải danh sách học sinh', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
        });
}

function displayAttendanceForm(data) {
    const classData = tutorData.classes.find(cls => cls.id == currentClass);
    const today = new Date();
    const dateStr = today.toLocaleDateString('vi-VN');

    // Update attendance info
    document.getElementById('attendance-class-name').textContent = `${classData.class_name} - ${classData.subject}`;
    document.getElementById('attendance-date').textContent = dateStr;
    document.getElementById('attendance-session').textContent = `Buổi ${data.next_session}`;

    // Show warning if attendance already taken
    let warningHtml = '';
    if (data.attendance_taken) {
        warningHtml = `
            <div class="warning-message" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle" style="color: #856404;"></i>
                <strong>Lưu ý:</strong> Điểm danh cho hôm nay đã được thực hiện. Nếu bạn tiếp tục, dữ liệu cũ sẽ bị ghi đè.
            </div>
        `;
    }

    // Create students list HTML
    let studentsHtml = '';
    if (data.students.length > 0) {
        studentsHtml = data.students.map(student => `
            <div class="student-item" data-student-id="${student.id}">
                <div class="student-info">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="student-details">
                        <h5>${student.full_name}</h5>
                        <p>Email: ${student.email}</p>
                        <p>Đã tham gia: ${student.sessions_attended}/${student.total_sessions} buổi</p>
                    </div>
                </div>
                <div class="attendance-controls">
                    <label class="attendance-checkbox">
                        <input type="checkbox" class="attendance-input" data-student-id="${student.id}" checked>
                        <span class="checkmark"></span>
                        <span class="attendance-label">Có mặt</span>
                    </label>
                </div>
            </div>
        `).join('');
    } else {
        studentsHtml = `
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px;"></i>
                <p>Lớp này chưa có học sinh nào đăng ký.</p>
            </div>
        `;
    }

    // Topic input
    const topicHtml = `
        <div class="topic-input" style="margin-bottom: 20px;">
            <label for="session-topic"><strong>Chủ đề buổi học:</strong></label>
            <input type="text" id="session-topic" placeholder="Nhập chủ đề buổi học hôm nay..." 
                   style="width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
    `;

    // Update students list
    const studentsList = document.getElementById('students-list');
    studentsList.innerHTML = warningHtml + topicHtml + studentsHtml;

    // Initialize attendance data
    attendanceData = {};
    data.students.forEach(student => {
        attendanceData[student.id] = 'present'; // Default to present
    });

    // Add event listeners for checkboxes
    document.querySelectorAll('.attendance-input').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const studentId = this.dataset.studentId;
            const label = this.closest('.student-item').querySelector('.attendance-label');

            if (this.checked) {
                attendanceData[studentId] = 'present';
                label.textContent = 'Có mặt';
                label.style.color = '#28a745';
            } else {
                attendanceData[studentId] = 'absent';
                label.textContent = 'Vắng mặt';
                label.style.color = '#dc3545';
            }
        });
    });

    // Show complete session button if there are students
    const completeBtn = document.querySelector('.complete-session-btn');
    if (data.students.length > 0) {
        completeBtn.style.display = 'block';
        completeBtn.onclick = completeSession;
    } else {
        completeBtn.style.display = 'none';
    }
}

function completeSession() {
    // Validation
    if (Object.keys(attendanceData).length === 0) {
        showMessage('Không có dữ liệu điểm danh để lưu', 'error');
        return;
    }

    // Get topic
    const topic = document.getElementById('session-topic').value.trim();

    // Show confirmation
    const attendedCount = Object.values(attendanceData).filter(status => status === 'present').length;
    const absentCount = Object.values(attendanceData).filter(status => status === 'absent').length;

    if (!confirm(`Xác nhận hoàn thành buổi học?\n\nCó mặt: ${attendedCount} học sinh\nVắng mặt: ${absentCount} học sinh\n\nDữ liệu sẽ được lưu vào hệ thống.`)) {
        return;
    }

    // Show loading
    const completeBtn = document.querySelector('.complete-session-btn');
    const originalText = completeBtn.innerHTML;
    completeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    completeBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.append('class_id', currentClass);
    formData.append('topic', topic);

    // Add attendance data
    Object.keys(attendanceData).forEach(studentId => {
        formData.append(`attendance[${studentId}]`, attendanceData[studentId]);
    });

    // Save to server
    fetch('/webapp/api/tutor/save-attendance', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');

                // Show success message in attendance section
                const successDiv = document.getElementById('attendance-success');
                successDiv.style.display = 'block';
                successDiv.scrollIntoView({ behavior: 'smooth' });

                // Disable form after successful save
                document.querySelectorAll('.attendance-input').forEach(input => {
                    input.disabled = true;
                });
                completeBtn.style.display = 'none';

                // Optionally go back to class detail after a delay
                setTimeout(() => {
                    backToClassDetail();
                }, 3000);

            } else {
                showMessage(data.message || 'Lỗi khi lưu điểm danh', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
        })
        .finally(() => {
            // Restore button
            completeBtn.innerHTML = originalText;
            completeBtn.disabled = false;
        });
}

// View student list
function viewStudentList() {
    if (!currentClass) {
        showMessage('Vui lòng chọn lớp học trước', 'error');
        return;
    }

    const classData = tutorData.classes.find(cls => cls.id == currentClass);
    if (!classData) {
        showMessage('Không tìm thấy thông tin lớp học', 'error');
        return;
    }

    // Show loading
    const studentGrid = document.getElementById('student-grid');
    studentGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
            <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Đang tải danh sách học sinh...</p>
        </div>
    `;

    // Show student list section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('student-list').classList.add('active');

    // Load students from server
    loadStudentList();
}

function loadStudentList() {
    const formData = new FormData();
    formData.append('class_id', currentClass);

    fetch('/webapp/api/tutor/get-student-list', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayStudentList(data.students, data.attendance_stats);
            } else {
                showMessage(data.message || 'Lỗi khi tải danh sách học sinh', 'error');
                showErrorStudentList();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
            showErrorStudentList();
        });
}

function displayStudentList(students, attendanceStats) {
    // Store all students for search functionality
    allStudents = students;
    filteredStudents = students;

    // Update stats
    updateStudentStats(students, attendanceStats);

    // Clear search when loading new data
    clearSearch();

    // Render students
    renderStudentGrid(students);
}

function updateStudentStats(students, attendanceStats) {
    const totalStudents = students.length;
    const avgAttendance = attendanceStats.average_attendance_rate || 0;
    const goodAttendanceCount = students.filter(student => {
        const attendanceRate = student.total_sessions > 0 ?
            Math.round((student.sessions_attended / student.total_sessions) * 100) : 0;
        return attendanceRate >= 80;
    }).length;

    document.getElementById('total-students').textContent = totalStudents;
    document.getElementById('avg-attendance').textContent = Math.round(avgAttendance) + '%';
    document.getElementById('good-attendance').textContent = goodAttendanceCount;
}

function renderStudentGrid(students) {
    const studentGrid = document.getElementById('student-grid');

    if (students.length === 0) {
        // Check if this is a search result or genuinely no students
        const searchInput = document.getElementById('student-search');
        if (searchInput && searchInput.value.trim() !== '') {
            // This is a search with no results
            studentGrid.style.display = 'none';
            showNoSearchResults(searchInput.value.trim());
        } else {
            // No students in class
            studentGrid.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>Lớp này chưa có học sinh nào đăng ký.</p>
                </div>
            `;
            studentGrid.style.display = 'grid';
            hideNoSearchResults();
        }
        return;
    }

    studentGrid.style.display = 'grid';
    hideNoSearchResults();

    let studentsHtml = '';
    students.forEach((student, index) => {
        const attendanceRate = student.total_sessions > 0 ?
            Math.round((student.sessions_attended / student.total_sessions) * 100) : 0;

        // Determine attendance status color
        let statusClass = 'average';
        let statusIcon = 'fa-user';
        if (attendanceRate >= 90) {
            statusClass = 'excellent';
            statusIcon = 'fa-star';
        } else if (attendanceRate >= 80) {
            statusClass = 'good';
            statusIcon = 'fa-thumbs-up';
        } else if (attendanceRate < 60) {
            statusClass = 'poor';
            statusIcon = 'fa-exclamation-triangle';
        }

        // Format enrollment date
        const enrollmentDate = student.enrollment_date ?
            new Date(student.enrollment_date).toLocaleDateString('vi-VN') : 'N/A';

        // Check if student name should be highlighted
        const searchTerm = document.getElementById('student-search').value.trim().toLowerCase();
        const isHighlighted = searchTerm && student.full_name.toLowerCase().includes(searchTerm);
        const nameClass = isHighlighted ? 'student-name highlighted' : 'student-name';
        const cardClass = isHighlighted ? `student-card ${statusClass} highlighted` : `student-card ${statusClass}`;

        studentsHtml += `
            <div class="${cardClass} filtering" data-student-name="${student.full_name.toLowerCase()}">
                <div class="student-header">
                    <div class="student-number">${getOriginalIndex(student) + 1}</div>
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="attendance-status">
                        <i class="fas ${statusIcon}"></i>
                    </div>
                </div>
                <div class="student-info">
                    <h4 class="${nameClass}">${student.full_name}</h4>
                    <p class="student-email">
                        <i class="fas fa-envelope"></i>
                        ${student.email}
                    </p>
                    ${student.phone ? `
                        <p class="student-phone">
                            <i class="fas fa-phone"></i>
                            ${student.phone}
                        </p>
                    ` : ''}
                    <p class="enrollment-date">
                        <i class="fas fa-calendar-plus"></i>
                        Ngày đăng ký: ${enrollmentDate}
                    </p>
                </div>
                <div class="attendance-summary">
                    <div class="attendance-rate ${statusClass}">
                        <span class="rate-number">${attendanceRate}%</span>
                        <span class="rate-label">Tỷ lệ tham gia</span>
                    </div>
                    <div class="attendance-details">
                        <div class="detail-item">
                            <span class="detail-number">${student.sessions_attended}</span>
                            <span class="detail-label">Có mặt</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-number">${student.total_sessions - student.sessions_attended}</span>
                            <span class="detail-label">Vắng mặt</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-number">${student.total_sessions}</span>
                            <span class="detail-label">Tổng buổi</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    studentGrid.innerHTML = studentsHtml;

    // Update search results info
    updateSearchResultsInfo();
}

function getOriginalIndex(student) {
    return allStudents.findIndex(s => s.id === student.id);
}

// Filter students based on search input
function filterStudents() {
    const searchInput = document.getElementById('student-search');
    const searchTerm = searchInput.value.trim().toLowerCase();
    const clearBtn = document.getElementById('clear-search');

    // Show/hide clear button
    if (searchTerm) {
        clearBtn.style.display = 'flex';
    } else {
        clearBtn.style.display = 'none';
    }

    // Filter students
    if (searchTerm === '') {
        // Show all students
        filteredStudents = allStudents;
        hideSearchResultsInfo();
    } else {
        // Filter by name (case insensitive, partial match)
        filteredStudents = allStudents.filter(student =>
            student.full_name.toLowerCase().includes(searchTerm)
        );
        showSearchResultsInfo();
    }

    // Re-render the grid with filtered results
    renderStudentGrid(filteredStudents);

    // Add search animation
    addSearchAnimation();
}

// Clear search and show all students
function clearSearch() {
    const searchInput = document.getElementById('student-search');
    const clearBtn = document.getElementById('clear-search');

    searchInput.value = '';
    clearBtn.style.display = 'none';

    // Reset to show all students
    filteredStudents = allStudents;
    hideSearchResultsInfo();
    hideNoSearchResults();

    // Re-render with all students
    renderStudentGrid(allStudents);
}

// Show search results info
function showSearchResultsInfo() {
    const searchResultsInfo = document.getElementById('search-results-info');
    const searchResultsCount = document.getElementById('search-results-count');

    searchResultsCount.textContent = filteredStudents.length;
    searchResultsInfo.style.display = 'block';
}

// Hide search results info
function hideSearchResultsInfo() {
    const searchResultsInfo = document.getElementById('search-results-info');
    searchResultsInfo.style.display = 'none';
}

// Show no search results message
function showNoSearchResults(searchTerm) {
    const noResultsDiv = document.getElementById('no-search-results');
    const searchTermSpan = document.getElementById('search-term');

    searchTermSpan.textContent = searchTerm;
    noResultsDiv.style.display = 'block';
}

// Hide no search results message
function hideNoSearchResults() {
    const noResultsDiv = document.getElementById('no-search-results');
    noResultsDiv.style.display = 'none';
}

// Update search results info
function updateSearchResultsInfo() {
    const searchInput = document.getElementById('student-search');
    if (searchInput.value.trim() !== '') {
        showSearchResultsInfo();
    }
}

// Add search animation effects
function addSearchAnimation() {
    const studentCards = document.querySelectorAll('.student-card');
    studentCards.forEach(card => {
        // Add a subtle animation when filtering
        card.style.transition = 'all 0.3s ease';
    });
}

// Enhanced keyboard shortcuts for search
document.addEventListener('keydown', function (event) {
    // Focus search when pressing Ctrl+F or Cmd+F
    if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
        event.preventDefault();
        const searchInput = document.getElementById('student-search');
        if (searchInput && document.getElementById('student-list').classList.contains('active')) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // Clear search when pressing Escape
    if (event.key === 'Escape') {
        const searchInput = document.getElementById('student-search');
        if (searchInput && document.activeElement === searchInput) {
            clearSearch();
            searchInput.blur();
        }
    }
});

// Add event listener for Enter key in search
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('student-search');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                // Focus first result if available
                const firstStudentCard = document.querySelector('.student-card:not(.hidden)');
                if (firstStudentCard) {
                    firstStudentCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstStudentCard.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        firstStudentCard.style.transform = '';
                    }, 300);
                }
            }
        });
    }
});

// View attendance history
function viewAttendanceHistory() {
    if (!currentClass) {
        showMessage('Vui lòng chọn lớp học trước', 'error');
        return;
    }

    const classData = tutorData.classes.find(cls => cls.id == currentClass);
    if (!classData) {
        showMessage('Không tìm thấy thông tin lớp học', 'error');
        return;
    }

    // Show loading
    const table = document.getElementById('attendance-history-table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>STT</th>
                <th>Họ và tên</th>
                <th colspan="10" style="text-align: center;">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải lịch sử điểm danh...
                </th>
            </tr>
        </thead>
    `;

    // Show attendance history section
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('attendance-history').classList.add('active');

    // Load attendance history from server
    loadAttendanceHistory();
}

function loadAttendanceHistory() {
    const formData = new FormData();
    formData.append('class_id', currentClass);

    fetch('/webapp/api/tutor/get-attendance-history', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAttendanceHistory(data.history, data.scheduled_sessions);
            } else {
                showMessage(data.message || 'Lỗi khi tải lịch sử điểm danh', 'error');
                showErrorTable();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
            showErrorTable();
        });
}

function displayAttendanceHistory(historyData, scheduledSessions) {
    const table = document.getElementById('attendance-history-table');
    const students = historyData.students;
    const sessions = historyData.sessions;
    const attendance = historyData.attendance;

    if (students.length === 0) {
        table.innerHTML = `
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ và tên</th>
                    <th>Thông báo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        Lớp này chưa có học sinh nào đăng ký
                    </td>
                </tr>
            </tbody>
        `;
        return;
    }

    // Combine completed sessions with scheduled future sessions
    const allSessions = [...sessions];

    // Add future scheduled sessions that don't exist yet
    scheduledSessions.forEach(scheduled => {
        const exists = sessions.find(s => s.session_date === scheduled.session_date);
        if (!exists) {
            allSessions.push({
                id: 'future_' + scheduled.session_date,
                session_date: scheduled.session_date,
                session_number: scheduled.session_number,
                status: 'future',
                topic: 'Chưa diễn ra'
            });
        }
    });

    // Sort all sessions by date
    allSessions.sort((a, b) => new Date(a.session_date) - new Date(b.session_date));

    // Build table header
    let headerHtml = `
        <thead>
            <tr>
                <th style="position: sticky; left: 0; background: #108AB1; z-index: 10; min-width: 50px;">STT</th>
                <th style="position: sticky; left: 50px; background: #108AB1; z-index: 10; min-width: 200px;">Họ và tên</th>
    `;

    allSessions.forEach((session, index) => {
        const sessionDate = new Date(session.session_date);
        const formattedDate = sessionDate.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit'
        });
        const isFuture = session.status === 'future';
        const headerClass = isFuture ? 'future-session' : 'completed-session';

        headerHtml += `
            <th class="${headerClass}" style="min-width: 100px; text-align: center;">
                <div>Buổi ${index + 1}</div>
                <div style="font-size: 0.8em; font-weight: normal;">${formattedDate}</div>
            </th>
        `;
    });

    headerHtml += `
            </tr>
        </thead>
    `;

    // Build table body
    let bodyHtml = '<tbody>';

    students.forEach((student, studentIndex) => {
        bodyHtml += `
            <tr>
                <td style="position: sticky; left: 0; background: white; z-index: 5; text-align: center; font-weight: bold;">
                    ${studentIndex + 1}
                </td>
                <td style="position: sticky; left: 50px; background: white; z-index: 5; font-weight: 600;">
                    ${student.full_name}
                </td>
        `;

        allSessions.forEach(session => {
            const isFuture = session.status === 'future';
            const cellClass = isFuture ? 'future-cell' : 'attendance-cell';

            if (isFuture) {
                // Future session - show as grayed out
                bodyHtml += `
                    <td class="${cellClass}" style="text-align: center;">
                        <i class="fas fa-clock" style="color: #ccc;"></i>
                    </td>
                `;
            } else {
                // Completed session - show attendance status
                const studentAttendance = attendance[session.id] ? attendance[session.id][student.id] : null;
                let statusIcon = '';
                let statusClass = '';

                if (studentAttendance === 'present') {
                    statusIcon = '<i class="fas fa-check-circle" style="color: #28a745;"></i>';
                    statusClass = 'present';
                } else if (studentAttendance === 'absent') {
                    statusIcon = '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
                    statusClass = 'absent';
                } else {
                    statusIcon = '<i class="fas fa-question-circle" style="color: #6c757d;"></i>';
                    statusClass = 'unknown';
                }

                bodyHtml += `
                    <td class="${cellClass} ${statusClass}" style="text-align: center;">
                        ${statusIcon}
                    </td>
                `;
            }
        });

        bodyHtml += '</tr>';
    });

    bodyHtml += '</tbody>';

    // Set table HTML
    table.innerHTML = headerHtml + bodyHtml;

    // Add statistics
    addAttendanceStatistics(students, sessions, attendance);
}

function addAttendanceStatistics(students, sessions, attendance) {
    const completedSessions = sessions.filter(s => s.status === 'completed');

    if (completedSessions.length === 0) {
        return;
    }

    // Calculate statistics
    let totalAttendances = 0;
    let totalPossibleAttendances = students.length * completedSessions.length;

    students.forEach(student => {
        completedSessions.forEach(session => {
            if (attendance[session.id] && attendance[session.id][student.id] === 'present') {
                totalAttendances++;
            }
        });
    });

    const attendanceRate = totalPossibleAttendances > 0 ?
        Math.round((totalAttendances / totalPossibleAttendances) * 100) : 0;

    // Add statistics above the table
    const historySection = document.querySelector('#attendance-history .history-section');
    const existingStats = historySection.querySelector('.attendance-stats-summary');

    if (existingStats) {
        existingStats.remove();
    }

    const statsHtml = `
        <div class="attendance-stats-summary" style="margin-bottom: 20px;">
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; min-width: 150px;">
                    <div style="font-size: 24px; font-weight: bold; color: #108AB1;">${students.length}</div>
                    <div style="font-size: 14px; color: #666;">Tổng học sinh</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; min-width: 150px;">
                    <div style="font-size: 24px; font-weight: bold; color: #28a745;">${completedSessions.length}</div>
                    <div style="font-size: 14px; color: #666;">Buổi đã dạy</div>
                </div>
                <div class="stat-card" style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center; min-width: 150px;">
                    <div style="font-size: 24px; font-weight: bold; color: #17a2b8;">${attendanceRate}%</div>
                    <div style="font-size: 14px; color: #666;">Tỷ lệ tham gia</div>
                </div>
            </div>
        </div>
    `;

    const tableContainer = historySection.querySelector('.table-container');
    tableContainer.insertAdjacentHTML('beforebegin', statsHtml);
}

function showErrorTable() {
    const table = document.getElementById('attendance-history-table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>STT</th>
                <th>Họ và tên</th>
                <th>Lỗi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" style="text-align: center; padding: 40px; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                    Không thể tải lịch sử điểm danh. Vui lòng thử lại.
                </td>
            </tr>
        </tbody>
    `;
}

// Back to class detail from attendance/student-list/history
function backToClassDetail() {
    document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
    document.getElementById('class-detail').classList.add('active');
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
    fetch('/webapp/api/tutor/update-profile', {
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
                    headerName.textContent = `Chào mừng, ${fullname}`;
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
    fetch('/webapp/api/tutor/change-password', {
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

function showErrorStudentList() {
    const studentGrid = document.getElementById('student-grid');
    studentGrid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #dc3545;">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px;"></i>
            <p>Không thể tải danh sách học sinh. Vui lòng thử lại.</p>
            <button onclick="loadStudentList()" style="margin-top: 16px; padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-redo"></i> Thử lại
            </button>
        </div>
    `;

    // Reset search when there's an error
    clearSearch();
    allStudents = [];
    filteredStudents = [];
}
