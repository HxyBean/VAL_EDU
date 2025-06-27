
<header class="header">
    <img src="../images/logo_transparent.png" class="logo" />
    <h1>Xin Chào!</h1>
    <div class="tutor-info">
        <i class="fas fa-user-tie"></i>
        <span>Chào mừng, <?= htmlspecialchars($student_name) ?></span>
        <button class="logout-btn" onclick="showLogoutModal()">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </button>
    </div>
</header>