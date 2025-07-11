// Global variables
let currentChildId = null;

// View child detail function
function viewChildDetail(childId) {
    currentChildId = childId;

    // Show loading
    document.getElementById('child-detail-content').innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải thông tin chi tiết...</p>
        </div>
    `;

    // Fetch child details
    fetch(`/webapp/api/parent/child-details?child_id=${childId}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.child) {
                displayChildDetails(data.child);
                showSection('child_detail');
            } else {
                showMessage(data.message || 'Không thể tải thông tin chi tiết', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading child details:', error);
            showMessage('Lỗi khi tải thông tin chi tiết', 'error');
        });
}

// Display child details
function displayChildDetails(child) {
    const content = document.getElementById('child-detail-content');

    // Update title
    document.getElementById('child-detail-title').textContent = `Chi Tiết - ${child.full_name}`;

    // Calculate total stats
    const totalSessions = child.academic_progress.total_sessions || 0;
    const attendedSessions = child.academic_progress.attended_sessions || 0;
    const absentSessions = child.academic_progress.absent_sessions || 0;
    const attendanceRate = child.academic_progress.attendance_rate || 0;

    content.innerHTML = `
        <div class="child-detail-container">
            <!-- Child Info Card -->
            <div class="child-info-card">
                <div class="child-profile">
                    <div class="child-avatar-large">
                        ${getChildInitials(child.full_name)}
                    </div>
                    <div class="child-basic-info">
                        <h3>${child.full_name}</h3>
                        <p><i class="fas fa-envelope"></i> ${child.email}</p>
                        <p><i class="fas fa-phone"></i> ${child.phone || 'Chưa có'}</p>
                        <p><i class="fas fa-calendar"></i> Đăng ký: ${formatDate(child.registration_date)}</p>
                        <span class="relationship-badge">${getRelationshipText(child.relationship_type)}</span>
                        ${child.is_primary ? '<span class="primary-badge">Phụ huynh chính</span>' : ''}
                    </div>
                </div>
            </div>
            
            <!-- Academic Progress -->
            <div class="progress-summary">
                <h4><i class="fas fa-chart-line"></i> Tổng Quan Học Tập</h4>
                <div class="progress-stats">
                    <div class="progress-stat">
                        <span class="stat-number">${child.classes.length}</span>
                        <span class="stat-label">Lớp học</span>
                    </div>
                    <div class="progress-stat">
                        <span class="stat-number">${totalSessions}</span>
                        <span class="stat-label">Tổng buổi học</span>
                    </div>
                    <div class="progress-stat">
                        <span class="stat-number">${attendedSessions}</span>
                        <span class="stat-label">Có mặt</span>
                    </div>
                    <div class="progress-stat">
                        <span class="stat-number">${absentSessions}</span>
                        <span class="stat-label">Vắng mặt</span>
                    </div>
                    <div class="progress-stat">
                        <span class="stat-number">${attendanceRate}%</span>
                        <span class="stat-label">Tỷ lệ tham gia</span>
                    </div>
                </div>
            </div>
            
            <!-- Classes Section -->
            <div class="child-classes-section">
                <h4><i class="fas fa-book-open"></i> Danh Sách Khóa Học</h4>
                ${child.classes.length > 0 ? generateClassesHTML(child.classes) : '<p class="no-data-text">Chưa đăng ký khóa học nào</p>'}
            </div>
            
            <!-- Attendance History Section -->
            <div class="attendance-history-section">
                <div class="section-header">
                    <h4><i class="fas fa-calendar-check"></i> Lịch Sử Điểm Danh</h4>
                    <div class="attendance-filters">
                        <select id="class-filter" onchange="filterAttendanceByClass()">
                            <option value="">Tất cả lớp học</option>
                            ${child.classes.map(c => `<option value="${c.id}">${c.class_name} - ${c.subject}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div id="attendance-history-content">
                    ${generateAttendanceHistoryHTML(child.attendance_history)}
                </div>
            </div>
            
            <!-- Payment History Section -->
            <div class="payment-history-section">
                <h4><i class="fas fa-credit-card"></i> Lịch Sử Thanh Toán</h4>
                ${child.payment_history.length > 0 ? generatePaymentHistoryHTML(child.payment_history) : '<p class="no-data-text">Chưa có lịch sử thanh toán</p>'}
            </div>
        </div>
    `;
}

// Generate classes HTML
function generateClassesHTML(classes) {
    return `
        <div class="classes-grid">
            ${classes.map(classInfo => `
                <div class="class-detail-card">
                    <div class="class-header">
                        <h5>${classInfo.class_name}</h5>
                        <span class="class-status ${classInfo.status}">${getStatusText(classInfo.status)}</span>
                    </div>
                    <div class="class-info">
                        <p><i class="fas fa-book"></i> ${classInfo.subject}</p>
                        <p><i class="fas fa-layer-group"></i> ${classInfo.class_level}</p>
                        <p><i class="fas fa-user-tie"></i> ${classInfo.tutor_name || 'Chưa phân công'}</p>
                        <p><i class="fas fa-calendar"></i> ${formatSchedule(classInfo)}</p>
                        <p><i class="fas fa-calendar-plus"></i> Bắt đầu: ${formatDate(classInfo.start_date)}</p>
                    </div>
                    <div class="class-progress">
                        <div class="progress-item">
                            <span>Buổi học: ${classInfo.total_sessions || 0}</span>
                        </div>
                        <div class="progress-item">
                            <span>Đã tham gia: ${classInfo.attended_sessions || 0}</span>
                        </div>
                        <div class="progress-item">
                            <span>Vắng mặt: ${classInfo.absent_sessions || 0}</span>
                        </div>
                        <div class="progress-item">
                            <span>Tỷ lệ: ${classInfo.attendance_rate || 0}%</span>
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

// Generate attendance history HTML
function generateAttendanceHistoryHTML(attendance) {
    if (!attendance || attendance.length === 0) {
        return '<p class="no-data-text">Chưa có lịch sử điểm danh</p>';
    }

    return `
        <div class="attendance-table-container">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Lớp học</th>
                        <th>Chủ đề</th>
                        <th>Giờ học</th>
                        <th>Thời lượng</th>
                        <th>Trạng thái</th>
                        <th>Giáo viên</th>
                    </tr>
                </thead>
                <tbody>
                    ${attendance.map(record => `
                        <tr>
                            <td>${formatDate(record.session_date)}</td>
                            <td>
                                <div class="class-info-cell">
                                    <strong>${record.class_name}</strong>
                                    <small>${record.subject} - ${record.class_level}</small>
                                </div>
                            </td>
                            <td>${record.topic || 'Không có chủ đề'}</td>
                            <td>${record.session_time ? record.session_time.substring(0, 5) : ''}</td>
                            <td>${record.duration_minutes || 0} phút</td>
                            <td>
                                <span class="attendance-status ${record.status}">
                                    ${getAttendanceStatusText(record.status)}
                                </span>
                            </td>
                            <td>${record.tutor_name || 'Chưa phân công'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Generate payment history HTML
function generatePaymentHistoryHTML(payments) {
    return `
        <div class="payment-table-container">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th>Ngày thanh toán</th>
                        <th>Lớp học</th>
                        <th>Số tiền</th>
                        <th>Giảm giá</th>
                        <th>Thành tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    ${payments.map(payment => `
                        <tr>
                            <td>${formatDate(payment.payment_date)}</td>
                            <td>
                                <div class="class-info-cell">
                                    <strong>${payment.class_name}</strong>
                                    <small>${payment.subject}</small>
                                </div>
                            </td>
                            <td>${formatCurrency(payment.amount)}</td>
                            <td>${formatCurrency(payment.discount_amount || 0)}</td>
                            <td><strong>${formatCurrency(payment.final_amount)}</strong></td>
                            <td>${getPaymentMethodText(payment.payment_method)}</td>
                            <td>
                                <span class="payment-status ${payment.status}">
                                    ${getPaymentStatusText(payment.status)}
                                </span>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Filter attendance by class
function filterAttendanceByClass() {
    const classId = document.getElementById('class-filter').value;

    if (!currentChildId) return;

    // Show loading
    document.getElementById('attendance-history-content').innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Đang tải lịch sử điểm danh...</p>
        </div>
    `;

    const url = classId
        ? `/webapp/api/parent/child-attendance?child_id=${currentChildId}&class_id=${classId}`
        : `/webapp/api/parent/child-attendance?child_id=${currentChildId}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('attendance-history-content').innerHTML =
                    generateAttendanceHistoryHTML(data.attendance);
            } else {
                showMessage(data.message || 'Không thể tải lịch sử điểm danh', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading attendance:', error);
            showMessage('Lỗi khi tải lịch sử điểm danh', 'error');
        });
}

// Go back to children list
function goBackToChildren() {
    currentChildId = null;
    showSection('my_children');
}

// Helper functions
function getChildInitials(name) {
    return name.split(' ')
        .map(word => word.charAt(0))
        .join('')
        .substring(0, 2)
        .toUpperCase();
}

function getRelationshipText(relationship) {
    const map = {
        'father': 'Con trai/gái',
        'mother': 'Con trai/gái',
        'guardian': 'Người được giám hộ'
    };
    return map[relationship] || 'Con trai/gái';
}

function getStatusText(status) {
    const map = {
        'active': 'Đang học',
        'completed': 'Hoàn thành',
        'closed': 'Đã đóng'
    };
    return map[status] || status;
}

function getAttendanceStatusText(status) {
    const map = {
        'present': 'Có mặt',
        'absent': 'Vắng mặt',
        'late': 'Đi muộn'
    };
    return map[status] || status;
}

function getPaymentMethodText(method) {
    const map = {
        'cash': 'Tiền mặt',
        'bank_transfer': 'Chuyển khoản',
        'credit_card': 'Thẻ tín dụng'
    };
    return map[method] || method;
}

function getPaymentStatusText(status) {
    const map = {
        'completed': 'Hoàn thành',
        'pending': 'Chờ xử lý',
        'failed': 'Thất bại'
    };
    return map[status] || status;
}

function formatSchedule(classInfo) {
    const days = classInfo.schedule_days || '';
    const time = classInfo.schedule_time ? classInfo.schedule_time.substring(0, 5) : '';
    const duration = classInfo.schedule_duration || 0;

    return `${days} ${time} (${duration}p)`;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

function formatCurrency(amount) {
    if (!amount || isNaN(amount)) return '0₫';
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0
    }).format(amount);
}

// Show message function
function showMessage(message, type = 'info') {
    // Remove existing messages
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

    document.body.appendChild(messageDiv);

    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

// Show section function
function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    // Show target section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // Update navbar
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });

    const navLink = document.querySelector(`[href="#${sectionId}"]`);
    if (navLink) {
        navLink.classList.add('active');
    }
}

// QR Payment functions
function showQRPayment(paymentId, studentName, className, amount, paymentMethod) {
    console.log('Showing QR payment for:', paymentId);

    // Store payment ID for confirmation
    document.getElementById('qr-payment-modal').dataset.paymentId = paymentId;

    // Populate payment information
    document.getElementById('qr-student-name').textContent = studentName;
    document.getElementById('qr-class-name').textContent = className;
    document.getElementById('qr-amount').textContent = formatCurrency(amount);
    document.getElementById('qr-method').textContent = getPaymentMethodText(paymentMethod);

    // Generate payment content
    const paymentContent = `VALEDU ${paymentId} ${studentName.replace(/\s+/g, '')}`;
    document.getElementById('qr-content').textContent = paymentContent;

    // Reset status
    document.getElementById('payment-status').style.display = 'none';
    document.getElementById('confirm-payment-btn').disabled = false;

    // Show modal
    document.getElementById('qr-payment-modal').style.display = 'block';
}

function closeQRPaymentModal() {
    document.getElementById('qr-payment-modal').style.display = 'none';
}

function simulateQRPayment() {
    const modal = document.getElementById('qr-payment-modal');
    const paymentId = modal.dataset.paymentId;

    if (!paymentId) {
        showMessage('Lỗi: Không tìm thấy thông tin thanh toán', 'error');
        return;
    }

    // Show processing status
    const statusDiv = document.getElementById('payment-status');
    const confirmBtn = document.getElementById('confirm-payment-btn');

    statusDiv.style.display = 'block';
    statusDiv.innerHTML = `
        <i class="fas fa-spinner fa-spin"></i>
        <p>Đang xử lý thanh toán...</p>
    `;
    confirmBtn.disabled = true;

    // Simulate payment processing delay
    setTimeout(() => {
        // Call API to confirm payment
        const formData = new FormData();
        formData.append('payment_id', paymentId);
        
        fetch('/webapp/api/parent/process-payment', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                statusDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <p>Thanh toán thành công!</p>
                `;

                setTimeout(() => {
                    closeQRPaymentModal();
                    showMessage('Thanh toán đã được xác nhận thành công!', 'success');
                    
                    // Update UI with new data
                    if (data.updated_stats) {
                        updatePaymentSummary(data.updated_stats);
                    }
                    
                    if (data.recent_payments) {
                        updatePaymentHistory(data.recent_payments);
                    }
                    
                    // Refresh bills to remove the paid bill
                    loadBills();
                }, 2000);
            } else {
                statusDiv.innerHTML = `
                    <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                    <p style="color: #dc3545;">Thanh toán thất bại: ${data.message}</p>
                `;
                confirmBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            statusDiv.innerHTML = `
                <i class="fas fa-times-circle" style="color: #dc3545;"></i>
                <p style="color: #dc3545;">Lỗi kết nối. Vui lòng thử lại.</p>
            `;
            confirmBtn.disabled = false;
        });
    }, 2000); // 2 second delay to simulate processing
}

// Update payment summary with new stats
function updatePaymentSummary(stats) {
    const totalPaidElement = document.querySelector('.summary-card.total .amount');
    const pendingAmountElement = document.getElementById('pending-amount');
    
    if (totalPaidElement && stats.total_paid !== undefined) {
        totalPaidElement.textContent = formatCurrency(stats.total_paid);
    }
    
    if (pendingAmountElement && stats.pending_payments !== undefined) {
        pendingAmountElement.textContent = formatCurrency(stats.pending_payments);
    }
}

// Update payment history table with recent payments
function updatePaymentHistory(recentPayments) {
    const paymentTableBody = document.querySelector('.payment-table tbody');
    if (!paymentTableBody || !recentPayments || recentPayments.length === 0) {
        return;
    }
    
    // Add new payments to the top of the table
    const newRows = recentPayments.map(payment => `
        <tr class="new-payment">
            <td>${formatDate(payment.payment_date)}</td>
            <td>${payment.student_name}</td>
            <td>${payment.class_name}</td>
            <td>${formatCurrency(payment.final_amount || payment.amount)}</td>
            <td>${getPaymentMethodText(payment.payment_method)}</td>
            <td>
                <span class="status completed">
                    <i class="fas fa-check-circle"></i> Hoàn thành
                </span>
            </td>
        </tr>
    `).join('');
    
    // Insert new rows at the beginning
    paymentTableBody.insertAdjacentHTML('afterbegin', newRows);
    
    // Highlight new payments briefly
    setTimeout(() => {
        const newPaymentRows = document.querySelectorAll('.new-payment');
        newPaymentRows.forEach(row => {
            row.style.backgroundColor = '#d4edda';
            setTimeout(() => {
                row.style.backgroundColor = '';
                row.classList.remove('new-payment');
            }, 3000);
        });
    }, 100);
}

// Update pending amount in summary
function updatePendingAmount() {
    fetch('/webapp/api/parent/bills')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pendingBills = data.bills.filter(bill => bill.status === 'pending' || bill.is_virtual);
                const totalPending = pendingBills.reduce((sum, bill) => sum + (bill.final_amount || bill.amount), 0);
                
                const pendingAmountElement = document.getElementById('pending-amount');
                if (pendingAmountElement) {
                    pendingAmountElement.textContent = formatCurrency(totalPending);
                }
            }
        })
        .catch(error => {
            console.error('Error updating pending amount:', error);
        });
}

function getPaymentMethodText(method) {
    const methods = {
        'cash': 'Tiền mặt',
        'bank_transfer': 'Chuyển khoản',
        'credit_card': 'Thẻ tín dụng'
    };
    return methods[method] || method;
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Close modal when clicking outside
window.addEventListener('click', function (event) {
    const modal = document.getElementById('qr-payment-modal');
    if (event.target === modal) {
        closeQRPaymentModal();
    }
});

// Load bills on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load bills when payment section is visible
    loadBills();
});

// Load pending bills
function loadBills() {
    const billsContainer = document.getElementById('bills-container');
    const loading = document.getElementById('bills-loading');
    
    if (loading) loading.style.display = 'block';
    
    fetch('/webapp/api/parent/bills')
        .then(response => response.json())
        .then(data => {
            if (loading) loading.style.display = 'none';
            
            if (data.success) {
                displayBills(data.bills);
            } else {
                showMessage('Lỗi khi tải hóa đơn: ' + data.message, 'error');
                billsContainer.innerHTML = `
                    <div class="no-bills">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Không thể tải danh sách hóa đơn</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading bills:', error);
            if (loading) loading.style.display = 'none';
            billsContainer.innerHTML = `
                <div class="no-bills">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Lỗi kết nối khi tải hóa đơn</p>
                </div>
            `;
        });
}

// Display bills in card format
function displayBills(bills) {
    const billsContainer = document.getElementById('bills-container');
    
    if (!bills || bills.length === 0) {
        billsContainer.innerHTML = `
            <div class="no-bills">
                <i class="fas fa-check-circle"></i>
                <p>Tuyệt vời! Không có hóa đơn nào cần thanh toán</p>
            </div>
        `;
        return;
    }
    
    // Filter only pending bills
    const pendingBills = bills.filter(bill => bill.status === 'pending' || bill.is_virtual);
    
    if (pendingBills.length === 0) {
        billsContainer.innerHTML = `
            <div class="no-bills">
                <i class="fas fa-check-circle"></i>
                <p>Tuyệt vời! Không có hóa đơn nào cần thanh toán</p>
            </div>
        `;
        return;
    }
    
    const billsHTML = `
        <div class="bills-container">
            ${pendingBills.map(bill => `
                <div class="bill-card">
                    <div class="bill-header">
                        <div class="bill-info">
                            <h4>${bill.class_name}</h4>
                            <div class="student-name">
                                <i class="fas fa-user"></i>
                                ${bill.student_name}
                            </div>
                        </div>
                        <div class="bill-amount">
                            <span class="amount">${formatCurrency(bill.final_amount || bill.amount)}</span>
                            <span class="currency">VND</span>
                        </div>
                    </div>
                    
                    <div class="bill-details">
                        <div class="bill-detail-row">
                            <span class="label">Môn học:</span>
                            <span class="value">${bill.subject || 'N/A'}</span>
                        </div>
                        <div class="bill-detail-row">
                            <span class="label">Phương thức:</span>
                            <span class="value">${getPaymentMethodText(bill.payment_method)}</span>
                        </div>
                        <div class="bill-detail-row">
                            <span class="label">Trạng thái:</span>
                            <span class="value">
                                <span class="status pending">
                                    <i class="fas fa-clock"></i> Chờ thanh toán
                                </span>
                            </span>
                        </div>
                        ${bill.created_at ? `
                        <div class="bill-detail-row">
                            <span class="label">Ngày tạo:</span>
                            <span class="value">${formatDate(bill.created_at)}</span>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="bill-actions">
                        <button class="pay-btn-primary" onclick="showQRPayment('${bill.id}', '${bill.student_name}', '${bill.class_name}', ${bill.final_amount || bill.amount}, '${bill.payment_method}')">
                            <i class="fas fa-qrcode"></i>
                            Thanh Toán Ngay
                        </button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    billsContainer.innerHTML = billsHTML;
}