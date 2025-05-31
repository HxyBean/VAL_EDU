// Lấy tham chiếu đến các elements
const monthBtn = document.querySelector('.monthbtn');
const weekBtn = document.querySelector('.weekbtn');
const dayBtn = document.querySelector('.daybtn');

const monthCalendar = document.querySelector('.monthcalendar');
const weekCalendar = document.querySelector('.weekcalendar');
const dayCalendar = document.querySelector('.daycalendar');

// Hàm ẩn tất cả các bảng
function hideAllCalendars() {
    monthCalendar.style.display = 'none';
    weekCalendar.style.display = 'none';
    dayCalendar.style.display = 'none';
}

// Hàm hiển thị bảng được chọn
function showCalendar(type) {
    hideAllCalendars();
    switch(type) {
        case 'month':
            monthCalendar.style.display = 'table';
            break;
        case 'week':
            weekCalendar.style.display = 'table';
            break;
        case 'day':
            dayCalendar.style.display = 'table';
            break;
    }
}

// Thêm event listeners cho các nút
monthBtn.addEventListener('click', () => showCalendar('month'));
weekBtn.addEventListener('click', () => showCalendar('week'));
dayBtn.addEventListener('click', () => showCalendar('day'));

// Mặc định hiển thị bảng tháng khi tải trang
window.addEventListener('DOMContentLoaded', () => {
    showCalendar('month');
});
