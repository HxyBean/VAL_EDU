// Global variables
let studentRegistrationChart = null;
let classDistributionChart = null;
let allCourses = [];
let filteredCourses = [];
let tutors = [];

// Initialize everything when DOM loads
document.addEventListener('DOMContentLoaded', function() {
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
        coursesNavLink.addEventListener('click', function() {
            setTimeout(loadCourses, 100);
        });
    }
});

// Global event listeners
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAllModals();
    }
});

window.addEventListener('resize', function() {
    handleWindowResize();
    resizeCharts();
});

// Window click handler for modal backdrop
window.onclick = function(event) {
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
    const level = course.class_level ? course.class_level.charAt(0).toUpperCase() : 'X';
    
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
                        label: function(context) {
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
        startDateInput.addEventListener('change', function() {
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
    modal.addEventListener('click', function(e) {
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
    showMessage('Chức năng xem chi tiết khóa học đang được phát triển', 'info');
}

function closeCourseDetailModal() {
    const modal = document.getElementById('course-detail-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ===========================================
// COURSE ACTION FUNCTIONS
// ===========================================

function editCourse(courseId) {
    showMessage('Chức năng chỉnh sửa khóa học đang được phát triển', 'info');
}

function closeCourse(courseId) {
    if (!confirm('Bạn có chắc chắn muốn đóng khóa học này? Hành động này không thể hoàn tác.')) {
        return;
    }
    
    fetch('/webapp/api/admin/close-course', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ course_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Đóng khóa học thành công!', 'success');
            loadCourses();
        } else {
            showMessage(data.message || 'Lỗi khi đóng khóa học', 'error');
        }
    })
    .catch(error => {
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    });
}

function reopenCourse(courseId) {
    if (!confirm('Bạn có chắc chắn muốn mở lại khóa học này?')) {
        return;
    }
    
    fetch('/webapp/api/admin/reopen-course', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ course_id: courseId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('Mở lại khóa học thành công!', 'success');
            loadCourses();
        } else {
            showMessage(data.message || 'Lỗi khi mở lại khóa học', 'error');
        }
    })
    .catch(error => {
        showMessage('Lỗi kết nối. Vui lòng thử lại!', 'error');
    });
}

function viewCourseStudents(courseId) {
    showMessage('Chức năng xem danh sách học viên đang được phát triển', 'info');
}

// ===========================================
// SEARCH AND FILTER FUNCTIONS
// ===========================================

function searchCourses() {
    const searchTerm = document.getElementById('course-search')?.value.toLowerCase() || '';
    const yearFilter = document.getElementById('year-filter')?.value || '';
    
    filteredCourses = allCourses.filter(course => {
        const matchesSearch = !searchTerm || 
            generateCourseCode(course).toLowerCase().includes(searchTerm) ||
            (course.class_level && course.class_level.toLowerCase().includes(searchTerm)) ||
            (course.tutor_name && course.tutor_name.toLowerCase().includes(searchTerm));
            
        const matchesYear = !yearFilter || 
            (course.class_year && course.class_year.toString() === yearFilter);
            
        return matchesSearch && matchesYear;
    });
    
    displayCourses(filteredCourses);
}

function filterCoursesByYear() {
    searchCourses(); // Reuse the search function
}

