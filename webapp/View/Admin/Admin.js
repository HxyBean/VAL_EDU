// Global variables
let studentRegistrationChart = null;
let classDistributionChart = null;
let allCourses = [];
let filteredCourses = [];
let tutors = [];
let selectedStudentId = null;
let currentParentId = null;
// Initialize everything when DOM loads
document.addEventListener('DOMContentLoaded', function () {
    console.log('Admin.js initializing...');

    // Initialize charts if data exists
    if (typeof adminData !== 'undefined' && adminData?.student_registration_trend) {
        initializeCharts();
    }

    // Load courses and tutors
    loadCourses();
    loadTutors();

    // Add navigation event listener
    const coursesNavLink = document.querySelector('[href="#manage_courses"]');
    if (coursesNavLink) {
        coursesNavLink.addEventListener('click', function () {
            setTimeout(loadCourses, 100);
        });
    }
});

// Global event listeners
document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

window.addEventListener('resize', function () {
    handleWindowResize();
    resizeCharts();
});

// Window click handler for modal backdrop
window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ===========================================
// UTILITY FUNCTIONS
// ===========================================

function showMessage(message, type = 'info') {
    document.querySelectorAll('.alert-message').forEach(msg => msg.remove());

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

    const colors = {
        'success': '#28a745',
        'error': '#dc3545',
        'warning': '#ffc107',
        'info': '#17a2b8'
    };

    messageDiv.style.backgroundColor = colors[type] || colors.info;
    messageDiv.textContent = message;

    if (!document.querySelector('style[data-alert-styles]')) {
        const style = document.createElement('style');
        style.setAttribute('data-alert-styles', 'true');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }

    document.body.appendChild(messageDiv);

    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 3000);
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        if (modal.style.display === 'block') {
            modal.style.display = 'none';
        }
    });
    document.body.style.overflow = 'auto';
}

function handleWindowResize() {
    const navbar = document.getElementById('navbar');
    const mainContent = document.querySelector('.main-content');
    const toggleButton = document.getElementById('navbarToggle');

    if (!navbar || !mainContent || !toggleButton) return;

    if (window.innerWidth <= 768) {
        navbar.classList.remove('collapsed');
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
}

function resizeCharts() {
    if (studentRegistrationChart) {
        studentRegistrationChart.resize();
    }
    if (classDistributionChart) {
        classDistributionChart.resize();
    }
}

// ===========================================
// HELPER FUNCTIONS
// ===========================================

function generateCourseCode(course) {
    if (!course) return 'N/A';

    const name = (course.class_name || 'UNK').toUpperCase();
    const year = course.class_year ? course.class_year.toString().slice(-2) : '00';
    const level = course.class_level ? course.class_level.charAt(0).toUpperCase() : 'XX';

    return `${name}-${year}${level}`;
}

function formatCurrency(amount) {
    if (!amount || isNaN(amount)) return '0đ';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(amount);
}

function formatSchedule(course) {
    if (!course) return 'Chưa xác định';

    const time = course.schedule_time ? course.schedule_time.substring(0, 5) : '';
    const days = course.schedule_days || '';
    const duration = course.schedule_duration || 0;

    if (!time && !days) return 'Chưa xác định';

    return `${days} ${time} (${duration}p)`;
}

function formatDate(dateString) {
    if (!dateString) return 'Chưa xác định';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Ngày không hợp lệ';

        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return 'Lỗi định dạng ngày';
    }
}

function formatDateShort(dateString) {
    if (!dateString) return 'Chưa xác định';

    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Ngày không hợp lệ';

        return date.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch (error) {
        return 'Lỗi định dạng ngày';
    }
}

function getStatusText(status) {
    const statusMap = {
        'active': 'Đang hoạt động',
        'completed': 'Đã hoàn thành',
        'closed': 'Đã đóng'
    };
    return statusMap[status] || 'Không xác định';
}

function getStartDateClass(startDateString) {
    if (!startDateString) return '';

    const startDate = new Date(startDateString);
    const today = new Date();
    const timeDiff = startDate.getTime() - today.getTime();
    const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

    if (daysDiff < 0) {
        return 'course-start-passed';
    } else if (daysDiff <= 7) {
        return 'course-start-soon';
    }
    return '';
}

// ===========================================
// AUTHENTICATION FUNCTIONS
// ===========================================

function savePersonalInfo() {
    const fullname = document.getElementById('fullname')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;

    if (!fullname?.trim() || !email?.trim()) {
        showMessage('Vui lòng điền đầy đủ họ tên và email!', 'error');
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showMessage('Địa chỉ email không hợp lệ!', 'error');
        return;
    }

    const saveBtn = document.querySelector('#personal-info-form .btn-primary');
    if (!saveBtn) return;

    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('full_name', fullname);
    formData.append('email', email);
    formData.append('phone', phone);

    fetch('/webapp/api/admin/update-profile', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');

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
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
        })
        .finally(() => {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
}

function changePassword() {
    const currentPassword = document.getElementById('current-password')?.value;
    const newPassword = document.getElementById('new-password')?.value;
    const confirmPassword = document.getElementById('confirm-password')?.value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        showMessage('Vui lòng điền đầy đủ thông tin mật khẩu!', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showMessage('Mật khẩu mới phải có ít nhất 6 ký tự!', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showMessage('Mật khẩu xác nhận không khớp!', 'error');
        return;
    }

    if (currentPassword === newPassword) {
        showMessage('Mật khẩu mới phải khác mật khẩu hiện tại!', 'error');
        return;
    }

    const changeBtn = document.querySelector('#change-password-form .btn-primary');
    if (!changeBtn) return;

    const originalText = changeBtn.innerHTML;
    changeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thay đổi...';
    changeBtn.disabled = true;

    const formData = new FormData();
    formData.append('current_password', currentPassword);
    formData.append('new_password', newPassword);
    formData.append('confirm_password', confirmPassword);

    fetch('/webapp/api/admin/change-password', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                document.getElementById('change-password-form')?.reset();
            } else {
                showMessage(data.message || 'Đổi mật khẩu thất bại', 'error');
            }
        })
        .catch(error => {
            showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
        })
        .finally(() => {
            changeBtn.innerHTML = originalText;
            changeBtn.disabled = false;
        });
}

// ===========================================
// CHART FUNCTIONS
// ===========================================

function initializeCharts() {
    initStudentRegistrationChart();
    initClassDistributionChart();
}

function initStudentRegistrationChart() {
    const ctx = document.getElementById('studentRegistrationChart');
    if (!ctx || !adminData?.student_registration_trend) return;

    const data = adminData.student_registration_trend;
    const labels = data.map(item => item.month_name);
    const values = data.map(item => item.student_count);

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
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(7, 58, 75, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#108AB1',
                    borderWidth: 1,
                    callbacks: {
                        label: function (context) {
                            return `${context.parsed.y} học viên`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#666' },
                    grid: { color: 'rgba(0, 0, 0, 0.1)' }
                },
                x: {
                    ticks: { color: '#666', maxRotation: 45 },
                    grid: { display: false }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    });
}

function initClassDistributionChart() {
    const ctx = document.getElementById('classDistributionChart');
    if (!ctx || !adminData?.class_level_distribution) return;

    const data = adminData.class_level_distribution;
    const labels = data.map(item => item.class_level || 'Không xác định');
    const values = data.map(item => item.class_count);
    const percentages = data.map(item => item.percentage);

    const colors = [
        'rgba(16, 138, 177, 0.8)',
        'rgba(7, 58, 75, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(40, 167, 69, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(108, 117, 125, 0.8)'
    ];

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
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(7, 58, 75, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#108AB1',
                    borderWidth: 1,
                    callbacks: {
                        label: function (context) {
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

    createClassDistributionLegend(data, colors);
}

function createClassDistributionLegend(data, colors) {
    const legendContainer = document.getElementById('class-distribution-legend');
    if (!legendContainer) return;

    const legendHtml = data.map((item, index) => `
        <div class="legend-item">
            <div class="legend-color" style="background-color: ${colors[index]}"></div>
            <span class="legend-label">${item.class_level || 'Không xác định'}</span>
            <span class="legend-value">${item.class_count} lớp (${item.percentage}%)</span>
        </div>
    `).join('');

    legendContainer.innerHTML = legendHtml;
}

function updateRegistrationChart() {
    const yearSelect = document.getElementById('registration-year-select');
    if (!yearSelect) return;

    const selectedYear = yearSelect.value;
    const chartContainer = document.querySelector('#studentRegistrationChart')?.parentElement;

    if (!chartContainer) return;

    chartContainer.innerHTML = '<div class="chart-loading">Đang tải...</div>';

    fetch(`/webapp/api/admin/chart-data?type=student_registration&year=${selectedYear}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chartContainer.innerHTML = '<canvas id="studentRegistrationChart"></canvas>';
                adminData.student_registration_trend = data.data;
                initStudentRegistrationChart();
            } else {
                showMessage('Lỗi khi tải dữ liệu biểu đồ', 'error');
            }
        })
        .catch(error => {
            showMessage('Lỗi kết nối khi tải biểu đồ', 'error');
        });
}

// ===========================================
// COURSE MANAGEMENT FUNCTIONS
// ===========================================

function loadCourses() {
    console.log('🔄 Starting loadCourses function...');

    const coursesGrid = document.getElementById('courses-grid');
    if (!coursesGrid) {
        console.error('❌ courses-grid element not found in DOM');
        // Create a temporary display area if missing
        const courseSection = document.querySelector('#manage_courses') || document.querySelector('.course-management');
        if (courseSection) {
            courseSection.innerHTML += '<div id="courses-grid" style="display: grid; gap: 20px; margin-top: 20px;"></div>';
        } else {
            showMessage('Không tìm thấy khu vực hiển thị khóa học', 'error');
            return;
        }
    }

    const grid = document.getElementById('courses-grid');
    grid.innerHTML = `
        <div class="loading-state" style="grid-column: 1 / -1; text-align: center; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #108AB1; margin-bottom: 15px;"></i>
            <p style="color: #666; font-size: 1.1rem;">Đang tải danh sách khóa học...</p>
        </div>
    `;

    // Try multiple API endpoints as fallback
    const apiEndpoints = [
        '/webapp/api/admin/get-courses',
        '/webapp/api/courses/list',
        '/webapp/api/get-courses'
    ];

    tryFetchFromEndpoints(apiEndpoints, 0);
}

function tryFetchFromEndpoints(endpoints, index) {
    if (index >= endpoints.length) {
        console.error('❌ All API endpoints failed');
        showErrorCourses("Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng.");
        return;
    }

    const endpoint = endpoints[index];
    console.log(`🌐 Trying endpoint ${index + 1}/${endpoints.length}: ${endpoint}`);

    fetch(endpoint, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log(`📡 Response from ${endpoint}:`, response.status, response.statusText);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Data received from', endpoint, ':', data);

            if (data && data.success && Array.isArray(data.courses)) {
                allCourses = data.courses;
                filteredCourses = data.courses;

                console.log(`📊 Loaded ${allCourses.length} courses successfully`);

                if (allCourses.length > 0) {
                    displayCourses(allCourses);
                    updateCourseStats(allCourses);
                } else {
                    showNoCourses();
                }
            } else if (data && Array.isArray(data)) {
                // Handle direct array response
                allCourses = data;
                filteredCourses = data;
                displayCourses(data);
                updateCourseStats(data);
            } else {
                throw new Error('Invalid data format: ' + JSON.stringify(data));
            }
        })
        .catch(error => {
            console.error(`❌ Error with ${endpoint}:`, error);
            // Try next endpoint
            setTimeout(() => {
                tryFetchFromEndpoints(endpoints, index + 1);
            }, 500);
        });
}

// Add course statistics update function
function updateCourseStats(courses) {
    const totalCourses = courses.length;
    const activeCourses = courses.filter(c => c.status === 'active' || !c.status).length;
    const totalStudents = courses.reduce((sum, c) => sum + (parseInt(c.current_students) || 0), 0);

    // Update stats in DOM if elements exist
    const statsElements = {
        'total-courses': totalCourses,
        'active-courses': activeCourses,
        'total-enrolled': totalStudents
    };

    Object.entries(statsElements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });

    console.log('📈 Course stats updated:', statsElements);
}

function loadTutors() {
    fetch('/webapp/api/admin/get-tutors')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tutors = data.tutors;
            }
        })
        .catch(error => {
            console.error('Error loading tutors:', error);
        });
}

function showNoCourses() {
    const coursesGrid = document.getElementById('courses-grid');
    if (!coursesGrid) return;

    coursesGrid.innerHTML = `
        <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 15px;">Chưa có khóa học nào</h3>
            <p style="color: #999; margin-bottom: 25px;">Nhấn "Tạo khóa học mới" để bắt đầu tạo khóa học đầu tiên</p>
            <button class="btn btn-primary" onclick="showCreateCourseModal()" style="padding: 12px 24px; font-size: 1rem;">
                <i class="fas fa-plus"></i> Tạo khóa học đầu tiên
            </button>
        </div>
    `;
}

function showErrorCourses(message) {
    const coursesGrid = document.getElementById('courses-grid');
    if (!coursesGrid) return;

    coursesGrid.innerHTML = `
        <div class="error-state" style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;"></i>
            <h3 style="color: #dc3545; margin-bottom: 15px;">Lỗi tải dữ liệu</h3>
            <p style="color: #666; margin-bottom: 25px;">${message}</p>
            <button class="btn btn-primary" onclick="loadCourses()" style="padding: 12px 24px; font-size: 1rem;">
                <i class="fas fa-refresh"></i> Thử lại
            </button>
        </div>
    `;
}

function displayCourses(courses) {
    const coursesGrid = document.getElementById('courses-grid');
    if (!coursesGrid) return;

    if (!courses || courses.length === 0) {
        showNoCourses();
        return;
    }

    let coursesHtml = '';

    courses.forEach((course) => {
        const courseCode = generateCourseCode(course);
        const tutorName = course.tutor_name || 'Chưa phân công';
        const statusClass = (course.status || 'active').toLowerCase();
        const statusText = getStatusText(course.status || 'active');
        const levelClass = (course.class_level || 'unknown').toLowerCase().replace(' ', '-');
        const isClosed = course.status === 'closed';
        const isInactive = course.status === 'inactive';

        const sessionsCompleted = course.actual_sessions_completed || course.sessions_completed || 0;
        const sessionsTotal = course.sessions_total || 0;
        const startDate = course.start_date ? formatDateShort(course.start_date) : 'Chưa xác định';

        // Add data-status attribute for hover styling
        coursesHtml += `
            <div class="course-card" 
                 data-status="${course.status || 'active'}" 
                 onclick="showCourseDetail(${course.id})">
                <div class="course-header">
                    <div>
                        <h3 class="course-title">${courseCode}</h3>
                        <span class="course-level ${levelClass}">${course.class_level || 'Không xác định'}</span>
                    </div>
                    <span class="course-status ${statusClass}">${statusText}</span>
                </div>
                
                <div class="course-info">
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-calendar-alt"></i> Ngày bắt đầu:</span>
                        <span class="course-info-value ${getStartDateClass(course.start_date)}">${startDate}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-chalkboard-teacher"></i> Giảng viên:</span>
                        <span class="course-info-value">${tutorName}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-users"></i> Học viên:</span>
                        <span class="course-info-value">${course.current_students || 0}/${course.max_students || 0}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-clock"></i> Thời gian:</span>
                        <span class="course-info-value">${formatSchedule(course)}</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-money-bill-wave"></i> Giá:</span>
                        <span class="course-info-value">${formatCurrency(course.price_per_session || 0)}/buổi</span>
                    </div>
                    <div class="course-info-item">
                        <span class="course-info-label"><i class="fas fa-chart-line"></i> Tiến độ:</span>
                        <span class="course-info-value">${sessionsCompleted}/${sessionsTotal} buổi</span>
                    </div>
                </div>
                
                <div class="course-actions" onclick="event.stopPropagation()">
                    ${!isClosed && !isInactive ? `
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
                    ` : `
                        <button class="btn btn-success btn-sm" onclick="reopenCourse(${course.id})">
                            <i class="fas fa-unlock"></i> Mở lại
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="showCourseDetail(${course.id})">
                            <i class="fas fa-eye"></i> Xem thông tin
                        </button>
                    `}
                </div>
            </div>
        `;
    });

    coursesGrid.innerHTML = coursesHtml;
}

// ===========================================
// COURSE MODAL FUNCTIONS
// ===========================================

function showCreateCourseModal() {
    console.log('🔄 Attempting to show create course modal...');

    // Find the existing modal in the HTML
    let modal = document.getElementById('create-course-modal');

    if (!modal) {
        console.error('❌ create-course-modal not found in DOM');
        showMessage('Lỗi: Không tìm thấy modal tạo khóa học', 'error');
        return;
    }

    // Show the modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Add show class for animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Set minimum date to today
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');

    if (startDateInput) {
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;

        if (!startDateInput.value) {
            // Set default to next Monday
            const nextMonday = new Date();
            nextMonday.setDate(nextMonday.getDate() + (1 + 7 - nextMonday.getDay()) % 7);
            startDateInput.value = nextMonday.toISOString().split('T')[0];
        }
    }

    if (endDateInput && startDateInput) {
        // Set end date to 3 months after start date
        startDateInput.addEventListener('change', function () {
            const startDate = new Date(this.value);
            const endDate = new Date(startDate);
            endDate.setMonth(endDate.getMonth() + 3);
            endDateInput.value = endDate.toISOString().split('T')[0];
            endDateInput.min = this.value;
        });
    }

    // Load tutors for the form
    loadTutorsForForm();

    // Add backdrop click handler
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeCreateCourseModal();
        }
    });

    console.log('✅ Create course modal shown successfully');
}

function closeCreateCourseModal() {
    const modal = document.getElementById('create-course-modal');
    if (!modal) return;

    console.log('🔄 Closing create course modal...');

    // Remove show class for exit animation
    modal.classList.remove('show');

    // Hide modal after animation
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset form
        const form = document.getElementById('create-course-form');
        if (form) {
            form.reset();
            // Uncheck all checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
    }, 300);

    console.log('✅ Create course modal closed');
}

function loadTutorsForForm() {
    const tutorSelect = document.getElementById('tutor-id');
    if (!tutorSelect) {
        console.warn('⚠️ tutor-id select not found');
        return;
    }

    tutorSelect.innerHTML = '<option value="">Đang tải...</option>';

    if (tutors && tutors.length > 0) {
        tutorSelect.innerHTML = '<option value="">Chọn sau</option>';
        tutors.forEach(tutor => {
            const option = document.createElement('option');
            option.value = tutor.id;
            option.textContent = tutor.full_name;
            tutorSelect.appendChild(option);
        });
        console.log(`✅ Loaded ${tutors.length} tutors to form`);
    } else {
        // Try to load tutors from API
        fetch('/webapp/api/admin/get-tutors')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.tutors) {
                    tutors = data.tutors;
                    tutorSelect.innerHTML = '<option value="">Chọn sau</option>';
                    tutors.forEach(tutor => {
                        const option = document.createElement('option');
                        option.value = tutor.id;
                        option.textContent = tutor.full_name;
                        tutorSelect.appendChild(option);
                    });
                    console.log(`✅ Fetched and loaded ${tutors.length} tutors`);
                } else {
                    tutorSelect.innerHTML = '<option value="">Không có giảng viên</option>';
                    console.warn('⚠️ No tutors found in API response');
                }
            })
            .catch(error => {
                tutorSelect.innerHTML = '<option value="">Lỗi tải danh sách</option>';
                console.error('❌ Error loading tutors:', error);
            });
    }
}

function createCourse(event) {
    event.preventDefault();
    console.log('🔄 Creating course...');

    const form = event.target;
    const formData = new FormData(form);

    // Collect selected schedule days
    const scheduleDays = [];
    form.querySelectorAll('input[name="schedule_days"]:checked').forEach(checkbox => {
        scheduleDays.push(checkbox.value);
    });

    if (scheduleDays.length === 0) {
        showMessage('Vui lòng chọn ít nhất một ngày học trong tuần', 'error');
        return;
    }

    // Add schedule days to form data
    formData.set('schedule_days', scheduleDays.join(','));

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
        submitBtn.disabled = true;

        // Create course via API
        fetch('/webapp/api/admin/create-course', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Tạo khóa học thành công!', 'success');
                    closeCreateCourseModal();
                    // Reload courses list
                    loadCourses();
                } else {
                    showMessage(data.message || 'Lỗi khi tạo khóa học', 'error');
                }
            })
            .catch(error => {
                console.error('Error creating course:', error);
                showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    }
}

// ===========================================
// COURSE DETAIL FUNCTIONS
// ===========================================

function showCourseDetail(courseId) {
    console.log('Showing course detail for ID:', courseId);
    const modal = document.getElementById('course-detail-modal');
    const content = document.getElementById('course-detail-content');

    if (!modal || !content) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading state
    content.innerHTML = `
        <div class="loading-state text-center p-5">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <p>Đang tải thông tin khóa học...</p>
        </div>
    `;

    // Show modal with animation
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Add show class for animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Update close button onclick event
    const closeBtn = modal.querySelector('.close');
    if (closeBtn) {
        closeBtn.onclick = closeCourseDetailModal;
    }

    // Add click event to modal backdrop
    modal.onclick = function (event) {
        if (event.target === modal) {
            closeCourseDetailModal();
        }
    };

    // Fetch course details
    fetch(`/webapp/api/admin/course-details?id=${courseId}`)
        .then(response => {
            console.log('API Response:', response);
            return response.json();
        })
        .then(data => {
            console.log('Course data:', data);
            if (data.success && data.data) {
                const course = data.data;
                content.innerHTML = `
                    <div class="course-details p-4">
                        <div class="section mb-4">
                            <h4 class="mb-3">Thông tin cơ bản</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Tên khóa học:</strong> ${course.class_name}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Môn học:</strong> ${course.subject}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Cấp độ:</strong> ${course.class_level}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Trạng thái:</strong> 
                                    <span class="status ${course.status}">${getStatusText(course.status)}</span>
                                </div>
                            </div>
                        </div>

                        <div class="section mb-4">
                            <h4 class="mb-3">Lịch học & Tiến độ</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Lịch học:</strong> ${course.schedule_days} ${course.schedule_time}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Thời lượng:</strong> ${course.schedule_duration} phút/buổi
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Số buổi:</strong> ${course.completed_sessions}/${course.sessions_total}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Học phí:</strong> ${formatCurrency(course.price_per_session)}/buổi
                                </div>
                            </div>
                        </div>

                        <div class="section mb-4">
                            <h4 class="mb-3">Thông tin giảng viên</h4>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <strong>Giảng viên phụ trách:</strong> ${course.tutor_name}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong> ${course.tutor_email || 'Chưa có'}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>SĐT:</strong> ${course.tutor_phone || 'Chưa có'}
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h4 class="mb-3">Danh sách học viên (${course.students ? course.students.length : 0})</h4>
                            ${course.students && course.students.length > 0 ? `
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>SĐT</th>
                                                <th>Ngày đăng ký</th>
                                                <th>Buổi tham gia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${course.students.map(student => `
                                                <tr>
                                                    <td>${student.full_name}</td>
                                                    <td>${student.email}</td>
                                                    <td>${student.phone || 'N/A'}</td>
                                                    <td>${formatDate(student.enrollment_date)}</td>
                                                    <td>${student.attended_sessions}/${course.completed_sessions}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            ` : '<p class="text-center">Chưa có học viên đăng ký</p>'}
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `
                    <div class="error-state text-center p-5">
                        <i class="fas fa-exclamation-circle text-danger fa-2x mb-3"></i>
                        <p class="text-danger">${data.message || 'Không thể tải thông tin khóa học'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="error-state text-center p-5">
                    <i class="fas fa-exclamation-triangle text-danger fa-2x mb-3"></i>
                    <p class="text-danger">Lỗi kết nối máy chủ</p>
                </div>
            `;
        });
}

function closeCreateCourseModal() {
    const modal = document.getElementById('create-course-modal');
    if (!modal) return;

    console.log('🔄 Closing create course modal...');

    // Remove show class for exit animation
    modal.classList.remove('show');

    // Hide modal after animation
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset form
        const form = document.getElementById('create-course-form');
        if (form) {
            form.reset();
            // Uncheck all checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        }
    }, 300);

    console.log('✅ Create course modal closed');
}

// ===========================================
// COURSE ACTION FUNCTIONS
// ===========================================

function editCourse(courseId, event) {
    if (event) {
        event.stopPropagation();
    }
    console.log('Editing course:', courseId);

    const modal = document.getElementById('edit-course-modal');
    const form = document.getElementById('edit-course-form');

    if (!modal || !form) {
        console.error('Required modal elements not found');
        return;
    }

    // Show loading state
    form.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin khóa học...</p>
        </div>
    `;

    // Show modal immediately
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    // Add show class for animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    fetch(`/webapp/api/admin/course-details?id=${courseId}`)
        .then(response => {
            console.log('API Response:', response);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Course data:', data);
            if (data.success && data.data) {
                populateEditForm(data.data);
            } else {
                throw new Error(data.message || 'Không thể tải thông tin khóa học');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi khi tải thông tin khóa học: ' + error.message, 'error');
            closeEditCourseModal();
        });
}

function loadTutorsForEditForm(selectedTutorId) {
    const tutorSelect = document.getElementById('edit-tutor-id');
    if (!tutorSelect) return;

    fetch('/webapp/api/admin/get-tutors')
        .then(response => response.json())
        .then(data => {
            if (data.success && Array.isArray(data.tutors)) {
                tutorSelect.innerHTML = '<option value="">Chọn giảng viên</option>';
                data.tutors.forEach(tutor => {
                    const option = document.createElement('option');
                    option.value = tutor.id;
                    option.textContent = tutor.full_name;
                    option.selected = tutor.id == selectedTutorId;
                    tutorSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading tutors:', error);
        });
}

function populateEditForm(course) {
    const form = document.getElementById('edit-course-form');
    if (!form) return;

    // Reset form with full structure matching create form
    form.innerHTML = `
        <input type="hidden" id="edit-course-id" name="course_id" value="${course.id}">
        <div class="form-grid">
            <div class="form-group">
                <label for="edit-class-name">Tên lớp <span class="required">*</span></label>
                <input type="text" id="edit-class-name" name="class_name" required placeholder="Ví dụ: IR, SP, LS...">
            </div>

            <div class="form-group">
                <label for="edit-class-year">Năm học <span class="required">*</span></label>
                <select id="edit-class-year" name="class_year" required>
                    <option value="">Chọn năm học</option>
                    <option value="2025">2025</option>
                    <option value="2026">2026</option>
                    <option value="2027">2027</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-class-level">Cấp lớp <span class="required">*</span></label>
                <select id="edit-class-level" name="class_level" required>
                    <option value="">Chọn cấp lớp</option>
                    <option value="Sơ cấp">Sơ cấp</option>
                    <option value="Trung cấp">Trung cấp</option>
                    <option value="Nâng cao">Nâng cao</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-subject">Môn học <span class="required">*</span></label>
                <select id="edit-subject" name="subject" required>
                    <option value="">Chọn môn học</option>
                    <option value="IELTS Speaking">IELTS Speaking</option>
                    <option value="IELTS Listening">IELTS Listening</option>
                    <option value="IELTS Reading">IELTS Reading</option>
                    <option value="IELTS Writing">IELTS Writing</option>
                    <option value="TOEIC Listening/Reading">TOEIC Listening/Reading</option>
                    <option value="TOEIC 4 Skills">TOEIC 4 Skills</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-tutor-id">Giảng viên</label>
                <select id="edit-tutor-id" name="tutor_id">
                    <option value="">Chọn giảng viên</option>
                </select>
            </div>

            <div class="form-group">
                <label for="edit-max-students">Số học sinh tối đa <span class="required">*</span></label>
                <input type="number" id="edit-max-students" name="max_students" required min="1" max="50" placeholder="15">
            </div>

            <div class="form-group">
                <label for="edit-sessions-total">Số buổi học <span class="required">*</span></label>
                <input type="number" id="edit-sessions-total" name="sessions_total" required min="1" placeholder="30">
            </div>

            <div class="form-group">
                <label for="edit-price-per-session">Giá tiền mỗi buổi (VNĐ) <span class="required">*</span></label>
                <input type="number" id="edit-price-per-session" name="price_per_session" required min="0" step="1000" placeholder="300000">
            </div>

            <div class="form-group">
                <label for="edit-schedule-time">Thời gian học <span class="required">*</span></label>
                <input type="time" id="edit-schedule-time" name="schedule_time" required>
            </div>

            <div class="form-group">
                <label for="edit-schedule-duration">Thời lượng (phút) <span class="required">*</span></label>
                <input type="number" id="edit-schedule-duration" name="schedule_duration" required min="30" step="15" placeholder="120">
            </div>

            <div class="form-group full-width">
                <label>Ngày học trong tuần <span class="required">*</span></label>
                <div class="checkbox-group" id="edit-schedule-days">
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T2"> Thứ 2
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T3"> Thứ 3
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T4"> Thứ 4
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T5"> Thứ 5
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T6"> Thứ 6
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="T7"> Thứ 7
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="schedule_days" value="CN"> Chủ nhật
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="edit-start-date">Ngày khai giảng <span class="required">*</span></label>
                <input type="date" id="edit-start-date" name="start_date" required>
            </div>

            <div class="form-group">
                <label for="edit-end-date">Ngày kết thúc <span class="required">*</span></label>
                <input type="date" id="edit-end-date" name="end_date" required>
            </div>

            <div class="form-group full-width">
                <label for="edit-description">Mô tả lớp học</label>
                <textarea id="edit-description" name="description" rows="3" placeholder="Mô tả chi tiết về khóa học..."></textarea>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditCourseModal()">Hủy</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    `;

    // Populate form fields with course data
    document.getElementById('edit-class-name').value = course.class_name || '';
    document.getElementById('edit-class-year').value = course.class_year || '';
    document.getElementById('edit-class-level').value = course.class_level || '';
    document.getElementById('edit-subject').value = course.subject || '';
    document.getElementById('edit-max-students').value = course.max_students || '';
    document.getElementById('edit-sessions-total').value = course.sessions_total || '';
    document.getElementById('edit-price-per-session').value = course.price_per_session || '';
    document.getElementById('edit-schedule-time').value = course.schedule_time || '';
    document.getElementById('edit-schedule-duration').value = course.schedule_duration || '';
    document.getElementById('edit-start-date').value = course.start_date || '';
    document.getElementById('edit-end-date').value = course.end_date || '';
    document.getElementById('edit-description').value = course.description || '';

    // Handle schedule days checkboxes
    const scheduleDays = course.schedule_days ? course.schedule_days.split(',') : [];
    document.querySelectorAll('#edit-schedule-days input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = scheduleDays.includes(checkbox.value);
    });

    // Load tutors dropdown
    loadTutorsForEditForm(course.tutor_id);

    // Add form submit event listener
    form.addEventListener('submit', updateCourse);
}

function closeEditCourseModal() {
    const modal = document.getElementById('edit-course-modal');
    if (!modal) return;

    console.log('🔄 Closing edit course modal...');

    // Remove show class for exit animation
    modal.classList.remove('show');

    // Hide modal after animation
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Reset form
        const form = document.getElementById('edit-course-form');
        if (form) {
            form.reset();
            form.innerHTML = ''; // Clear form content
        }
    }, 300);

    console.log('✅ Edit course modal closed');
}

function closeCourseDetailModal() {
    const modal = document.getElementById('course-detail-modal');
    if (!modal) return;

    console.log('🔄 Closing course detail modal...');

    // Remove show class for exit animation
    modal.classList.remove('show');

    // Hide modal after animation
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';

        // Clear content
        const content = document.getElementById('course-detail-content');
        if (content) {
            content.innerHTML = '';
        }
    }, 300);

    console.log('✅ Course detail modal closed');
}

function updateCourse(event) {
    event.preventDefault();
    console.log('Updating course...');

    const form = event.target;
    const formData = new FormData(form);

<<<<<<< Updated upstream
=======
    // Validate required fields
    const requiredFields = ['class_name', 'class_year', 'class_level', 'subject', 'max_students', 'sessions_total', 'price_per_session', 'schedule_time', 'schedule_duration', 'start_date', 'end_date'];

    for (const field of requiredFields) {
        if (!formData.get(field)) {
            showMessage(`Vui lòng điền đầy đủ thông tin: ${field}`, 'error');
            return;
        }
    }

>>>>>>> Stashed changes
    // Get selected schedule days
    const scheduleDays = [];
    form.querySelectorAll('input[name="schedule_days"]:checked').forEach(checkbox => {
        scheduleDays.push(checkbox.value);
    });
<<<<<<< Updated upstream
=======

    if (scheduleDays.length === 0) {
        showMessage('Vui lòng chọn ít nhất một ngày học trong tuần', 'error');
        return;
    }

>>>>>>> Stashed changes
    formData.set('schedule_days', scheduleDays.join(','));

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    submitBtn.disabled = true;

    console.log('Form data:', Object.fromEntries(formData));

    fetch('/webapp/api/admin/update-course', {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(async response => {
            console.log('Update response:', response);
<<<<<<< Updated upstream
=======

            // Get response text first
>>>>>>> Stashed changes
            const text = await response.text();
            console.log('Response text:', text);

            try {
                const data = text ? JSON.parse(text) : {};
                if (!response.ok) {
                    throw new Error(data.message || `Server responded with ${response.status}`);
                }
                return data;
            } catch (e) {
                console.error('Parse error:', e);
                throw new Error(`Server error: ${text || response.statusText}`);
            }
        })
        .then(data => {
            if (data.success) {
                showMessage('Cập nhật khóa học thành công!', 'success');
                closeEditCourseModal();
                loadCourses(); // Refresh the courses list
            } else {
                throw new Error(data.message || 'Cập nhật không thành công');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi khi cập nhật khóa học: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// ===========================================
// ADD TUTOR MODAL FUNCTIONS
// ===========================================

// Show Add Tutor Modal
function showAddTutorModal() {
    const modal = document.getElementById('add-tutor-modal');
    if (!modal) return;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

<<<<<<< Updated upstream
=======
    // Set default values and generate discount code
    const discountPercentageInput = document.getElementById('tutor-discount-percentage');
    if (discountPercentageInput) {
        discountPercentageInput.value = '5';
    }

    // Auto-generate discount code
    generateDiscountCode();

>>>>>>> Stashed changes
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

<<<<<<< Updated upstream
=======
function generateDiscountCode() {
    // Generate a unique 8-character discount code
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    document.getElementById('tutor-discount-code').value = code;
}

>>>>>>> Stashed changes
// Close Add Tutor Modal
function closeAddTutorModal() {
    const modal = document.getElementById('add-tutor-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('add-tutor-form').reset();
    }, 300);
}

// Create Tutor
function createTutor(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    submitBtn.disabled = true;

    fetch('/webapp/api/admin/create-tutor', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Tạo giáo viên mới thành công!', 'success');
                closeAddTutorModal();
                // Reload tutors list if needed
                loadTutors();
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}
// Load and display tutors
<<<<<<< Updated upstream
=======
function searchTutors() {
    const searchTerm = document.getElementById('tutor-search').value.toLowerCase();

    if (!tutors || tutors.length === 0) {
        // If no tutors loaded, try to load them first
        loadTutors();
        return;
    }

    let filteredTutors;

    if (searchTerm.trim() === '') {
        filteredTutors = tutors;
    } else {
        filteredTutors = tutors.filter(tutor => {
            const searchFields = [
                tutor.full_name || '',
                tutor.email || '',
                tutor.phone || '',
                tutor.username || ''
            ];

            return searchFields.some(field =>
                field.toLowerCase().includes(searchTerm)
            );
        });
    }

    displayTutors(filteredTutors);
}

// Make sure tutors are loaded and stored in global variable
>>>>>>> Stashed changes
function loadTutors() {
    const tutorsGrid = document.querySelector('.teachers-grid');
    if (!tutorsGrid) return;

    // Show loading state
    tutorsGrid.innerHTML = `
        <div class="loading-state" style="grid-column: 1 / -1;">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải danh sách giáo viên...</p>
        </div>
    `;

    fetch('/webapp/api/admin/get-tutors')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tutors) {
                displayTutors(data.tutors);
            } else {
                throw new Error(data.message || 'Không thể tải danh sách giáo viên');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tutorsGrid.innerHTML = `
                <div class="error-state" style="grid-column: 1 / -1;">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Không thể tải danh sách giáo viên</h3>
                    <p>${error.message}</p>
                    <button onclick="loadTutors()" class="btn-primary">
                        <i class="fas fa-sync"></i> Thử lại
                    </button>
                </div>
            `;
        });
}

// Display tutors in grid
function displayTutors(tutors) {
    const tutorsGrid = document.querySelector('.teachers-grid');
    if (!tutorsGrid) return;

    if (!tutors.length) {
        tutorsGrid.innerHTML = `
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-user-tie"></i>
                <h3>Chưa có giáo viên nào</h3>
                <p>Nhấn nút "Thêm Giáo Viên Mới" để bắt đầu</p>
            </div>
        `;
        return;
    }

    tutorsGrid.innerHTML = tutors.map(tutor => `
        <div class="teacher-card">
            <div class="teacher-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <h4>${tutor.full_name}</h4>
            <p>${tutor.email}</p>
            <div class="teacher-stats">
                <span><i class="fas fa-phone"></i> ${tutor.phone || 'N/A'}</span>
                <span><i class="fas fa-calendar-alt"></i> ${formatDate(tutor.created_at)}</span>
            </div>
            <div class="teacher-actions">
                <button class="btn-edit" onclick="editTutor(${tutor.id})">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </button>
                <button class="btn-view" onclick="viewTutor(${tutor.id})">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </button>
            </div>
        </div>
    `).join('');
}

// View tutor details
function viewTutor(tutorId) {
    console.log('Viewing tutor:', tutorId);
    const modal = document.getElementById('tutor-detail-modal');
    const content = document.getElementById('tutor-detail-content');

    if (!modal || !content) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading state
    content.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin giáo viên...</p>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Fetch tutor details
    fetch(`/webapp/api/admin/tutor-details?id=${tutorId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load tutor details');
            }

            const tutor = data.tutor;
            content.innerHTML = `
                <div class="tutor-detail">
                    <div class="tutor-profile">
                        <div class="tutor-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h2>${tutor.full_name}</h2>
                        <p class="tutor-status ${tutor.is_active ? 'active' : 'inactive'}">
                            <i class="fas fa-circle"></i> 
                            ${tutor.is_active ? 'Đang hoạt động' : 'Không hoạt động'}
                        </p>
                    </div>
                    
                    <div class="info-section">
                        <h3>Thông tin liên hệ</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <i class="fas fa-envelope"></i>
                                <span class="label">Email:</span>
                                <span class="value">${tutor.email}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <span class="label">Số điện thoại:</span>
                                <span class="value">${tutor.phone || 'Chưa cập nhật'}</span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Lớp học đang dạy (${tutor.active_classes})</h3>
                        <div class="classes-grid">
                            ${tutor.classes && tutor.classes.length > 0 ?
                    tutor.classes.map(course => `
                                    <div class="class-item">
                                        <div class="class-name">
                                            <i class="fas fa-book-open"></i>
                                            ${course.class_name}
                                        </div>
                                        <div class="class-info">
                                            <span><i class="fas fa-users"></i> ${course.enrolled_students}/${course.max_students}</span>
                                            <span><i class="fas fa-calendar"></i> ${formatSchedule(course)}</span>
                                        </div>
                                    </div>
                                `).join('') :
                    '<p class="no-classes">Chưa có lớp học nào được phân công</p>'
                }
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Có lỗi xảy ra</h3>
                    <p>${error.message}</p>
                </div>
            `;
        });
}

// Close tutor detail modal
function closeTutorDetailModal() {
    const modal = document.getElementById('tutor-detail-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        const content = document.getElementById('tutor-detail-content');
        if (content) {
            content.innerHTML = '';
        }
    }, 300);
}

// Edit tutor functions
function editTutor(tutorId) {
    console.log('Editing tutor:', tutorId);
    const modal = document.getElementById('edit-tutor-modal');
    const form = document.getElementById('edit-tutor-form');

    if (!modal || !form) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading state
    form.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin giáo viên...</p>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Fetch tutor details
    fetch(`/webapp/api/admin/tutor-details?id=${tutorId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to load tutor details');
            }

            const tutor = data.tutor;
            populateEditTutorForm(tutor);
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi khi tải thông tin: ' + error.message, 'error');
            closeEditTutorModal();
        });
}

function populateEditTutorForm(tutor) {
    const form = document.getElementById('edit-tutor-form');
    form.innerHTML = `
        <input type="hidden" id="edit-tutor-id" name="tutor_id" value="${tutor.id}">
        
        <div class="form-group">
            <label for="edit-tutor-fullname">Họ và Tên <span class="required">*</span></label>
            <input type="text" id="edit-tutor-fullname" name="fullname" required 
                   value="${tutor.full_name || ''}">
        </div>

        <div class="form-group">
            <label for="edit-tutor-email">Email <span class="required">*</span></label>
            <input type="email" id="edit-tutor-email" name="email" required 
                   value="${tutor.email || ''}">
        </div>

        <div class="form-group">
            <label for="edit-tutor-phone">Số điện thoại</label>
            <input type="tel" id="edit-tutor-phone" name="phone" 
                   value="${tutor.phone || ''}">
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeEditTutorModal()">Hủy</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    `;
}

function updateTutor(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    submitBtn.disabled = true;

    fetch('/webapp/api/admin/update-tutor', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Cập nhật thông tin thành công!', 'success');
                closeEditTutorModal();
                loadTutors(); // Refresh tutors list
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function closeEditTutorModal() {
    const modal = document.getElementById('edit-tutor-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        const form = document.getElementById('edit-tutor-form');
        if (form) form.reset();
    }, 300);
<<<<<<< Updated upstream
=======
}

// Load and display students
function loadStudents() {
    const tableBody = document.getElementById('students-table-body');
    const loadingElement = document.getElementById('students-loading');
    const noStudentsElement = document.getElementById('no-students');

    if (!tableBody || !loadingElement || !noStudentsElement) return;

    // Show loading state
    tableBody.style.display = 'none';
    loadingElement.style.display = 'block';
    noStudentsElement.style.display = 'none';

    fetch('/webapp/api/admin/get-students')
        .then(response => response.json())
        .then(data => {
            loadingElement.style.display = 'none';

            if (data.success && data.students && data.students.length > 0) {
                tableBody.innerHTML = data.students.map((student, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${student.id}</td>
                        <td>${student.full_name}</td>
                        <td>${student.email}</td>
                        <td>${formatDate(student.created_at)}</td>
                        <td>
                            <span class="student-status ${student.is_active ? 'active' : 'inactive'}">
                                ${student.is_active ? 'Đang hoạt động' : 'Ngừng hoạt động'}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewStudent(${student.id})" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editStudent(${student.id})" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
                tableBody.style.display = 'table-row-group';
            } else {
                noStudentsElement.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            loadingElement.style.display = 'none';
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        Có lỗi xảy ra khi tải danh sách học viên
                    </td>
                </tr>
            `;
            tableBody.style.display = 'table-row-group';
        });
}

// Helper function to format date
function formatDate(dateString) {
    const options = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('vi-VN', options);
}

// Call loadStudents when the page loads
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('manage_students').classList.contains('active')) {
        loadStudents();
    }
});

// Load students when switching to student management tab
document.querySelector('[href="#manage_students"]').addEventListener('click', function () {
    loadStudents();
});

// Edit student
function editStudent(studentId) {
    const modal = document.getElementById('edit-student-modal');
    const form = document.getElementById('edit-student-form');

    if (!modal || !form) {
        console.error('Modal elements not found');
        return;
    }

    // Show loading state
    form.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin học viên...</p>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Add show class for animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Fetch student details
    fetch(`/webapp/api/admin/student-details?id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.student) {
                populateEditStudentForm(data.student);
            } else {
                throw new Error(data.message || 'Failed to load student details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
            closeEditStudentModal();
        });
}

// Populate edit form
function populateEditStudentForm(student) {
    const form = document.getElementById('edit-student-form');
    form.innerHTML = `
        <input type="hidden" id="edit-student-id" name="student_id" value="${student.id}">
        
        <div class="form-group">
            <label for="edit-student-fullname">Họ và Tên <span class="required">*</span></label>
            <input type="text" id="edit-student-fullname" name="fullname" required 
                   value="${student.full_name || ''}">
        </div>

        <div class="form-group">
            <label for="edit-student-email">Email <span class="required">*</span></label>
            <input type="email" id="edit-student-email" name="email" required 
                   value="${student.email || ''}">
        </div>

        <div class="form-group">
            <label for="edit-student-phone">Số điện thoại</label>
            <input type="tel" id="edit-student-phone" name="phone" 
                   value="${student.phone || ''}">
        </div>

        <div class="form-group">
            <label for="edit-student-status">Trạng thái</label>
            <select id="edit-student-status" name="is_active">
                <option value="1" ${student.is_active == 1 ? 'selected' : ''}>Đang học</option>
                <option value="0" ${student.is_active == 0 ? 'selected' : ''}>Ngừng học</option>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-success" onclick="showAddToCourseModal(${student.id})">
                <i class="fas fa-plus-circle"></i> Thêm vào khóa học
            </button>
                                                                                                                                                                                                                                                                                                                                                                                                                         <button type="button" class="btn btn-secondary" onclick="closeEditStudentModal()">Hủy</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu thay đổi
            </button>
        </div>
    `;
}

function searchStudents() {
    const searchTerm = document.getElementById('student-search').value.toLowerCase();
    const tableBody = document.getElementById('students-table-body');

    if (!tableBody) return;

    // Get all table rows
    const rows = tableBody.querySelectorAll('tr');

    if (rows.length === 0) {
        // If no students loaded, try to load them first
        loadStudents();
        return;
    }

    let visibleCount = 0;

    rows.forEach(row => {
        if (searchTerm.trim() === '') {
            row.style.display = '';
            visibleCount++;
        } else {
            // Get text content from relevant cells (name, email, etc.)
            const cells = row.querySelectorAll('td');
            if (cells.length >= 4) {
                const studentId = cells[1].textContent.toLowerCase();
                const studentName = cells[2].textContent.toLowerCase();
                const studentEmail = cells[3].textContent.toLowerCase();

                const searchFields = [studentId, studentName, studentEmail];

                const isMatch = searchFields.some(field =>
                    field.includes(searchTerm)
                );

                if (isMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });

    // Show/hide empty state based on search results
    const noStudentsElement = document.getElementById('no-students');
    if (noStudentsElement) {
        if (visibleCount === 0 && searchTerm.trim() !== '') {
            noStudentsElement.style.display = 'block';
            noStudentsElement.innerHTML = `
                <i class="fas fa-search"></i>
                <h3>Không tìm thấy kết quả</h3>
                <p>Không có học viên nào phù hợp với từ khóa "${searchTerm}"</p>
            `;
        } else {
            noStudentsElement.style.display = 'none';
        }
    }
}

// Update student
function updateStudent(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
    submitBtn.disabled = true;

    fetch('/webapp/api/admin/update-student', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Cập nhật thông tin thành công!', 'success');
                closeEditStudentModal();
                loadStudents(); // Refresh students list
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

// Close edit modal
function closeEditStudentModal() {
    const modal = document.getElementById('edit-student-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        const form = document.getElementById('edit-student-form');
        if (form) form.reset();
    }, 300);
}

// ===========================================
// ADD STUDENT TO COURSE MODAL FUNCTIONS
// ===========================================

// Show Add Student to Course Modal
function showAddToCourseModal(studentId) {
    const modal = document.getElementById('add-student-course-modal');
    const coursesList = document.getElementById('available-courses-list');

    if (!modal || !coursesList) return;

    // Show loading state
    coursesList.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải danh sách khóa học...</p>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    fetch('/webapp/api/admin/available-courses')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.courses) {
                displayAvailableCourses(data.courses, studentId);
            } else {
                throw new Error(data.message || 'Failed to load courses');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            coursesList.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Có lỗi xảy ra</h3>
                    <p>${error.message}</p>
                </div>
            `;
        });
}

function displayAvailableCourses(courses, studentId) {
    const coursesList = document.getElementById('available-courses-list');

    if (courses.length === 0) {
        coursesList.innerHTML = `
            <div class="no-courses">
                <i class="fas fa-info-circle"></i>
                <p>Không có khóa học nào khả dụng</p>
            </div>
        `;
        return;
    }

    const coursesHtml = courses.map(course => `
        <div class="course-item ${course.available_slots <= 0 ? 'full' : ''}">
            <div class="course-info">
                <h4>${course.class_name}</h4>
                <p><i class="fas fa-users"></i> ${course.enrolled_students}/${course.max_students} học viên</p>
                <p><i class="fas fa-calendar"></i> ${formatSchedule(course)}</p>
                <p><i class="fas fa-money-bill"></i> ${formatCurrency(course.price_per_session)}/buổi</p>
            </div>
            <button 
                class="btn-enroll" 
                onclick="enrollStudent(${studentId}, ${course.id})"
                ${course.available_slots <= 0 ? 'disabled' : ''}
            >
                ${course.available_slots <= 0 ? 'Lớp đã đầy' : 'Thêm vào lớp'}
            </button>
        </div>
    `).join('');

    coursesList.innerHTML = `
        <div class="courses-grid">
            ${coursesHtml}
        </div>
    `;
}

function enrollStudent(studentId, courseId) {
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    fetch('/webapp/api/admin/enroll-student', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            student_id: studentId,
            course_id: courseId
        })
    })
        .then(response => {
            if (!response.ok) {
<<<<<<< Updated upstream
                throw new Error(`Đã đăng ký lớp này: ${response.status}`);
=======
                throw new Error(`Lớp đã đăng ký: ${response.status}`);
>>>>>>> Stashed changes
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Thêm học viên vào lớp thành công!', 'success');
                closeAddStudentToCourseModal();
                // Refresh student details if needed
                viewStudent(studentId);
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
            button.disabled = false;
            button.innerHTML = originalText;
        });
}

function closeAddStudentToCourseModal() {
    const modal = document.getElementById('add-student-course-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 300);
}

// View student details
function viewStudent(studentId) {
    const modal = document.getElementById('student-detail-modal');
    const content = document.getElementById('student-detail-content');

    if (!modal || !content) return;

    // Show loading state
    content.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin học viên...</p>
        </div>
    `;

    // Show modal
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Add show class for animation
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);

    // Fetch student details
    fetch(`/webapp/api/admin/student-details?id=${studentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.student) {
                displayStudentDetails(data.student);
            } else {
                throw new Error(data.message || 'Failed to load student details');
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="error-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Có lỗi xảy ra</h3>
                    <p>${error.message}</p>
                </div>
            `;
        });
}

// Display student details in modal
function displayStudentDetails(student) {
    const content = document.getElementById('student-detail-content');

    content.innerHTML = `
        <div class="student-profile">
            <div class="student-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h2>${student.full_name}</h2>
            <span class="student-status ${student.is_active ? 'active' : 'inactive'}">
                <i class="fas fa-circle"></i>
                ${student.is_active ? 'Đang học' : 'Ngừng học'}
            </span>
        </div>

        <div class="info-section">
            <h3>Thông tin cơ bản</h3>
            <div class="info-grid">
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span class="label">Email:</span>
                    ${student.email}
                </div>
                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <span class="label">Số điện thoại:</span>
                    ${student.phone || 'Chưa cập nhật'}
                </div>
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="label">Ngày tham gia:</span>
                    ${formatDate(student.created_at)}
                </div>
            </div>
        </div>

        <div class="info-section">
            <h3>Khóa học đang theo học</h3>
            ${student.enrollments && student.enrollments.length > 0 ? `
                <div class="enrollments-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tên khóa học</th>
                                <th>Ngày bắt đầu</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${student.enrollments.map(enrollment => `
                                <tr>
                                    <td>${enrollment.class_name}</td>
                                    <td>${formatDate(enrollment.enrollment_date)}</td>
                                    <td>
                                        <span class="status ${enrollment.enrollment_status}">
                                            ${enrollment.enrollment_status === 'active' ? 'Đang học' : 'Hoàn thành'}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-icon btn-delete" 
                                                onclick="removeFromCourse(${student.id}, ${enrollment.class_id})"
                                                title="Xóa khỏi khóa học">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : `
                <div class="no-enrollments">
                    <i class="fas fa-info-circle"></i>
                    <p>Học viên chưa tham gia khóa học nào</p>
                </div>
            `}
        </div>
    `;
}

// Close student detail modal
function closeStudentDetailModal() {
    const modal = document.getElementById('student-detail-modal');
    if (!modal) return;

    modal.classList.remove('show');

    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 300);
}

function removeFromCourse(studentId, courseId) {
    if (!confirm('Bạn có chắc chắn muốn xóa học viên này khỏi khóa học không?')) {
        return;
    }

    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;

    // Parse IDs to integers
    studentId = parseInt(studentId);
    courseId = parseInt(courseId);

    // Log data being sent
    console.log('Removing student from course:', { student_id: studentId, course_id: courseId });

    fetch('/webapp/api/admin/remove-from-course', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            student_id: studentId,
            course_id: courseId
        })
    })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.message || 'Đã xóa học viên khỏi khóa học thành công', 'success');
                viewStudent(studentId); // Refresh student details
            } else {
                throw new Error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('Lỗi: ' + error.message, 'error');
        })
        .finally(() => {
            button.innerHTML = originalHTML;
            button.disabled = false;
        });
}

function closeCourse(courseId, event) {
    // Make event parameter optional and add safety checks
    if (event) {
        event.stopPropagation();
    }

    if (!confirm('Bạn có chắc chắn muốn đóng khóa học này không?')) {
        return;
    }

    console.log('Closing course:', courseId);

    // Find the button that triggered this action
    const button = event ? event.target : document.querySelector(`button[onclick*="closeCourse(${courseId})"]`);
    let originalText = 'Đóng';

    if (button) {
        originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đóng...';
        button.disabled = true;
    }

    fetch('/webapp/api/admin/close-course', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            course_id: courseId
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Đóng khóa học thành công!', 'success');
                loadCourses(); // Refresh courses list
            } else {
                throw new Error(data.message || 'Không thể đóng khóa học');
            }
        })
        .catch(error => {
            console.error('Error closing course:', error);
            showMessage('Lỗi khi đóng khóa học: ' + error.message, 'error');
        })
        .finally(() => {
            if (button) {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
}

function reopenCourse(courseId, event) {
    // Make event parameter optional and add safety checks
    if (event) {
        event.stopPropagation();
    }

    if (!confirm('Bạn có chắc chắn muốn mở lại khóa học này không?')) {
        return;
    }

    console.log('Reopening course:', courseId);

    // Find the button that triggered this action
    const button = event ? event.target : document.querySelector(`button[onclick*="reopenCourse(${courseId})"]`);
    let originalText = 'Mở lại';

    if (button) {
        originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang mở...';
        button.disabled = true;
    }

    fetch('/webapp/api/admin/reopen-course', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            course_id: courseId
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage('Mở lại khóa học thành công!', 'success');
                loadCourses(); // Refresh courses list
            } else {
                throw new Error(data.message || 'Không thể mở lại khóa học');
            }
        })
        .catch(error => {
            console.error('Error reopening course:', error);
            showMessage('Lỗi khi mở lại khóa học: ' + error.message, 'error');
        })
        .finally(() => {
            if (button) {
                button.innerHTML = originalText;
                button.disabled = false;
            }
        });
}

function searchCourses() {
    const searchTerm = document.getElementById('course-search').value.toLowerCase();

    if (!allCourses || allCourses.length === 0) {
        return;
    }

    let coursesToFilter = allCourses;

    // Apply year filter first if it's set
    const yearFilter = document.getElementById('year-filter').value;
    if (yearFilter) {
        coursesToFilter = allCourses.filter(course => {
            if (!course.start_date) return false;
            const courseYear = new Date(course.start_date).getFullYear();
            return courseYear.toString() === yearFilter;
        });
    }

    // Then apply search filter
    if (searchTerm.trim() === '') {
        filteredCourses = coursesToFilter;
    } else {
        filteredCourses = coursesToFilter.filter(course => {
            const searchFields = [
                course.class_name || '',
                course.subject || '',
                course.class_level || '',
                course.tutor_name || '',
                course.description || ''
            ];

            return searchFields.some(field =>
                field.toLowerCase().includes(searchTerm)
            );
        });
    }

    displayCourses(filteredCourses);
    updateCourseStats(filteredCourses);
}

function filterCoursesByYear(year) {
    console.log('Filtering courses by year:', year);

    if (!allCourses || allCourses.length === 0) {
        filteredCourses = [];
        displayCourses(filteredCourses);
        updateCourseStats(filteredCourses);
        return;
    }

    let coursesToFilter = allCourses;

    // Apply year filter
    if (!year || year === '') {
        coursesToFilter = allCourses;
    } else {
        coursesToFilter = allCourses.filter(course => {
            if (!course.start_date) return false;
            const courseYear = new Date(course.start_date).getFullYear();
            return courseYear.toString() === year.toString();
        });
    }

    // Apply search filter if there's a search term
    const searchTerm = document.getElementById('course-search').value.toLowerCase();
    if (searchTerm.trim() !== '') {
        filteredCourses = coursesToFilter.filter(course => {
            const searchFields = [
                course.class_name || '',
                course.subject || '',
                course.class_level || '',
                course.tutor_name || '',
                course.description || ''
            ];

            return searchFields.some(field =>
                field.toLowerCase().includes(searchTerm)
            );
        });
    } else {
        filteredCourses = coursesToFilter;
    }

    displayCourses(filteredCourses);
    updateCourseStats(filteredCourses);
}

// Thêm các functions sau vào Admin.js

// ===========================================
// PARENT MANAGEMENT FUNCTIONS
// ===========================================

// Thêm vào function loadParents

function loadParents() {
    console.log('🔄 Starting loadParents function...');

    const parentsTableBody = document.getElementById('parents-table-body');
    if (!parentsTableBody) {
        console.error('Parents table body not found');
        return;
    }

    // Show loading state
    const loadingElement = document.getElementById('parents-loading');
    const noParentsElement = document.getElementById('no-parents');

    if (loadingElement) loadingElement.style.display = 'block';
    if (noParentsElement) noParentsElement.style.display = 'none';
    parentsTableBody.innerHTML = '';

    fetch('/webapp/api/admin/get-parents', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Parents data received:', data);

            // Debug: Log is_active values from API
            if (data.success && data.parents) {
                data.parents.forEach(parent => {
                    console.log(`API Response - Parent ${parent.id}: is_active = ${parent.is_active} (${typeof parent.is_active})`);
                });
            }

            if (loadingElement) loadingElement.style.display = 'none';

            if (data.success && data.parents && data.parents.length > 0) {
                displayParents(data.parents);
            } else {
                showNoParents();
            }
        })
        .catch(error => {
            console.error('Error loading parents:', error);
            if (loadingElement) loadingElement.style.display = 'none';
            showErrorParents(error.message);
        });
}

function displayParents(parents) {
    const parentsTableBody = document.getElementById('parents-table-body');
    if (!parentsTableBody) return;

    parentsTableBody.innerHTML = parents.map(parent => {
        // Chuyển đổi is_active về boolean một cách rõ ràng
        const isActive = parent.is_active == 1 || parent.is_active === true || parent.is_active === '1';
        const statusClass = isActive ? 'active' : 'inactive';
        const statusText = isActive ? 'Hoạt động' : 'Không hoạt động';

        const createdDate = new Date(parent.created_at).toLocaleDateString('vi-VN');
        const totalPaid = formatCurrency(parent.total_paid || 0);

        // Debug log
        console.log(`Parent ${parent.id}: is_active = ${parent.is_active} (${typeof parent.is_active}), converted to ${isActive}`);

        return `
            <tr>
                <td>${parent.id}</td>
                <td>${parent.full_name}</td>
                <td>${parent.email}</td>
                <td>${parent.phone || 'Chưa có'}</td>
                <td>${parent.children_count || 0}</td>
                <td>${totalPaid}</td>
                <td>${createdDate}</td>
                <td><span class="status ${statusClass}">${statusText}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon btn-view" onclick="viewParent(${parent.id})" title="Xem chi tiết">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon btn-edit" onclick="editParent(${parent.id})" title="Chỉnh sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function showNoParents() {
    const parentsTableBody = document.getElementById('parents-table-body');
    const noParentsElement = document.getElementById('no-parents');

    if (parentsTableBody) parentsTableBody.innerHTML = '';
    if (noParentsElement) noParentsElement.style.display = 'block';
}

function showErrorParents(message) {
    const parentsTableBody = document.getElementById('parents-table-body');
    if (!parentsTableBody) return;

    parentsTableBody.innerHTML = `
        <tr>
            <td colspan="9" class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Lỗi tải dữ liệu: ${message}</p>
                <button onclick="loadParents()" class="btn-primary">Thử lại</button>
            </td>
        </tr>
    `;
}

function searchParents() {
    const searchTerm = document.getElementById('parent-search').value.toLowerCase();
    const tableRows = document.querySelectorAll('#parents-table-body tr');

    tableRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// ===========================================
// PARENT MODAL FUNCTIONS
// ===========================================

function showAddParentModal() {
    const modal = document.getElementById('add-parent-modal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Reset form
        const form = document.getElementById('add-parent-form');
        if (form) form.reset();
    }
}

function closeAddParentModal() {
    const modal = document.getElementById('add-parent-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function createParent(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    submitBtn.disabled = true;

    fetch('/webapp/api/admin/create-parent', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Tạo phụ huynh thành công!', 'success');
                closeAddParentModal();
                loadParents(); // Reload the parents list
            } else {
                showMessage(data.message || 'Có lỗi xảy ra khi tạo phụ huynh', 'error');
            }
        })
        .catch(error => {
            console.error('Error creating parent:', error);
            showMessage('Lỗi hệ thống khi tạo phụ huynh', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function viewParent(parentId) {
    fetch(`/webapp/api/admin/parent-details?parent_id=${parentId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.parent) {
                displayParentDetails(data.parent);
            } else {
                showMessage(data.message || 'Không thể tải thông tin phụ huynh', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading parent details:', error);
            showMessage('Lỗi khi tải thông tin phụ huynh', 'error');
        });
}


function displayParentDetails(parent) {
    const modal = document.getElementById('parent-detail-modal');
    const content = document.getElementById('parent-detail-content');

    if (!modal || !content) return;

    // Chuyển đổi is_active một cách rõ ràng
    const isActive = parent.is_active == 1 || parent.is_active === true || parent.is_active === '1';
    const statusText = isActive ? 'Hoạt động' : 'Không hoạt động';
    const statusClass = isActive ? 'active' : 'inactive';

    const createdDate = new Date(parent.created_at).toLocaleDateString('vi-VN');
    const totalPaid = formatCurrency(parent.total_paid || 0);

    // Debug log
    console.log(`Parent detail ${parent.id}: is_active = ${parent.is_active} (${typeof parent.is_active}), converted to ${isActive}`);

    content.innerHTML = `
        <div class="parent-detail">
            <div class="parent-profile">
                <div class="parent-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2>${parent.full_name}</h2>
                <span class="parent-status ${statusClass}">${statusText}</span>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <span class="label">Tên đăng nhập:</span>
                        <span>${parent.username}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span class="label">Email:</span>
                        <span>${parent.email}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span class="label">Số điện thoại:</span>
                        <span>${parent.phone || 'Chưa có'}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="label">Địa chỉ:</span>
                        <span>${parent.address || 'Chưa có'}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span class="label">Ngày tạo:</span>
                        <span>${createdDate}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-toggle-on"></i>
                        <span class="label">Trạng thái:</span>
                        <span class="status ${statusClass}">${statusText}</span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h3><i class="fas fa-chart-bar"></i> Thống kê</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-child"></i>
                        <span class="label">Số con:</span>
                        <span>${parent.children_count || 0}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-money-bill"></i>
                        <span class="label">Tổng thanh toán:</span>
                        <span>${totalPaid}</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-receipt"></i>
                        <span class="label">Số lần thanh toán:</span>
                        <span>${parent.payment_count || 0}</span>
                    </div>
                </div>
            </div>
            
            ${parent.children && parent.children.length > 0 ? `
            <div class="info-section">
                <h3><i class="fas fa-users"></i> Danh sách con (${parent.children.length})</h3>
                <div class="children-grid">
                    ${parent.children.map(child => `
                        <div class="child-item">
                            <div class="child-name">${child.full_name}</div>
                            <div class="child-info">
                                <span><i class="fas fa-envelope"></i> ${child.email}</span>
                                <span><i class="fas fa-phone"></i> ${child.phone || 'Chưa có'}</span>
                                <span><i class="fas fa-heart"></i> ${child.relationship_type === 'father' ? 'Cha' : child.relationship_type === 'mother' ? 'Mẹ' : 'Người giám hộ'}</span>
                                <span><i class="fas fa-book"></i> ${child.enrolled_classes} lớp</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : `
            <div class="info-section">
                <div class="no-children">
                    <i class="fas fa-child"></i>
                    <h3>Chưa có con nào được liên kết</h3>
                    <p>Phụ huynh này chưa có con nào trong hệ thống</p>
                </div>
            </div>
            `}
        </div>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeParentDetailModal() {
    const modal = document.getElementById('parent-detail-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function editParent(parentId) {
    fetch(`/webapp/api/admin/parent-details?parent_id=${parentId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.parent) {
                populateEditParentForm(data.parent);
            } else {
                showMessage(data.message || 'Không thể tải thông tin phụ huynh', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading parent for edit:', error);
            showMessage('Lỗi khi tải thông tin phụ huynh', 'error');
        });
}

// Sửa function populateEditParentForm

function populateEditParentForm(parent) {
    // Chuyển đổi is_active một cách rõ ràng
    const isActive = parent.is_active == 1 || parent.is_active === true || parent.is_active === '1';

    document.getElementById('edit-parent-id').value = parent.id;
    document.getElementById('edit-parent-fullname').value = parent.full_name;
    document.getElementById('edit-parent-email').value = parent.email;
    document.getElementById('edit-parent-phone').value = parent.phone || '';
    document.getElementById('edit-parent-address').value = parent.address || '';
    document.getElementById('edit-parent-status').value = isActive ? '1' : '0';

    // Debug log
    console.log(`Edit form populate - Parent ${parent.id}: is_active = ${parent.is_active}, setting select to ${isActive ? '1' : '0'}`);

    const modal = document.getElementById('edit-parent-modal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}
function updateParent(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    // Debug logging
    console.log('=== Update Parent Debug ===');
    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key, ':', value);
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
    submitBtn.disabled = true;

    fetch('/webapp/api/admin/update-parent', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Check if response has content
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Response is not JSON');
            }

            return response.text(); // Get as text first
        })
        .then(text => {
            console.log('Raw response text:', text);

            // Try to parse JSON
            if (!text.trim()) {
                throw new Error('Empty response from server');
            }

            return JSON.parse(text);
        })
        .then(data => {
            console.log('Parsed response data:', data);
            if (data.success) {
                showMessage('Cập nhật phụ huynh thành công!', 'success');
                closeEditParentModal();
                loadParents(); // Reload the parents list
            } else {
                showMessage(data.message || 'Có lỗi xảy ra khi cập nhật phụ huynh', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating parent:', error);
            showMessage('Lỗi hệ thống khi cập nhật phụ huynh: ' + error.message, 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
}

function closeEditParentModal() {
    const modal = document.getElementById('edit-parent-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Add parent management to the initialization
document.addEventListener('DOMContentLoaded', function () {
    // ... existing initialization code ...

    // Add navigation event listener for parents
    const parentsNavLink = document.querySelector('[href="#manage_parent"]');
    if (parentsNavLink) {
        parentsNavLink.addEventListener('click', function () {
            setTimeout(() => {
                loadParents();
            }, 100);
        });
    }
});

// Show link student modal
function showLinkStudentModal() {
    currentParentId = document.getElementById('edit-parent-id').value;
    if (!currentParentId) {
        showMessage('Vui lòng chọn phụ huynh trước', 'error');
        return;
    }

    const modal = document.getElementById('link-student-modal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';

        // Reset form
        resetLinkStudentForm();

        // Focus on search input
        setTimeout(() => {
            const searchInput = document.getElementById('student-search-input');
            if (searchInput) searchInput.focus();
        }, 100);
    }
}

// Close link student modal
function closeLinkStudentModal() {
    const modal = document.getElementById('link-student-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        resetLinkStudentForm();
    }
}

// Reset link student form
function resetLinkStudentForm() {
    selectedStudentId = null;
    document.getElementById('student-search-input').value = '';
    document.getElementById('students-search-results').innerHTML = '';
    document.getElementById('relationship-section').style.display = 'none';
    document.getElementById('relationship-type').value = '';
    document.getElementById('is-primary-parent').checked = false;
    document.getElementById('link-student-btn').disabled = true;

    // Hide loading and no results
    document.getElementById('students-loading').style.display = 'none';
    document.getElementById('no-students-found').style.display = 'none';
}

// Search students for linking
function searchStudentsForLink() {
    const searchTerm = document.getElementById('student-search-input').value.trim();
    const resultsContainer = document.getElementById('students-search-results');
    const loadingElement = document.getElementById('students-loading');
    const noResultsElement = document.getElementById('no-students-found');

    // Hide relationship section when searching
    document.getElementById('relationship-section').style.display = 'none';
    selectedStudentId = null;
    document.getElementById('link-student-btn').disabled = true;

    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        loadingElement.style.display = 'none';
        noResultsElement.style.display = 'none';
        return;
    }

    // Show loading
    loadingElement.style.display = 'block';
    noResultsElement.style.display = 'none';
    resultsContainer.innerHTML = '';

    fetch('/webapp/api/admin/search-students', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `search_term=${encodeURIComponent(searchTerm)}&parent_id=${currentParentId}`
    })
        .then(response => response.json())
        .then(data => {
            loadingElement.style.display = 'none';

            if (data.success && data.students && data.students.length > 0) {
                displayStudentsForLink(data.students);
                noResultsElement.style.display = 'none';
            } else {
                resultsContainer.innerHTML = '';
                noResultsElement.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error searching students:', error);
            loadingElement.style.display = 'none';
            noResultsElement.style.display = 'block';
            showMessage('Lỗi khi tìm kiếm học viên', 'error');
        });
}

// Display students for linking
function displayStudentsForLink(students) {
    const resultsContainer = document.getElementById('students-search-results');

    resultsContainer.innerHTML = students.map(student => {
        const initials = student.full_name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .substring(0, 2)
            .toUpperCase();

        const studentCode = `HV${String(student.id).padStart(4, '0')}`;

        return `
            <div class="student-item" onclick="selectStudentForLink(${student.id}, '${student.full_name}')">
                <div class="student-avatar">${initials}</div>
                <div class="student-info">
                    <div class="student-name">${student.full_name}</div>
                    <div class="student-details">
                        <span class="student-id">${studentCode}</span> • 
                        ${student.email} • 
                        ${student.phone || 'Chưa có SĐT'}
                        ${student.already_linked ? ' • <span style="color: #dc3545;">Đã liên kết</span>' : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Select student for linking
function selectStudentForLink(studentId, studentName) {
    // Remove previous selection
    document.querySelectorAll('.student-item').forEach(item => {
        item.classList.remove('selected');
    });

    // Add selection to clicked item
    event.currentTarget.classList.add('selected');

    selectedStudentId = studentId;

    // Show relationship section
    document.getElementById('relationship-section').style.display = 'block';

    // Update relationship section header
    const relationshipSection = document.getElementById('relationship-section');
    const existingHeader = relationshipSection.querySelector('h4');
    if (existingHeader) {
        existingHeader.textContent = `Liên kết với học viên: ${studentName}`;
    }

    // Enable link button when relationship is selected
    updateLinkButtonState();
}

// Update link button state
function updateLinkButtonState() {
    const relationshipType = document.getElementById('relationship-type').value;
    const linkBtn = document.getElementById('link-student-btn');

    if (selectedStudentId && relationshipType) {
        linkBtn.disabled = false;
    } else {
        linkBtn.disabled = true;
    }
}

// Add event listener for relationship type change
document.addEventListener('DOMContentLoaded', function () {
    const relationshipSelect = document.getElementById('relationship-type');
    if (relationshipSelect) {
        relationshipSelect.addEventListener('change', updateLinkButtonState);
    }
});

// Link student to parent
function linkStudentToParent() {
    if (!selectedStudentId || !currentParentId) {
        showMessage('Vui lòng chọn học viên và phụ huynh', 'error');
        return;
    }

    const relationshipType = document.getElementById('relationship-type').value;
    const isPrimary = document.getElementById('is-primary-parent').checked;

    if (!relationshipType) {
        showMessage('Vui lòng chọn mối quan hệ', 'error');
        return;
    }

    const linkBtn = document.getElementById('link-student-btn');
    const originalText = linkBtn.innerHTML;
    linkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang liên kết...';
    linkBtn.disabled = true;

    const formData = new FormData();
    formData.append('parent_id', currentParentId);
    formData.append('student_id', selectedStudentId);
    formData.append('relationship_type', relationshipType);
    formData.append('is_primary', isPrimary ? '1' : '0');

    fetch('/webapp/api/admin/link-parent-student', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('Liên kết học viên thành công!', 'success');
                closeLinkStudentModal();

                // Reload parent details if parent detail modal is open
                if (document.getElementById('parent-detail-modal').style.display === 'block') {
                    viewParent(currentParentId);
                }

                // Reload parents list
                loadParents();
            } else {
                showMessage(data.message || 'Có lỗi xảy ra khi liên kết học viên', 'error');
            }
        })
        .catch(error => {
            console.error('Error linking student:', error);
            showMessage('Lỗi hệ thống khi liên kết học viên', 'error');
        })
        .finally(() => {
            linkBtn.innerHTML = originalText;
            linkBtn.disabled = false;
        });
>>>>>>> Stashed changes
}