// Prevent multiple execution of this script
if (typeof window.studentScriptLoaded === 'undefined') {
    window.studentScriptLoaded = true;

    // Show class detail with real data
    function showClassDetail(classId) {
        console.log('Showing class detail for ID:', classId);
        console.log('Available student data:', window.studentData);

        // Find the class data from studentData passed from PHP
        const classData = window.studentData.courses.find(course => course.id == classId);
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
        const classAttendance = window.studentData.attendance.filter(att => att.class_id == classId);
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

    // Calendar variables
    let currentDate = new Date();
    let selectedDate = null;
    let scheduleData = {};

    // Initialize calendar when schedule section is shown
    function showSchedule() {
        document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById('schedule').classList.add('active');

        // Update navigation
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelector('.nav-link[href="#schedule"]').classList.add('active');

        // Load schedule data and render calendar
        loadScheduleData();
    }

    // Load schedule data for student
    function loadScheduleData() {
        if (!window.studentData || !window.studentData.courses) {
            console.log('No student data available');
            renderCalendar();
            return;
        }

        // Generate schedule data from student's courses
        scheduleData = {};

        console.log('=== PROCESSING STUDENT SCHEDULE DATA ===');

        window.studentData.courses.forEach((courseInfo, index) => {
            console.log(`\nProcessing course ${index + 1}:`, courseInfo.class_name);
            console.log('Schedule days:', courseInfo.schedule_days);
            console.log('Schedule time:', courseInfo.schedule_time);
            console.log('Start date:', courseInfo.start_date);
            console.log('End date:', courseInfo.end_date);

            if (!courseInfo.schedule_days || !courseInfo.schedule_time) {
                console.log('Skipping course - missing schedule info');
                return;
            }

            const startDate = new Date(courseInfo.start_date);
            const endDate = new Date(courseInfo.end_date);
            const scheduleDays = parseScheduleDays(courseInfo.schedule_days);

            console.log('Parsed schedule days for', courseInfo.class_name, ':', scheduleDays);

            // Generate all class dates
            let currentClassDate = new Date(startDate);
            let datesAdded = 0;

            while (currentClassDate <= endDate && datesAdded < 50) { // Limit for performance
                const dayOfWeek = currentClassDate.getDay();
                console.log(`Checking date ${currentClassDate.toDateString()}, day of week: ${dayOfWeek}`);

                if (scheduleDays.includes(dayOfWeek)) {
                    const dateKey = formatDateKey(currentClassDate);
                    console.log(`✓ Adding class on ${dateKey} (day ${dayOfWeek})`);

                    if (!scheduleData[dateKey]) {
                        scheduleData[dateKey] = [];
                    }

                    scheduleData[dateKey].push({
                        classId: courseInfo.id,
                        className: courseInfo.class_name,
                        subject: courseInfo.subject,
                        time: courseInfo.schedule_time,
                        duration: courseInfo.schedule_duration,
                        instructor: courseInfo.instructor_name,
                        classLevel: courseInfo.class_level
                    });

                    datesAdded++;
                }

                currentClassDate.setDate(currentClassDate.getDate() + 1);
            }

            console.log(`Added ${datesAdded} dates for course ${courseInfo.class_name}`);
        });

        console.log('Final student schedule data:', scheduleData);
        console.log('=== END PROCESSING STUDENT SCHEDULE DATA ===');

        renderCalendar();
    }

    // Parse schedule days (e.g., "T2,T4,T6" -> [1,3,5])
    function parseScheduleDays(scheduleDaysStr) {
        const daysMap = {
            'CN': 0,  // Chủ nhật = 0
            'T2': 1,  // Thứ hai = 1
            'T3': 2,  // Thứ ba = 2
            'T4': 3,  // Thứ tư = 3
            'T5': 4,  // Thứ năm = 4
            'T6': 5,  // Thứ sáu = 5
            'T7': 6   // Thứ bảy = 6
        };

        console.log('Original schedule days string:', scheduleDaysStr);

        if (!scheduleDaysStr) {
            return [];
        }

        const result = scheduleDaysStr.split(',').map(day => {
            const trimmedDay = day.trim();
            const dayNumber = daysMap[trimmedDay];
            console.log(`Mapping ${trimmedDay} to ${dayNumber}`);
            return dayNumber;
        }).filter(day => day !== undefined);

        console.log('Parsed schedule days:', result);
        return result;
    }

    // Format date as key (YYYY-MM-DD)
    function formatDateKey(date) {
        return date.toISOString().split('T')[0];
    }

    // Render calendar
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        // Update month/year display
        const monthNames = [
            'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
            'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
        ];
        document.getElementById('current-month-year').textContent = `${monthNames[month]} ${year}`;

        // Get first day of month and number of days
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const firstDayOfWeek = firstDay.getDay();
        const daysInMonth = lastDay.getDate();

        // Get previous month's last days
        const prevMonth = new Date(year, month, 0);
        const daysInPrevMonth = prevMonth.getDate();

        const calendarGrid = document.getElementById('calendar-grid');
        calendarGrid.innerHTML = '';

        // Add previous month's trailing days
        for (let i = firstDayOfWeek - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const date = new Date(year, month - 1, day);
            calendarGrid.appendChild(createCalendarDay(date, true));
        }

        // Add current month's days
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            calendarGrid.appendChild(createCalendarDay(date, false));
        }

        // Add next month's leading days
        const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
        const remainingCells = totalCells - (firstDayOfWeek + daysInMonth);

        for (let day = 1; day <= remainingCells; day++) {
            const date = new Date(year, month + 1, day);
            calendarGrid.appendChild(createCalendarDay(date, true));
        }
    }

    // Create calendar day element
    function createCalendarDay(date, isOtherMonth) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';

        if (isOtherMonth) {
            dayElement.classList.add('other-month');
        }

        // Check if it's today
        const today = new Date();
        if (date.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }

        // Check if it's selected date
        if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
            dayElement.classList.add('selected');
        }

        // Check if this date has schedule
        const dateKey = formatDateKey(date);
        const hasSchedule = scheduleData[dateKey] && scheduleData[dateKey].length > 0;

        if (hasSchedule) {
            dayElement.classList.add('has-schedule');
        }

        // Create day number
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = date.getDate();
        dayElement.appendChild(dayNumber);

        // Add schedule indicator
        if (hasSchedule) {
            const indicator = document.createElement('div');
            indicator.className = 'schedule-indicator';
            dayElement.appendChild(indicator);

            // Add mini schedule info for current month days
            if (!isOtherMonth) {
                const miniInfo = document.createElement('div');
                miniInfo.className = 'mini-class-info';

                const schedules = scheduleData[dateKey];
                if (schedules.length === 1) {
                    const schedule = schedules[0];
                    miniInfo.innerHTML = `
                        <div class="mini-class-time">${schedule.time.substring(0, 5)}</div>
                        <div>${schedule.className}</div>
                    `;
                } else {
                    miniInfo.innerHTML = `<div class="mini-class-time">${schedules.length} lớp</div>`;
                }

                dayElement.appendChild(miniInfo);
            }
        }

        // Add click event
        dayElement.addEventListener('click', () => selectDate(date));

        return dayElement;
    }

    // Select a date and show schedule details
    function selectDate(date) {
        selectedDate = date;
        renderCalendar(); // Re-render to show selection

        const dateKey = formatDateKey(date);
        const schedules = scheduleData[dateKey] || [];

        showScheduleDetails(date, schedules);
    }

    // Show schedule details for selected date
    function showScheduleDetails(date, schedules) {
        const detailsPanel = document.getElementById('schedule-details');
        const titleElement = document.getElementById('selected-date-title');
        const contentElement = document.getElementById('schedule-details-content');

        // Format date for display
        const dateStr = date.toLocaleDateString('vi-VN', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        titleElement.textContent = `Lịch học ${dateStr}`;

        if (schedules.length === 0) {
            contentElement.innerHTML = `
                <div class="no-schedule">
                    <i class="fas fa-calendar-times"></i>
                    <h4>Không có lịch học</h4>
                    <p>Bạn không có lịch học nào trong ngày này.</p>
                </div>
            `;
        } else {
            let scheduleHtml = '';

            schedules.forEach(schedule => {
                scheduleHtml += `
                    <div class="schedule-item">
                        <div class="schedule-item-header">
                            <h4 class="class-name-schedule">${schedule.className} - ${schedule.subject}</h4>
                            <div class="schedule-time">
                                <i class="fas fa-clock"></i> ${schedule.time.substring(0, 5)}
                            </div>
                        </div>
                        
                        <div class="schedule-item-details">
                            <div class="schedule-detail">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <span>Giảng viên: ${schedule.instructor || 'Chưa phân công'}</span>
                            </div>
                            <div class="schedule-detail">
                                <i class="fas fa-layer-group"></i>
                                <span>Cấp độ: ${schedule.classLevel || 'N/A'}</span>
                            </div>
                            <div class="schedule-detail">
                                <i class="fas fa-hourglass-half"></i>
                                <span>Thời lượng: ${schedule.duration || 120} phút</span>
                            </div>
                        </div>
                        
                        <div class="schedule-actions">
                            <button class="btn-primary btn-sm" onclick="showClassDetail('${schedule.classId}')">
                                <i class="fas fa-eye"></i> Xem chi tiết lớp
                            </button>
                            <button class="btn-info btn-sm" onclick="prepareForClass('${schedule.classId}')">
                                <i class="fas fa-book-open"></i> Chuẩn bị học
                            </button>
                        </div>
                    </div>
                `;
            });

            contentElement.innerHTML = scheduleHtml;
        }

        detailsPanel.style.display = 'block';
        detailsPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Close schedule details
    function closeScheduleDetails() {
        document.getElementById('schedule-details').style.display = 'none';
        selectedDate = null;
        renderCalendar();
    }

    // Navigate to previous month
    function previousMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
        closeScheduleDetails();
    }

    // Navigate to next month
    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
        closeScheduleDetails();
    }

    // Go to today
    function goToToday() {
        currentDate = new Date();
        renderCalendar();
        closeScheduleDetails();
    }

    // Prepare for class function
    function prepareForClass(classId) {
        // You can implement this to show study materials, homework, etc.
        showMessage('Tính năng chuẩn bị học đang được phát triển!', 'info');
    }

    // Add event listener for schedule navigation
    document.addEventListener('DOMContentLoaded', function () {
        // Add click handler for schedule nav link
        const scheduleNavLink = document.querySelector('[href="#schedule"]');
        if (scheduleNavLink) {
            scheduleNavLink.addEventListener('click', function (e) {
                e.preventDefault();
                showSchedule();
            });
        }
    });

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
        const fullname = document.getElementById('fullname').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();

        // Basic validation
        if (!fullname || !email) {
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
        if (phone && !/^[0-9\s\-\+\(\)]{10,15}$/.test(phone)) {
            showMessage('Số điện thoại không hợp lệ!', 'error');
            return;
        }

        // Show loading
        const saveBtn = document.querySelector('.save-btn');
        if (!saveBtn) {
            console.error('Save button not found');
            return;
        }

        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
        saveBtn.disabled = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('full_name', fullname);
        formData.append('email', email);
        formData.append('phone', phone);

        console.log('Sending data:', {
            full_name: fullname,
            email: email,
            phone: phone
        });

        // Call API - FIXED: Use correct endpoint for student
        fetch('/webapp/api/student/update-profile', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log('Response status:', response.status);

                // Check if response is OK
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response error text:', text);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }

                // Try to parse JSON
                return response.json();
            })
            .then(data => {
                console.log('API response:', data);

                if (data && data.success === true) {
                    showMessage(data.message || 'Cập nhật thông tin thành công!', 'success');

                    // Update header info if needed
                    const headerName = document.querySelector('.user-info span');
                    if (headerName) {
                        const firstName = fullname.split(' ').pop();
                        headerName.textContent = `Chào mừng, ${firstName}`;
                    }
                } else {
                    const errorMessage = data && data.message ? data.message : 'Cập nhật thất bại';
                    showMessage(errorMessage, 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showMessage('Lỗi kết nối hoặc server. Vui lòng thử lại!', 'error');
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
        if (!changeBtn) {
            console.error('Change password button not found');
            return;
        }

        const originalText = changeBtn.innerHTML;
        changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thay đổi...';
        changeBtn.disabled = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        formData.append('confirm_password', confirmPassword);

        // Call API - FIXED: Use correct endpoint for student
        fetch('/webapp/api/student/change-password', {
            method: 'POST',
            body: formData
        })
            .then(response => {
                console.log('Response status:', response.status);

                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response error text:', text);
                        throw new Error(`HTTP error! status: ${response.status}`);
                    });
                }

                return response.json();
            })
            .then(data => {
                console.log('API response:', data);

                if (data && data.success === true) {
                    showMessage(data.message || 'Đổi mật khẩu thành công!', 'success');
                    hideChangePassword();
                } else {
                    const errorMessage = data && data.message ? data.message : 'Đổi mật khẩu thất bại';
                    showMessage(errorMessage, 'error');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showMessage('Lỗi kết nối hoặc server. Vui lòng thử lại!', 'error');
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

    // Add CSS for status colors (only if not already added)
    if (!document.getElementById('student-status-styles')) {
        const statusStyles = document.createElement('style');
        statusStyles.id = 'student-status-styles';
        statusStyles.textContent = `
        .text-success {
            color: #28a745 !important;
            font-weight: 600;
        }
        .text-danger {
            color: #dc3545 !important;
            font-weight: 600;
        }
    `;
        document.head.appendChild(statusStyles);
    }

    // Initialize event listeners only once
    function initializeEventListeners() {
        // Handle invite form submission
        const form = document.getElementById('parent-invite-form');
        if (form && !form.dataset.listenerAdded) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const email = this.parent_email.value;
                if (!email) {
                    showMessage('Vui lòng nhập email phụ huynh', 'error');
                    return;
                }

                sendConnectionRequest();
            });
            form.dataset.listenerAdded = 'true';
        }

        // Close modal on outside click
        if (!window.studentModalClickListenerAdded) {
            window.addEventListener('click', function (event) {
                const modal = document.getElementById('add-parent-modal');
                if (event.target === modal) {
                    closeAddParentModal();
                }
            });
            window.studentModalClickListenerAdded = true;
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeEventListeners);
    } else {
        initializeEventListeners();
    }

} // End of script loaded check