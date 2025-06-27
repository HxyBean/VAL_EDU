<nav class="navbar" id="navbar">
    <button class="navbar-toggle" id="navbarToggle">
        <i class="fas fa-chevron-left"></i>
    </button>
    <ul>
        <li><a href="#overview" class="nav-link active"><i class="fas fa-globe"></i> <span>Tổng Quan</span></a></li>
        <li><a href="#settings" class="nav-link"><i class="fas fa-cog"></i> <span>Cài Đặt</span></a></li>
    </ul>
</nav>
<div id="change-password-section" style="display:none">
    <!-- Change password content -->
</div>
<script>
    
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
        toggleButton.style.left = '60px';
    } else {
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
        mainContent.style.marginLeft = '285px';
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
});

</script>