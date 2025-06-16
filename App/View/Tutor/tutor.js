// Navigation functionality (chức năng điều hướng)
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();

        // Remove active class from all nav links and sections 
        // (loại bỏ các lớp đang active khỏi tất cả các link và các phần điều hướng)
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
        document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));

        // Add active class to clicked nav link
        this.classList.add('active');

        // Show corresponding section
        const targetId = this.getAttribute('href').substring(1);
        document.getElementById(targetId).classList.add('active');
    });
});        // Navbar toggle functionality
document.getElementById('navbarToggle').addEventListener('click', function () {
    // Check if we're on mobile (768px or less)
    if (window.innerWidth <= 768) {
        return; // Don't toggle on mobile
    }

    const navbar = document.getElementById('navbar');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = this.querySelector('i');
    const toggleButton = this;

    navbar.classList.toggle('collapsed');
    if (navbar.classList.contains('collapsed')) {
        // Navbar is collapsed
        // Thu gọn navbar
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
        mainContent.style.marginLeft = '95px'; // 60px (collapsed navbar) + 30px (toggle button) + 5px (gap)
        toggleButton.style.left = '60px';
    } else {
        // Navbar is expanded
        // mở rộng navbar
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
        mainContent.style.marginLeft = '285px'; // 250px (expanded navbar) + 30px (toggle button) + 5px (gap)
        toggleButton.style.left = '250px';
    }
});

// Handle window resize to reset mobile layout
// sử lí kích cỡ cửa sổ để reset layout điện thoại 
window.addEventListener('resize', function () {
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
