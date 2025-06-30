// Handle window resize to reset mobile layout
// sử lí kích cỡ cửa sổ để reset layout điện thoại 
window.addEventListener('resize', function() {
    const navbar = document.getElementById('navbar');
    const mainContent = document.querySelector('.main-content');
    const toggleButton = document.getElementById('navbarToggle');
    
    if (window.innerWidth <= 768) {
        // Mobile layout - reset any desktop toggle states
        //layout điện thoại - reset bất kì trạng thái chuyển đổi nào của desktop
        navbar.classList.remove('collapsed');
        mainContent.style.marginLeft = '';
        toggleButton.style.left = '';
    } else {
        // Desktop layout - restore proper margins based on navbar state
        // layout desktop - trả lại về lề chuẩn cho trạng thái navbar
        if (navbar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '95px';
            toggleButton.style.left = '60px';
        } else {
            mainContent.style.marginLeft = '285px';
            toggleButton.style.left = '250px';
        }
    }
});

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

    // Show loading
    const saveBtn = document.querySelector('#personal-info-form .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    saveBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.append('full_name', fullname);
    formData.append('email', email);
    formData.append('phone', phone);

    // Call API
    fetch('/webapp/api/admin/update-profile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            
            // Update header info if needed
            const headerName = document.querySelector('.admin-info span');
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
    const changeBtn = document.querySelector('#change-password-form .btn-primary');
    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thay đổi...';
    changeBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    // Call API
    fetch('/webapp/api/admin/change-password', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            document.getElementById('change-password-form').reset();
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

// Chart variables
let studentRegistrationChart = null;
let classDistributionChart = null;

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    if (adminData && adminData.student_registration_trend) {
        initializeCharts();
    }
    
    // Load courses if we're on the manage courses section or load for all pages
    loadCourses();
    
    // Load tutors for the form
    loadTutors();
    
    // Add event listener for navigation to courses section
    const coursesNavLink = document.querySelector('[href="#manage_courses"]');
    if (coursesNavLink) {
        coursesNavLink.addEventListener('click', function() {
            setTimeout(() => {
                loadCourses();
            }, 100);
        });
    }
});

// Initialize all charts
function initializeCharts() {
    initStudentRegistrationChart();
    initClassDistributionChart();
}

// Student Registration Trend Chart (Bar Chart)
function initStudentRegistrationChart() {
    const ctx = document.getElementById('studentRegistrationChart');
    if (!ctx) return;
    
    const data = adminData.student_registration_trend || [];
    const labels = data.map(item => item.month_name);
    const values = data.map(item => item.student_count);
    
    // Destroy existing chart if exists
    if (studentRegistrationChart) {
        studentRegistrationChart.destroy();
    }
    
    studentRegistrationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Số học viên đăng ký',
                data: values,
                backgroundColor: 'rgba(16, 138, 177, 0.7)',
                borderColor: 'rgba(16, 138, 177, 1)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(7, 58, 75, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#108AB1',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return `${context.parsed.y} học viên`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#666'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#666',
                        maxRotation: 45
                    },
                    grid: {
                        display: false
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Class Level Distribution Chart (Pie Chart)
function initClassDistributionChart() {
    const ctx = document.getElementById('classDistributionChart');
    if (!ctx) return;
    
    const data = adminData.class_level_distribution || [];
    const labels = data.map(item => item.class_level || 'Không xác định');
    const values = data.map(item => item.class_count);
    const percentages = data.map(item => item.percentage);
    
    // Generate colors
    const colors = [
        'rgba(16, 138, 177, 0.8)',
        'rgba(7, 58, 75, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(40, 167, 69, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(108, 117, 125, 0.8)'
    ];
    
    // Destroy existing chart if exists
    if (classDistributionChart) {
        classDistributionChart.destroy();
    }
    
    classDistributionChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: '#fff',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // We'll create custom legend
                },
                tooltip: {
                    backgroundColor: 'rgba(7, 58, 75, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#108AB1',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const percentage = percentages[context.dataIndex];
                            return `${label}: ${value} lớp (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
    
    // Create custom legend
    createClassDistributionLegend(data, colors);
}

// Create custom legend for pie chart
function createClassDistributionLegend(data, colors) {
    const legendContainer = document.getElementById('class-distribution-legend');
    if (!legendContainer) return;
    
    let legendHtml = '';
    data.forEach((item, index) => {
        legendHtml += `
            <div class="legend-item">
                <div class="legend-color" style="background-color: ${colors[index]}"></div>
                <span class="legend-label">${item.class_level || 'Không xác định'}</span>
                <span class="legend-value">${item.class_count} lớp (${item.percentage}%)</span>
            </div>
        `;
    });
    
    legendContainer.innerHTML = legendHtml;
}

// Update registration chart when year changes
function updateRegistrationChart() {
    const yearSelect = document.getElementById('registration-year-select');
    const selectedYear = yearSelect.value;
    
    // Show loading
    const chartContainer = document.querySelector('#studentRegistrationChart').parentElement;
    chartContainer.innerHTML = '<div class="chart-loading"></div>';
    
    // Fetch new data
    fetch(`/webapp/api/admin/chart-data?type=student_registration&year=${selectedYear}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Restore chart canvas
                chartContainer.innerHTML = '<canvas id="studentRegistrationChart"></canvas>';
                
                // Update adminData
                adminData.student_registration_trend = data.data;
                
                // Re-initialize chart
                initStudentRegistrationChart();
            } else {
                showMessage('Lỗi khi tải dữ liệu biểu đồ', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối khi tải biểu đồ', 'error');
        });
}

// Refresh class distribution chart
function refreshClassDistribution() {
    const refreshBtn = document.querySelector('.refresh-btn');
    refreshBtn.style.transform = 'rotate(180deg)';
    
    fetch('/webapp/api/admin/chart-data?type=class_distribution')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                adminData.class_level_distribution = data.data;
                initClassDistributionChart();
                showMessage('Đã cập nhật biểu đồ phân bố lớp học', 'success');
            } else {
                showMessage('Lỗi khi tải dữ liệu biểu đồ', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi kết nối khi tải biểu đồ', 'error');
        })
        .finally(() => {
            setTimeout(() => {
                refreshBtn.style.transform = '';
            }, 300);
        });
}

// Handle window resize for charts
window.addEventListener('resize', function() {
    if (studentRegistrationChart) {
        studentRegistrationChart.resize();
    }
    if (classDistributionChart) {
        classDistributionChart.resize();
    }
});

// Logout modal functions
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

// Course Management Variables
let allCourses = [];
let filteredCourses = [];
let tutors = [];

// Initialize course management when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load courses if we're on the manage courses section
    if (window.location.hash === '#manage_courses') {
        loadCourses();
    }
    
    // Load tutors for the form
    loadTutors();
});

// Course Management Functions
function showCreateCourseModal() {
    document.getElementById('create-course-modal').style.display = 'block';
    loadTutorsForForm();
}

function closeCreateCourseModal() {
    document.getElementById('create-course-modal').style.display = 'none';
    document.getElementById('create-course-form').reset();
}

function loadTutorsForForm() {
    const tutorSelect = document.getElementById('tutor-id');
    tutorSelect.innerHTML = '<option value="">Chọn sau</option>';
    
    tutors.forEach(tutor => {
        const option = document.createElement('option');
        option.value = tutor.id;
        option.textContent = tutor.full_name;
        tutorSelect.appendChild(option);
    });
}

function createCourse(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    // Get selected schedule days
    const scheduleDays = [];
    document.querySelectorAll('input[name="schedule_days"]:checked').forEach(checkbox => {
        scheduleDays.push(checkbox.value);
    });
    
    if (scheduleDays.length === 0) {
        showMessage('Vui lòng chọn ít nhất một ngày học trong tuần', 'error');
        return;
    }
    
    formData.set('schedule_days', scheduleDays.join(','));
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    submitBtn.disabled = true;
    
    fetch('/webapp/api/admin/create-course', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Tạo khóa học thành công!', 'success');
            closeCreateCourseModal();
            loadCourses(); // Reload courses
        } else {
            showMessage(data.message || 'Lỗi khi tạo khóa học', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function loadCourses() {
    console.log("=== loadCourses DEBUG START ===");
    const coursesGrid = document.getElementById('courses-grid');
    
    if (!coursesGrid) {
        console.error("courses-grid element not found");
        return;
    }
    
    coursesGrid.innerHTML = `
        <div class="loading-state" style="grid-column: 1 / -1;">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải danh sách khóa học...</p>
        </div>
    `;
    
    console.log("Making fetch request to /webapp/api/admin/get-courses");
    
    fetch('/webapp/api/admin/get-courses', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log("Response received:");
        console.log("- Status:", response.status);
        console.log("- Headers:", Object.fromEntries(response.headers.entries()));
        console.log("- OK:", response.ok);
        
        return response.text().then(text => {
            console.log("Raw response text:", text);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${text}`);
            }
            
            try {
                const data = JSON.parse(text);
                console.log("Parsed JSON data:", data);
                return data;
            } catch (e) {
                console.error("JSON parse error:", e);
                console.error("Response was:", text);
                throw new Error("Invalid JSON response: " + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        console.log("Processing API response:", data);
        
        if (data.success) {
            console.log("API success - courses array:", data.courses);
            console.log("Courses count:", data.courses ? data.courses.length : 'null');
            
            if (data.courses && Array.isArray(data.courses)) {
                allCourses = data.courses;
                filteredCourses = allCourses;
                console.log("Setting allCourses:", allCourses.length, "courses");
                displayCourses(allCourses);
                updateCourseStats(allCourses);
            } else {
                console.error("Courses is not an array:", typeof data.courses);
                showErrorCourses("Dữ liệu khóa học không hợp lệ");
            }
        } else {
            console.error("API returned error:", data.message);
            showErrorCourses(data.message || "API trả về lỗi");
        }
    })
    .catch(error => {
        console.error('Fetch error details:', error);
        console.error('Error stack:', error.stack);
        showErrorCourses("Lỗi kết nối: " + error.message);
    })
    .finally(() => {
        console.log("=== loadCourses DEBUG END ===");
    });
}

function loadTutors() {
    console.log("loadTutors called");
    
    fetch('/webapp/api/admin/get-tutors')
    .then(response => {
        console.log("Tutors response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Tutors data:", data);
        if (data.success) {
            tutors = data.tutors;
            console.log("Tutors loaded:", tutors.length);
        } else {
            console.error("Failed to load tutors:", data.message);
        }
    })
    .catch(error => {
        console.error('Error loading tutors:', error);
    });
}

function displayCourses(courses) {
    const coursesGrid = document.getElementById('courses-grid');
    
    if (courses.length === 0) {
        coursesGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-graduation-cap"></i>
                <h3>Chưa có khóa học nào</h3>
                <p>Nhấn "Tạo khóa học mới" để bắt đầu tạo khóa học đầu tiên</p>
            </div>
        `;
        return;
    }
    
    let coursesHtml = '';
    courses.forEach(course => {
        const courseCode = generateCourseCode(course);
        const tutorName = course.tutor_name || 'Chưa phân công';
        const statusClass = course.status.toLowerCase();
        const statusText = getStatusText(course.status);
        const levelClass = course.class_level.toLowerCase().replace(' ', '-');
        
        coursesHtml += `
            <div class="course-card" onclick="showCourseDetail(${course.id})">
                <div class="course-header">
                    <div>
                        <h3 class="course-title">${courseCode}</h3>
                        <span class="course-level ${levelClass}">${course.class_level}</span>
                    </div>
                    <span class="course-status ${statusClass}">${statusText}</span>
                </div>
                
                <div class="course-info">
                    <div class="course-info-item">
                        <span class="course-info-label">Giảng viên:</span>
                        <span class="course-info-value">${tutorName}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label">Học viên:</span>
                        <span class="course-info-value">${course.current_students || 0}/${course.max_students}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label">Thời gian:</span>
                        <span class="course-info-value">${formatSchedule(course)}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label">Giá:</span>
                        <span class="course-info-value">${formatCurrency(course.price_per_session)}/buổi</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label">Tiến độ:</span>
                        <span class="course-info-value">${course.sessions_completed || 0}/${course.sessions_total} buổi</span>
                    </div>
                </div>
                
                ${course.description ? `<div class="course-description">${course.description}</div>` : ''}
                
                <div class="course-actions" onclick="event.stopPropagation()">
                    <button class="btn btn-primary btn-sm" onclick="editCourse(${course.id})">
                        <i class="fas fa-edit"></i> Sửa
                    </button>
                    ${course.status === 'active' ? `
                        <button class="btn btn-warning btn-sm" onclick="closeCourse(${course.id})">
                            <i class="fas fa-lock"></i> Đóng
                        </button>
                    ` : ''}
                    <button class="btn btn-info btn-sm" onclick="showCourseDetail(${course.id})">
                        <i class="fas fa-eye"></i> Xem
                    </button>
                </div>
            </div>
        `;
    });
    
    coursesGrid.innerHTML = coursesHtml;
}

function generateCourseCode(course) {
    // Generate course code: Subject Level Year.ClassName
    // Example: IELTS Reading Sơ cấp 2025.IR
    return `${course.subject} ${course.class_level} ${course.class_year}.${course.class_name}`;
}

function getStatusText(status) {
    const statusMap = {
        'active': 'Hoạt động',
        'completed': 'Hoàn thành',
        'closed': 'Đã đóng'
    };
    return statusMap[status] || status;
}

function formatSchedule(course) {
    const time = course.schedule_time ? course.schedule_time.substring(0, 5) : '';
    const days = course.schedule_days || '';
    return time && days ? `${time} - ${days}` : 'Chưa xác định';
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function updateCourseStats(courses) {
    const totalCourses = courses.length;
    const activeCourses = courses.filter(c => c.status === 'active').length;
    const completedCourses = courses.filter(c => c.status === 'completed').length;
    const closedCourses = courses.filter(c => c.status === 'closed').length;
    
    document.getElementById('total-courses').textContent = totalCourses;
    document.getElementById('active-courses').textContent = activeCourses;
    document.getElementById('completed-courses').textContent = completedCourses;
    document.getElementById('closed-courses').textContent = closedCourses;
}

function filterCoursesByYear() {
    const selectedYear = document.getElementById('year-filter').value;
    const searchTerm = document.getElementById('course-search').value.toLowerCase();
    
    let filtered = allCourses;
    
    if (selectedYear) {
        filtered = filtered.filter(course => course.class_year == selectedYear);
    }
    
    if (searchTerm) {
        filtered = filtered.filter(course => 
            generateCourseCode(course).toLowerCase().includes(searchTerm) ||
            course.subject.toLowerCase().includes(searchTerm) ||
            (course.tutor_name && course.tutor_name.toLowerCase().includes(searchTerm))
        );
    }
    
    filteredCourses = filtered;
    displayCourses(filtered);
    updateCourseStats(filtered);
}

function searchCourses() {
    filterCoursesByYear(); // Use the same filtering logic
}

function clearCourseFilters() {
    document.getElementById('year-filter').value = '';
    document.getElementById('course-search').value = '';
    filteredCourses = allCourses;
    displayCourses(allCourses);
    updateCourseStats(allCourses);
}

function showCourseDetail(courseId) {
    const course = allCourses.find(c => c.id === courseId);
    if (!course) return;
    
    const modal = document.getElementById('course-detail-modal');
    const title = document.getElementById('course-detail-title');
    const content = document.getElementById('course-detail-content');
    
    title.textContent = generateCourseCode(course);
    
    content.innerHTML = `
        <div class="detail-section">
            <h4>Thông tin cơ bản</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Mã khóa học</div>
                    <div class="detail-value">${generateCourseCode(course)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Trạng thái</div>
                    <div class="detail-value">
                        <span class="course-status ${course.status}">${getStatusText(course.status)}</span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Cấp độ</div>
                    <div class="detail-value">${course.class_level}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Môn học</div>
                    <div class="detail-value">${course.subject}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Giảng viên</div>
                    <div class="detail-value">${course.tutor_name || 'Chưa phân công'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Số học viên</div>
                    <div class="detail-value">${course.current_students || 0}/${course.max_students}</div>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Lịch học & Thời gian</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Thời gian học</div>
                    <div class="detail-value">${course.schedule_time ? course.schedule_time.substring(0, 5) : 'Chưa xác định'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Thời lượng</div>
                    <div class="detail-value">${course.schedule_duration} phút</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Ngày học</div>
                    <div class="detail-value">${course.schedule_days || 'Chưa xác định'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Ngày khai giảng</div>
                    <div class="detail-value">${formatDate(course.start_date)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Ngày kết thúc</div>
                    <div class="detail-value">${formatDate(course.end_date)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tiến độ</div>
                    <div class="detail-value">${course.sessions_completed || 0}/${course.sessions_total} buổi</div>
                </div>
            </div>
        </div>
        
        <div class="detail-section">
            <h4>Thông tin tài chính</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Giá mỗi buổi</div>
                    <div class="detail-value">${formatCurrency(course.price_per_session)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tổng doanh thu dự kiến</div>
                    <div class="detail-value">${formatCurrency(course.price_per_session * course.sessions_total * (course.current_students || 0))}</div>
                </div>
            </div>
        </div>
        
        ${course.description ? `
            <div class="detail-section">
                <h4>Mô tả</h4>
                <div class="detail-item">
                    <div class="detail-value" style="white-space: pre-wrap;">${course.description}</div>
                </div>
            </div>
        ` : ''}
        
        <div class="detail-section">
            <h4>Hành động</h4>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="editCourse(${course.id})">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </button>
                ${course.status === 'active' ? `
                    <button class="btn btn-warning" onclick="closeCourse(${course.id})">
                        <i class="fas fa-lock"></i> Đóng khóa học
                    </button>
                ` : ''}
                <button class="btn btn-info" onclick="viewCourseStudents(${course.id})">
                    <i class="fas fa-users"></i> Xem học viên
                </button>
                <button class="btn btn-success" onclick="viewCourseSchedule(${course.id})">
                    <i class="fas fa-calendar"></i> Xem lịch học
                </button>
            </div>
        </div>
    `;
    
    modal.style.display = 'block';
}

function closeCourseDetailModal() {
    document.getElementById('course-detail-modal').style.display = 'none';
}

function formatDate(dateString) {
    if (!dateString) return 'Chưa xác định';
    return new Date(dateString).toLocaleDateString('vi-VN');
}

function editCourse(courseId) {
    // TODO: Implement edit course functionality
    showMessage('Chức năng chỉnh sửa khóa học đang được phát triển', 'info');
}

function closeCourse(courseId) {
    if (!confirm('Bạn có chắc chắn muốn đóng khóa học này? Hành động này không thể hoàn tác.')) {
        return;
    }
    
    fetch('/webapp/api/admin/close-course', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ course_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Đóng khóa học thành công!', 'success');
            loadCourses(); // Reload courses
        } else {
            showMessage(data.message || 'Lỗi khi đóng khóa học', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    });
}

function viewCourseStudents(courseId) {
    // TODO: Implement view course students functionality
    showMessage('Chức năng xem học viên đang được phát triển', 'info');
}

function viewCourseSchedule(courseId) {
    // TODO: Implement view course schedule functionality  
    showMessage('Chức năng xem lịch học đang được phát triển', 'info');
}

function showErrorCourses(message = "Lỗi khi tải dữ liệu") {
    const coursesGrid = document.getElementById('courses-grid');
    coursesGrid.innerHTML = `
        <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Lỗi khi tải dữ liệu</h3>
            <p>${message}</p>
            <button class="btn btn-primary" onclick="loadCourses()">
                <i class="fas fa-refresh"></i> Thử lại
            </button>
        </div>
    `;
}

// Close modals when clicking outside
window.onclick = function(event) {
    const createModal = document.getElementById('create-course-modal');
    const detailModal = document.getElementById('course-detail-modal');
    
    if (event.target === createModal) {
        closeCreateCourseModal();
    }
    if (event.target === detailModal) {
        closeCourseDetailModal();
    }
}

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreateCourseModal();
        closeCourseDetailModal();
    }
});