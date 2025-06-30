document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('navbar');
    const navbarToggle = document.getElementById('navbarToggle');
    const navLinks = document.querySelectorAll('.nav-link');
    const contentSections = document.querySelectorAll('.content-section');
    const mainContent = document.querySelector('.main-content');

    // Check if elements exist
    if (!navbar || !navbarToggle) {
        console.error('Navbar elements not found');
        return;
    }

    console.log('Navbar elements found, setting up toggle'); // Debug

    // Toggle navbar collapse
    navbarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Navbar toggle clicked'); // Debug log
        
        if (window.innerWidth <= 768) {
            // Mobile behavior
            navbar.classList.toggle('mobile-open');
            return;
        }
        
        // Desktop behavior
        navbar.classList.toggle('collapsed');
        const toggleIcon = this.querySelector('i');
        
        if (navbar.classList.contains('collapsed')) {
            console.log('Collapsing navbar'); // Debug
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
            }
            if (mainContent) {
                mainContent.style.marginLeft = '60px';
            }
            navbarToggle.style.left = '60px';
        } else {
            console.log('Expanding navbar'); // Debug
            if (toggleIcon) {
                toggleIcon.classList.remove('fa-chevron-right');
                toggleIcon.classList.add('fa-chevron-left');
            }
            if (mainContent) {
                mainContent.style.marginLeft = '250px';
            }
            navbarToggle.style.left = '250px';
        }
    });

    // Handle navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Nav link clicked:', this.getAttribute('href')); // Debug log
            
            const targetId = this.getAttribute('href').substring(1);
            
            // Update active nav link
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Show target section
            contentSections.forEach(section => {
                section.classList.remove('active');
            });
            
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
                console.log('Switched to section:', targetId); // Debug log
            } else {
                console.error('Section not found:', targetId);
            }
        });
    });

    // Mobile responsiveness
    function handleResize() {
        if (window.innerWidth <= 768) {
            navbar.classList.remove('collapsed');
            navbar.classList.remove('mobile-open');
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
            navbarToggle.style.left = '10px';
        } else {
            navbar.classList.remove('mobile-open');
            if (navbar.classList.contains('collapsed')) {
                if (mainContent) {
                    mainContent.style.marginLeft = '60px';
                }
                navbarToggle.style.left = '60px';
            } else {
                if (mainContent) {
                    mainContent.style.marginLeft = '250px';
                }
                navbarToggle.style.left = '250px';
            }
        }
    }

    // Initial setup and resize listener
    handleResize();
    window.addEventListener('resize', handleResize);

    console.log('DashboardNavbar.js loaded successfully'); // Debug log
});