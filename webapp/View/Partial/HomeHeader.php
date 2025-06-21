<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="/webapp/">
                <img src="/webapp/View/images/logo_transparent.png" alt="VAL Edu Logo">
            </a>
            <div class="logo-text">
                <strong>VAL Edu</strong>
                <span>English Center For Everyone</span>
            </div>
        </div>
        
        <nav class="nav-menu">
            <ul>
                <li><a class="active" href="/webapp/">Trang chủ</a></li>
                <li class="dropdown">
                    <a href="#">Giới thiệu</a>
                    <ul class="dropdown-content">
                        <li><a href="#">Hall Of Fame</a></li>
                        <li><a href="#">Đội ngũ giáo viên tại VAL Edu</a></li>
                        <li><a href="#">Liên hệ</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Câu hỏi thường gặp (FAQ)</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Khóa Học</a>
                    <ul class="dropdown-content">
                        <li><a href="#">KHÓA HỌC IELTS CƠ BẢN</a></li>
                        <li><a href="#">KHÓA HỌC IELTS CẤP TỐC</a></li>
                        <li><a href="#">KHÓA HỌC TOEIC</a></li>
                        <li><a href="#">KHÓA HỌC TIẾNG ANH TIỂU HỌC</a></li>
                        <li><a href="#">KHÓA HỌC TIẾNG ANH THCS</a></li>
                        <li><a href="#">KHÓA HỌC TIẾNG ANH THPT</a></li>
                    </ul>
                </li>
                <li><a href="#">Lịch Khai giảng</a></li>
                <li class="dropdown">
                    <a href="#">Thư viện</a>
                    <ul class="dropdown-content">
                        <li><a href="#">IELTS</a></li>
                        <li><a href="#">TOEIC</a></li>
                        <li><a href="#">Tiếng Anh giao tiếp</a></li>
                    </ul>
                </li>
                <li><a href="#">Tin tức</a></li>
            </ul>
        </nav>      
                
        <div class="cta-button">
            <?php if ($user_logged_in ?? false): ?>
                <span class="welcome-text">Xin chào, <?= htmlspecialchars($user_name ?? '') ?></span>
                <!-- Use proper route URLs -->
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <a href="/webapp/admin/dashboard" class="dashboard-btn">Dashboard</a>
                <?php elseif (($_SESSION['user_role'] ?? '') === 'tutor'): ?>
                    <a href="/webapp/tutor/dashboard" class="dashboard-btn">Dashboard</a>
                <?php elseif (($_SESSION['user_role'] ?? '') === 'student'): ?>
                    <a href="/webapp/student/dashboard" class="dashboard-btn">Dashboard</a>
                <?php elseif (($_SESSION['user_role'] ?? '') === 'parent'): ?>
                    <a href="/webapp/parent/dashboard" class="dashboard-btn">Dashboard</a>
                <?php endif; ?>
                <a href="/webapp/logout" class="logout-btn">Đăng xuất</a>
            <?php else: ?>
                <a href="/webapp/login" class="sign-in-btn">Đăng nhập</a>  
                <a href="/webapp/register" class="register-btn">Đăng ký</a> 
            <?php endif; ?>
        </div>
    </div>
</header>